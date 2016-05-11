<?php

include "koneksi.php";

try{
	//*
	$time = isset($_GET['t'])?$_GET['t'] : '07:00';
	$comp = isset($_GET['c'])?$_GET['c'] : '0';
	$cc = substr($time,0,1);
	$tz = ($cc == "-") ? $time :  "+".$time ; 
	// echo $tz ."<br>"; 
	
	$q_wkt = "select max(convert_tz(from_unixtime(d.epochtime),'+07:00','$tz') ) wkt
				from data d
					inner join titik_ukur tu on tu.id_titik_ukur = d.id_titik_ukur 
					inner join ship s on s.id_ship = tu.id_ship
				where s.status = 1 and s.id_company = $comp
				group by tu.id_ship;";
	
	// echo $q_wkt."<br>";
	$stm = $conn->prepare($q_wkt);
	$stm->execute();
	$hsl = $stm->fetchAll(PDO::FETCH_OBJ);
	// $hsl = $stm->fetchAll(PDO::FETCH_ASSOC);

	$a = "'";
	foreach ($hsl as $s){
		$a .= $s->wkt."','";
	}
	
	// echo $a;
	$b = substr($a,0,-2);
	// echo $b;
	
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

	// $jsonResult = array(
		// 'success' => true,
		// 'posisi' => $posisi
	// );
	//*/
	
	//================================================================================
	// Creates the Document.
	$dom = new DOMDocument('1.0', 'UTF-8');
	
	// Creates the root KML element and appends it to the root document.
	// $node = $dom->createElementNS('http:"//earth.google.com/kml/2.1, 'kml');
	$node = $dom->createElementNS("http://www.opengis.net/kml/2.2", 'kml');
 
	$parNode = $dom->appendChild($node);

	// Creates a KML Document element and append it to the KML element.
	$dnode = $dom->createElement('Document');
	$docNode = $parNode->appendChild($dnode);

	foreach($posisi as $pos){
		$node = $dom->createElement('Placemark');
		$placeNode = $docNode->appendChild($node);

		// Creates an id attribute and assign it the value of id column.
		$placeNode->setAttribute('id', 'placemark' . $pos->id);

		// Create name, and description elements and assigns them the values of the name and address columns from the results.
		// $nameNode = $dom->createElement('name',htmlentities($row['name']));
		$nameNode = $dom->createElement('name',htmlentities($pos->ves));
		$placeNode->appendChild($nameNode);
		$descNode = $dom->createElement('time', $pos->wkt);
		$placeNode->appendChild($descNode);
		// $styleUrl = $dom->createElement('styleUrl', '#' . $loks->type . 'Style');
		// $placeNode->appendChild($styleUrl);
		// Creates a Point element.
		$pointNode = $dom->createElement('Point');
		$placeNode->appendChild($pointNode);

		// Creates a coordinates element and gives it the value of the lng and lat columns from the results.
		$coorStr = $pos->lng . ','  . $pos->lat;
		$coorNode = $dom->createElement('coordinates', $coorStr);
		$pointNode->appendChild($coorNode);
		
	}

	
	$kmlOutput = $dom->saveXML();
	// header('Content-type: application/vnd.google-earth.kml+xml');
	header('Content-type: application/xml');
	echo $kmlOutput;
	
	//================================================================================
	
}catch(Exception $e){
	echo $e->getMessage();
}

?>