<?php

include "koneksi.php";

try{
	$time = isset($_GET['t'])?$_GET['t'] : '07:00';
	$comp = isset($_GET['c'])?$_GET['c'] : '0';
	$cc = substr($time,0,1);
	$tz = ($cc == "-") ? $time :  "+".$time ; 
	
	$q_wkt = "select max(data_time) wkt 
				from data d1 
					join titik_ukur tu1 on tu1.id_titik_ukur = d1.id_titik_ukur 
					join ship s on s.id_ship = tu1.id_ship
				where s.status = 1
				group by tu1.id_ship;";
	$stm = $conn->prepare($q_wkt);
	$stm->execute();
	$hsl = $stm->fetchAll(PDO::FETCH_OBJ);

	$a = "'";
	foreach ($hsl as $s){
		$a .= $s->wkt."','";
	}
	$b = substr($a,0,-2);
	
	$c = "select tu.id_ship id, s.name ves, convert_tz(from_unixtime(d.epochtime),'+07:00','$tz') wkt,
			max(case when tu.id_data_type = 1 then round(d.value,2) end) lat, 
			max(case when tu.id_data_type = 2 then round(d.value,2) end) lng 
		from data d 
			join titik_ukur tu on tu.id_titik_ukur = d.id_titik_ukur
			join ship s on s.id_ship = tu.id_ship
		where convert_tz(from_unixtime(d.epochtime),'+07:00','$tz') in ($b)
		group by tu.id_ship;";
	
	$stm = $conn->prepare($c);
	$stm->execute();
	$posisi = $stm->fetchAll(PDO::FETCH_OBJ);

	//================================================================================
	$dom = new DOMDocument('1.0', 'UTF-8');
		$node = $dom->createElementNS("http://www.opengis.net/kml/2.2", 'kml');
		$parNode = $dom->appendChild($node);
			$dnode = $dom->createElement('Document');
			$docNode = $parNode->appendChild($dnode);
			
				$Fnode = $dom->createElement('Folder');
				$FoldNode = $docNode->appendChild($Fnode);
					$lnode = $dom->createElement('Position');
					$LokNode = $FoldNode->appendChild($lnode);
			
	foreach($posisi as $pos){
		$plcnode = $dom->createElement('Placemark');
		$PlaceNode = $LokNode->appendChild($plcnode);
		$plcnode->setAttribute('id', $pos->id);
			$nameNode = $dom->createElement('name',htmlentities($pos->ves));
			$plcnode->appendChild($nameNode);
			$timeNode = $dom->createElement('time', $pos->wkt);
			$plcnode->appendChild($timeNode);
			$pNode = $dom->createElement('Point');
			$plcnode->appendChild($pNode);
				$coorStr = $pos->lng . ','  . $pos->lat;
				$coorNode = $dom->createElement('coordinates', $coorStr);
				$pNode->appendChild($coorNode);
		
	}

	
	$kmlOutput = $dom->saveXML();
	header('Content-type: application/vnd.google-earth.kml+xml');
	echo $kmlOutput;
	
	//================================================================================
	
}catch(Exception $e){
	echo $e->getMessage();
}

?>