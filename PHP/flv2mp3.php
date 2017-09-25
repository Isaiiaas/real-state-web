<?php
	
	$file = "flv-example.flv";
	$flv = fopen($file, "rb");
	
	$mp3 = fopen("downloads/New-file-name.mp3", "wb");
	
	$binary_data = fread($flv, 13);
	$datasize = 0;
	$fileoffset = 13;
	$filelength = filesize($file);
	
	while (($filelength - $fileoffset) > $datasize) {
		
		$binary_data = fread($flv, 12);
		
		$data = unpack("C1TagType/" . "C3DataSize/" . "C3TimeStamp/" . "C1TimeStampEx/" . "C3StreamId/" . "C1MediaInfo", $binary_data);
		
		$datasize = ($data['DataSize1'] << 16) | ($data['DataSize2'] << 8) | ($data['DataSize3']);
		$binary_data = fread($flv, $datasize - 1);
		$previousTagSize = fread($flv, 4);
		if ($data['TagType'] == 8) {
			fwrite($mp3, $binary_data);
		}
		$fileoffset += 12 + 3 + $datasize;
	}
	
	fclose($flv);
	fclose($mp3);
	unlink($file);
?>
