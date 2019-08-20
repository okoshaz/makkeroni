<?php

//  	print "[[;#fff;]textfiles of the 'home' folder:]\n";
  	print "[[;#fff;]./:]\n";
	foreach (glob("home/*.txt") as $filename) {
		$filenameFiltered = substr($filename, 5);

		$meret = filesize($filename);
		$meret = round($meret,1);
    print($filenameFiltered . " (". $meret . " Bytes)". "\n");
}

  	print "\n[[;#fff;]./saved:]\n";
	foreach (glob("home/saved/*.*") as $filename) {
		$filenameFiltered = substr($filename, 11);

		$meret = filesize($filename);
		$meret = round($meret,1);
    print($filenameFiltered . " (". $meret . " Bytes)". "\n");
}

  	print "\n[[;#fff;]./soundfiles:]\n";
	foreach (glob("home/soundfiles/*.*") as $filename) {
		$filenameFiltered = substr($filename, 16);

		$meret = filesize($filename) / 1024;
		$meret = round($meret,1);
    print($filenameFiltered . " (". $meret . " kB)". "\n");
}





?>