<?php

include "koneksi.php";

try{

	// $params = isset($_GET['id'])?$_GET['id'] : '';
	// $encode = json_decode(base64_decode($params));
	
	// $tz = $encode->{'timezone'};
	// $id_user = $encode->{'id'};


	// echo $id_user.'<br>'; 
	// echo $tz.'<br>';
	// convert_tz(from_unixtime(max(d.epochtime)),'+07:00','".$tz."') time,
	
	$id_user = 1;
	$tz = '+08:00';
	
	
	// $kapal = "select s.id_ship
				// from ship s 
					// join user u  on u.id_company = s.id_company
				// where 
					// s.status = 1
					// and u.id = $id_user
				// ;";
	// $q_wkt = "select max(data_time) wkt
				// from data d
					// inner join titik_ukur tu on tu.id_titik_ukur = d.id_titik_ukur 
					// inner join ship s on s.id_ship = tu.id_ship
				// where s.status = 1 and s.id_company = 1
				// group by tu.id_ship;";
	$q_wkt = "select 
		tu.id_ship, 
		max(case when tu.id_data_type = 1 then round(d.value,2) end) lat,
		max(case when tu.id_data_type = 2 then round(d.value,2) end) lng
    from data d
		inner join titik_ukur tu on tu.id_titik_ukur = d.id_titik_ukur
    where data_time in 
		(select max(data_time) 
			from data d1 join titik_ukur tu1 on tu1.id_titik_ukur = d1.id_titik_ukur 
            group by tu1.id_ship)
group by tu.id_ship
;";
	
	echo $q_wkt."<br>";
	
	
	$stm = $conn->prepare($q_wkt);
	$stm->execute();
	$hsl = $stm->fetchAll(PDO::FETCH_OBJ);

	print_r($hsl);
	// $qa = '';
	// foreach ($hsl as $row) {
		// // echo $row->id_ship.'<br>';
		// // $aa = $aa.' union dengan '.$row->id_ship;
		
		// // $qa = $qa."select 
					// // s.id_ship id,
					// // convert_tz(from_unixtime(d.epochtime),'+07:00','$tz') waktu, 
					// // s.imo, 
					// // st.color, 
					// // s.name vessel, 
					// // s.img, 
					// // max(case when tu.id_data_type = 1 then round(d.value,2) end) lat,
					// // max(case when tu.id_data_type = 2 then round(d.value,2) end) lng,
					// // max(case when tu.id_data_type = 4 then round(d.value,2) end) speed,
					// // max(case when tu.id_data_type = 3 then round(d.value,2) end) heading
				// // from data d
					// // inner join titik_ukur tu on tu.id_titik_ukur = d.id_titik_ukur
					// // inner join ship s on s.id_ship = tu.id_ship
					// // inner join ship_type st on st.id_ship_type = s.id_ship_type

				// // where tu.id_ship = $row->id_ship and d.data_time = (select max(data_time) from data d1 
						// // inner join titik_ukur tu1 on tu1.id_titik_ukur = d1.id_titik_ukur 
					// // where tu1.id_ship =$row->id_ship)" ." union \n";
		// $qa = $qa."select 
					// s.id_ship id,
					// convert_tz(from_unixtime(d.epochtime),'+07:00','$tz') waktu, 
					// st.color, 
					// s.name vessel, 
					// max(case when tu.id_data_type = 1 then round(d.value,2) end) lat,
					// max(case when tu.id_data_type = 2 then round(d.value,2) end) lng
				// from data d
					// inner join titik_ukur tu on tu.id_titik_ukur = d.id_titik_ukur
					// inner join ship s on s.id_ship = tu.id_ship
					// inner join ship_type st on st.id_ship_type = s.id_ship_type
				// where tu.id_ship = $row->id_ship and d.data_time = (select max(data_time) from data d1 
						// inner join titik_ukur tu1 on tu1.id_titik_ukur = d1.id_titik_ukur 
					// where tu1.id_ship =$row->id_ship)" ." union \n";
		
		
	// }

	// $query = substr($qa,0,-8).";";
	// // echo $query."<br>"; 
	// $stm = $conn->prepare($query);
	// $stm->execute();
	// $posisi = $stm->fetchAll(PDO::FETCH_OBJ);
	// print_r($posisi);


	// $jsonResult = array(
		// 'success' => true,
		// 'posisi' => $posisi
	// );
}catch(PDOException $e){
	$jsonResult = array(
		'success' => false,
		'posisi' => $e->getMessage()
	);
}
// echo json_encode($jsonResult);

?>