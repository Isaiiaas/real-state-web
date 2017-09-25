<pre><?php
	include 'class.gpxreader.php';
	include 'functions.music.php';
	$c = new GuitarProReader( "gp3/John_Lennon_Imagine.gp3");
	$c->getGuitarProVersion();
	$c->getSongInfo();
	$c->getLyrics();
	$c->getMusicInfo();
	$c->getMeasures();
	$c->getTracks();
	$c->processMeasuresAndTracks();
	$c->close();
	
	$finalNotes =getFinalFormat($c->beats[0]);
	if(empty($c->fileInfo['interpret'])) $c->fileInfo['interpret'] = $c->fileInfo['author'];
	echo "<h3>".$c->fileInfo['title']." - ".$c->fileInfo['interpret']."</h3>";
	
	print_r($finalNotes);
	
	if( preg_match('/6/',$finalNotes) and !preg_match('/3/',$finalNotes)){
		$finalNotes  =  flautaDoce(changeFormat(reduceFinalSemitone($finalNotes, 24)));
		}elseif( preg_match('/6/',$finalNotes) and !preg_match('/4/',$finalNotes)){
		$finalNotes  =  flautaDoce(changeFormat(reduceFinalSemitone($finalNotes, 12)));
		}elseif( preg_match('/5/',$finalNotes)  and preg_match('/4/',$finalNotes)){
		$finalNotes  =  flautaDoce(changeFormat(reduceFinalSemitone($finalNotes, 12)));
	}
	
	echo $finalNotes;
	
	/*echo "<br>";
		print_r($c->tracks);
		print_r($c->beats);
		print_r($c->beats);
		print_r($c->musicInfo);
	print_r($c->fileInfo);;*/
	
?>

