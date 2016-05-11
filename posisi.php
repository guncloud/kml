<?php

include "koneksi.php";

try{
	
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
	
	$c = "select tu.id_ship, s.name, convert_tz(from_unixtime(d.epochtime),'+07:00','$tz') w,
			max(case when tu.id_data_type = 1 then round(d.value,2) end) lat, 
			max(case when tu.id_data_type = 2 then round(d.value,2) end) lng 
		from data d 
			join titik_ukur tu on tu.id_titik_ukur = d.id_titik_ukur
			join ship s on s.id_ship = tu.id_ship
		where convert_tz(from_unixtime(d.epochtime),'+07:00','$tz') in ($b)
		group by tu.id_ship;";
	
	// $aa = implode (", ",$hsl);
	// echo $c;
	
	// $qa = '';

	// $query = substr($qa,0,-8).";";
	// // echo $query."<br>"; 
	$stm = $conn->prepare($c);
	$stm->execute();
	$posisi = $stm->fetchAll(PDO::FETCH_OBJ);
	// print_r($posisi);


	$jsonResult = array(
		'success' => true,
		'posisi' => $posisi
	);
}catch(PDOException $e){
	$jsonResult = array(
		'success' => false,
		'posisi' => $e->getMessage()
	);
}
echo json_encode($jsonResult);

?>