<?php  
$servername = "localhost";
$username = "marine";
$password = "monita2014";
$database = "marine_1";
try {
	$conn = new PDO("mysql:host=$servername;dbname=$database", $username, $password);
	// echo "sukses";
} catch (Exception $ex) {
	echo "Koneksi bermasalah: " . $ex->getMessage(). "<br/>";
}
?>
