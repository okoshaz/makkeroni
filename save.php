<?php
function writeToFile($file, $data) {
    file_put_contents($file, $data);
}

$data = $_GET['content'];
$filenev = $_GET['file'];
// echo $data;
writeToFile('home/saved/'.$filenev,$data);
    
?>
