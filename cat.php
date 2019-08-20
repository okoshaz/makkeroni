<?php

	$file = $_GET["file"];
	print file_get_contents( $file );
?>