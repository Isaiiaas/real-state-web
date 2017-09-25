<?php
	class GuitarProReader {
		
		var $version = 3;
		var $filename;
		var $file = null;
		var $fileoffset = 0;	
		
		var $notas  = Array("A","Bb","B","C","Db","D","Eb","E","F","Gb","G","Ab");
		var $dinamics =  Array("ppp", "pp", "p", "mp", "mf", "f", "ff", "fff");
		var $stringEffects = Array("Tremolo bar","Tapping","Slapping","Popping","Unknown");
		var $bendEffects = Array("","Bend","Bend and release","Bend, release and bend","Pre bend","Pre bend and release","Tremolo dip","Tremolo dive","Tremolo release up","Tremolo inverted dip","Tremolo return","Tremolo release down");
		var $vibratoType = Array("none","fast","average","slow");
		
		var $error = false;
		var $songInfo;
		var $strings = Array();
		var $fileInfo = Array(); 
		var $musicInfo = Array(); 
		
		var $readingTime;
		
		
		var $chords  = Array();
		var $ts = Array(); // ts = time signature	
		var $tracks;
		var $sections = Array();
		//measures = espace between bars
		
		
		var $beats = Array();
		var $debug = 0;
				
		function __construct($filename) {
			$this->filename =  $filename;
			$this->file =  fopen($filename, "rb");
			$this->readingTime  =  time();
		}
		
		function debug_log($str){
			if($this->debug == 1) echo "\t\t".$str."\n";
		}
		
		function readInt(){
			if($r = fread($this->file , 4)){
				if(strlen($r) == 4 ){
					return $r;
					}else{
					//exit ("fail 2");
				}
				
				}else{
				//exit ("faial");
			}
		}
		
		function _readInt(){
			$r= '';
			if($r= $this->readInt()) {
				$r = unpack("ISize", $r);
				$r = $r['Size'];	
			}
			return $r;
		}
		
		function readShortInt(){
			return fread($this->file , 2);
		}
		
		function readByte(){
			return fread($this->file , 1);
		}
		
		function _readByte($unsigned =false){
		$read = fread($this->file , 1);
			if(strlen($read)==0){
				$this->error =1;
				return 0;
			}
		
			if($unsigned){	
				$r =  unpack("CSize",$read);
				}else{
				$r =  unpack("cSize", $read);
			}
			return $r['Size'];	
		}
		
		function close(){
			fclose($this->file);
			$this->file=NULL;
		}
		
		function unpackSize($str){
			$r =  unpack("cSize", $str);
			return $r['Size'];	
		}
		
		function unpackSize2($str){
			$r =  unpack("ISize", $str);
			return $r['Size'];	
		}
		
		function readHeadersInfo(){
			
			$this->_readInt();
			$size = $this->_readByte();
			$binary_data = ($size>0) ? fread($this->file, $size) : "";
			return $binary_data; 
		}
		
		function readString(){
			$size = $this->_readByte();
			$binary_data = ($size>0) ? fread($this->file, $size) : "";
			return $binary_data; 
		}
		
		function getSongInfo(){
			
			$this->fileInfo['title'] = $this->readHeadersInfo();
			$this->fileInfo['subtitle'] = $this->readHeadersInfo();
			$this->fileInfo['interpret'] = $this->readHeadersInfo();
			$this->fileInfo['album'] = $this->readHeadersInfo();
			$this->fileInfo['author'] = $this->readHeadersInfo();
			$this->fileInfo['copyright'] = $this->readHeadersInfo();					  
			$this->fileInfo['tabAuthor'] = $this->readHeadersInfo();					  
			$this->fileInfo['instructional'] = $this->readHeadersInfo();
			$this->fileInfo['Notice'] = Array();
			$this->readNotice();
			if($this->version <=4 ){
				$this->readByte(); //TripletFeel;
			}
		}
		
		function readNotice(){
			$size = $this->_readInt();
			if($size>0){
				for($i=0;$i<$size;$i++) $this->fileInfo['Notice'] = $this->readHeadersInfo();		
			}
		}
		
		function getGuitarProVersion(){
			
			$size = $this->_readByte();
			$this->fileInfo['version']  = fread($this->file, $size);
			fread($this->file, 30-$size);
			
			$n = explode("PRO v",$this->fileInfo['version']);
			$this->version = floor($n[1]);
		}
		
		function getLyrics(){
			
			if($this->version >=4 ){
				$size = $this->_readInt();
				if($size == 0 ) $size = 1;
				for($i=0;$i<$size;$i++){
					for($x=0;$x<5;$x++){
						$this->_readInt();
						$size2=  $this->_readInt();
						
						if($size2>0 ){
							$data = fread($this->file,$size2);
							$this->fileInfo['lyrics'][] = $data;
						}
					}
				}
			}
			
		}
		
		function addSemitone($note,$recursive = false, $repeatN=''){
			$number = '';
			
			if($recursive){
				//	echo $note."-";
				for($i = 0 ;$i<$repeatN;$i++){
					$note = $this->addSemitone($note);
				}
				//echo $repeatN."--".$note."\n";
				
				return $note;
				}else{
				
				if(preg_match('/\d+/', $note,$numbers)){
					$number = true;
					$note = str_replace(explode(",","1,2,3,4,5,6,7,8,9,0"),"",$note);
					
					$numbers =$numbers[0];
					}else{
					$numbers='';
				} 
				
				$notes = explode(",","C,C#,D,D#,E,F,F#,G,G#,A,A#,B");
				$index = array_search($note, $notes);
				if($index == count($notes)-1){
					if(empty($numbers)) {
						return $notes[0]."1";
						}else{
						return $notes[0].($numbers+1);
					}
					}else{
					return ($notes[$index+1]).$numbers;
				}
				
			}
			
		}
		
		function getMusicInfo(){
			$this->musicInfo['time'] = $this->_readInt();
			
			if($this->version >=4 ){
				$this->musicInfo['key'] = $this->_readByte(); //     0: C    |    1: G (#)    | 	2: D (##)   |    -1: F (b)
				$this->musicInfo['octave'] = $this->_readByte();
			}
			
			if($this->version <4 ){
				$this->musicInfo['key'] = $this->_readInt(); //     0: C    |    1: G (#)    | 	2: D (##)   |    -1: F (b)
			}
			
			for($i=1;$i<=64;$i++){
				$this->musicInfo['channels'] = Array(
				"Instrument"=>$this->_readInt(),
				"Volume"=> $this->_readByte(),
				"Pan"=> $this->_readByte(),
				"Chorus"=> $this->_readByte(),
				"Reverb"=> $this->_readByte(),
				"Phaser"=> $this->_readByte(),
				"Tremolo"=> $this->_readByte(),
				"blank1"=>$this->unpackSize($this->readShortInt())
				);
			}
			
			$this->musicInfo['measures_size'] =$this->_readInt();
			$this->musicInfo['tracks_size'] = $this->_readInt();
		}
		
		function getMeasures(){
			
			for($i=0;$i<$this->musicInfo['measures_size'];$i++){
				
				if($this->version >= 3 ){
					
					$bytemask =  $this->_readByte() & 0xFF;
					if($bytemask & 1){
						$this->ts['numerator'] = $this->_readByte();
					}
					if($bytemask & 2){
						$this->ts['denominator'] = $this->_readByte();
					}
					
					if($bytemask & 4){
						$this->debug_log("Start of repeat:");
					}
					if($bytemask & 8){
						$this->debug_log("End of repeat:");
						$this->_readByte();
					}
					
					if($this->version < 5 ){
						
						
						if($bytemask & 16){
							$this->debug_log("Number of alternate ending: ". $this->_readByte());
						}	
						
						if($bytemask & 32){
							$this->_readInt();
							$s = $this->_readByte();
							
							$section = Array();
							$section['name'] = fread($this->file, $s);
							$section['color']  =  $this->_readByte().",". $this->_readByte().",". $this->_readByte();
							$this->sections[$i][] = $section;
							$this->_readByte();//read unnused value
						}	
						
						if($bytemask & 64){
							$this->debug_log("New key signature: ");
							$k = $this->_readByte();
							$this->debug_log($k);
							$km = ($k < 0 ) ? "flats" : "sharps";
							$this->debug_log($km);
							$k = $this->_readByte();
							$km =  ($k) ? "major" : "minor";
							$this->debug_log($km);
							
						}	
						
						if($bytemask & 80){
							$this->debug_log("Double bar:");
						}	
						
					}
				}
			}
			
		}
		
		function getTracks(){
			for($i=0;$i<$this->musicInfo['tracks_size'];$i++){
				
				
				$bytemask =  $this->_readByte() & 0xFF;
				$this->debug_log("Track bitmask: {$bytemask} ");
				
				if($bytemask & 1){
					$this->debug_log("Is a drum track:");
				}
				if($bytemask & 2){
					$this->debug_log( "Is a 12 string guitar track:");
				}
				if($bytemask & 4){
					$this->debug_log("Is a banjo track:");
				}
				if($bytemask & 16){
					$this->debug_log("Is marked for solo playback:");
				}
				if($bytemask & 32){
					$this->debug_log( "Is marked for muted playback");
				}
				if($bytemask & 64){
					$this->debug_log("Is marked for RSE playback");
				}
				if($bytemask & 128){
					$this->debug_log("Is set to have the tuning displayed");
				}
				
				
				$size = $this->_readByte();
				$track = Array();
				
				$name = ($size > 0) ? fread($this->file, $size) : "No Name";
				if(40-$size>0){
				fread($this->file, 40-$size);
				}
				$strnum = $this->_readInt();
				
				//	echo "track Name:".$name."\n";
				//echo "Number of Strings:".$strnum."\n";
				$track['name'] = $name;
				$track['strings_count'] = $strnum;
				$track['strings'] =  Array();
				$this->strings[$i] = $strnum;
				$track['capo'] ='';
				$track['color'] = '';
				
				for($x=0;$x<7;$x++){
					$s = $this->_readInt();
					if($x< $strnum ){
						$track['octave'][$x] =floor($s /16)-1 ;//3
						if(NULL == (($this->notas[($s + 3) % 12]))){
						$this->error =true;
						return 0;
						}
						$track['strings'][$x] = ($this->notas[($s + 3) % 12]).((floor($s /16))+1);
						$track['midi'][$x] = $s;
						
						//echo "Tuning for the string {$x} =  ".($this->notas[($s + 3) % 12])."\n";
						}else{
						//echo "(skipping definition for unused string)\n";
					}
				}
				//print_r($track);
				
				$track['Midi'] = $this->_readInt();//echo "\nMidiPort ";
				$track['Channel']  =$this->_readInt();//echo "\nChannel ";
				$track['Channel IE'] = $this->_readInt();//echo "\nChannel IE ";
				$track['nfrets'] = $this->_readInt();//echo "\nfrets ";
				$track['capo'] =  ($this->_readInt()) ? "Yes" : "No";//Capo
				$track['color'] =  $this->_readByte().",". $this->_readByte().",". $this->_readByte();
				
				$this->_readByte();//Read unused value
				
				$this->tracks[$i] = $track;
			}
		}
		
		function processDiagram0($track){
			$diagram  =Array();
			$this->_readInt();	
			$size = $this->_readByte();
			//	if($size == 0) exit("Ops!");
			$data = ($size>0) ? fread($this->file, $size) : "No name";
			$fret = $this->_readInt();
			$diagram['name']  = $data;
			$r= Array();
			if($fret){
				for($dia = 0;$dia < $this->strings[$track];$dia++){//dia = diagram
					$freatPlayed = $this->_readInt(); ////Read the fret played on this string
					if((real) $freatPlayed == -1){
						//echo "string {$dia} not played\n";
						}else{
						$r['dia'] = $freatPlayed;
						//echo "string {$dia} played = ".$freatPlayed."\n";
					}
				}
			}
			$diagram['chords'] = $r;
			
			return $diagram;
			
		}
		
		function processDiagram1($track){
			
			$chordInfo = Array("bares"=>Array(),"type"=>"diagram1");
			
			$chordInfo['display'] =  (! $this->_readByte()) ? "flat" : "sharp";
			fread($this->file, 3);// skipping 3 bytes of unknown data
			
			
			$chordInfo['root'] =  $this->_readByte();
			if($this->version == 3)	fseek($this->file, 3, SEEK_CUR );// skipping 3 bytes of unknown data
			
			
			$chordInfo['type'] =  $this->_readByte();
			if($this->version == 3)		fseek($this->file, 3, SEEK_CUR );// skipping 3 bytes of unknown data
			
			
			$chordInfo['91113option'] =  $this->_readByte();
			if($this->version == 3)	fseek($this->file, 3, SEEK_CUR );// skipping 3 bytes of unknown data
			
			
			$chordInfo['bass'] = ($this->notas[($this->_readInt() + 3) % 12]);
			$chordInfo['moreless'] = $this->_readByte();
			fseek($this->file, 4, SEEK_CUR );//Unknown data
			
			$size = $this->_readByte();// real string size;
			$name = fread($this->file, 20);
			$chordInfo['name'] = $name;
			
			
			fseek($this->file, 2, SEEK_CUR );//Unknown data
			
			
			$byte = $this->_readByte();
			if (!$byte){
				$chordInfo['fifth'] = "perfect" ;
				}elseif($byte == 1) {
				$chordInfo['fifth'] = "augmented";
				}else{ 
				$chordInfo['fifth'] = "diminished";
			}
			
			if($this->version == 3)	fseek($this->file, 3, SEEK_CUR );// skipping 3 bytes of unknown data
			
			$byte = $this->_readByte();
			if (!$byte){
				$chordInfo['nineth'] =  "perfect" ;
				}elseif($byte == 1) {
				$chordInfo['nineth'] =  "augmented";
				}else{ 
				$chordInfo['nineth'] =  "diminished";
			}
			
			if($this->version == 3)	fseek($this->file, 3, SEEK_CUR );// skipping 3 bytes of unknown data
			
			
			$byte = $this->_readByte();
			if (!$byte){
				$chordInfo['eleventh'] = "perfect" ;
				}elseif($byte == 1) {
				$chordInfo['eleventh'] = "augmented";
				}else{ 
				$chordInfo['eleventh'] = "diminished";
			}
			
			if($this->version == 3)	fseek($this->file, 3, SEEK_CUR );// skipping 3 bytes of unknown data
			
			$chordInfo['base_fret'] = $this->_readInt();
			
			$tmpStrings = Array();
			
			for($sn = 0; $sn < 7; $sn++){	
				if($sn< $this->strings[$track]){
					$strname = $this->_readInt();
					//echo "String #".$strname."\n";
					if((float) $strname == -1){
						//echo "String #".($sn+1)." | (String unused)\n";
						}else{
						$tmpStrings[($sn+1)] = $strname;
						//echo "String #".($sn+1)." | Fret:".$strname."\n";
					}
					
					}else{
					//echo "(skipping definition for unused string)\n";
					$this->_readInt();
				}
			}
			
			
			$chordInfo['strings'] = $tmpStrings;
			
			
			$barres = $this->_readByte();
			$tmpBarres = Array();
			
			for($b = 0; $b < 5; $b++){	//For each of the 5 possible barres
				if($b < $barres){	//If this barre is defined
					//echo "Barre #".($b+1)." | Fret ". $this->_readByte()."\n";
					$tmpBarres[($b+1)] = $this->_readByte();
					}	else	{
					//echo ("(skipping fret definition for undefined barre)\n");
					$this->_readByte();
				}
			}	
			
			$chordInfo['bares'][] = $tmpBarres;
			
			for($b = 0; $b < 5; $b++){	//For each of the 5 possible barres
				if($b < $barres){	//If this barre is defined
					$tmpBarres[($b+1)] = $this->_readByte();
					//echo "Barre #".($b+1)." starts at string ". $this->_readByte()."\n";
					}	else	{
					#echo ("(skipping fret definition for undefined barre)\n");
					$this->_readByte();
				}
			}	
			
			$chordInfo['bares'][] = $tmpBarres;
			
			for($b = 0; $b < 5; $b++){	//For each of the 5 possible barres
				if($b < $barres){	//If this barre is defined
					$tmpBarres[($b+1)] = $this->_readByte();
					//echo "Barre #".($b+1)." ends at string ". $this->_readByte()." \n";
					}	else	{
					//echo ("(skipping fret definition for undefined barre)\n");
					$this->_readByte();
				}
			}	
			
			$chordInfo['bares'][] = $tmpBarres;
			
			/*echo "\nChord includes first interval: ";
				echo ( $this->_readByte())  ?  "yes" : "No";
				echo "\nChord includes third interval: ";
				echo ( $this->_readByte())  ?  "yes" : "No";
				echo "\nChord includes fifth interval: ";
				echo ( $this->_readByte())  ?  "yes" : "No";
				echo "\nChord includes seventh interval: ";
				echo ( $this->_readByte())  ?  "yes" : "No";
				echo "\nChord includes ninth interval: ";
				echo ( $this->_readByte())  ?  "yes" : "No";
				echo "\nChord includes eleventh interval: ";
				echo ( $this->_readByte())  ?  "yes" : "No";
				echo "\nChord includes thirteenth interval: ";
				echo ( $this->_readByte())  ?  "yes" : "No";
			echo "\n\n";*/
			
			for($i=0;$i<7;$i++) 
			$this->_readByte();
			
			$this->_readByte(); // unknown data
			
			
			for($s = 0; $s < 7; $s++){	
				if($s < $this->strings[$track]){
					$strname = $this->_readByte();
					}else{
					$this->_readByte();
				}
			}
			
			
			$chordInfo['chord_fing'] =  (! $this->_readByte())  ?  "no" : "yes";
			return  $chordInfo;
		}
		
		function processBeatEffects(){
			
			$byte1 = $this->_readByte();
			$byte2  = 0;
			//echo "Beat effects bitmask:   ".$byte1."\n";
			
			if($byte1 & 1){	//Vibrato
				//echo ("(Vibrato)\n");
			}
			if($byte1 & 2){  // Wide vibrato
				//echo ("(Wide vibrato)\n");
			}
			if($byte1 & 4){ // Natural harmonic
				//echo ("(Natural harmonic)\n");
			}
			if($byte1 & 8){//Artificial harmonic
				//echo ("(Artificial harmonic)\n");
			}
			if($byte1 & 32){ // Tapping/popping/slapping
				//echo "tapping";
				$ns = $this->stringEffects[ $this->_readByte()];
				if($this->version < 4){
						//echo  "\nString effect value:\n";
						 $this->_readInt();
				}
			}
			
			if($byte1 & 64){ //Stroke effect
				//echo "Upstroke speed:".$this->_readByte()."\n";
				//echo "Downstroke speed:".$this->_readByte()."\n";
				$this->_readByte();
				$this->_readByte();
			}
			
			if($byte1 & 4){ //BEND
				//echo "\n\nBend".$this->bendEffects[ $this->_readByte()]."\n";
				//echo "Height:".$this->_readInt()."\n";

				//echo "TREMOLO";
				$points = $this->_readInt();
				
				//echo "Number of points: ".$points."\n";
				if($points>= 100 ){
					//echo "Too many bend points, aborting.\n";
					}else{
					
					for($bc = 0;$bc<$points;$bc++){
						//echo "Time relative to previous point: \n";
					$this->_readInt();
						//echo "Vertical Position \n";
						$this->_readInt();
						$type = $this->_readInt();
						
						if($type){
							//echo "Vibrato type:";
							//$this->vibratoType[$type];
						}
						
					}
					
				}
				
			}
			
		}
		
		function processMixTableChange(){
			//	echo "\n\n";
			//echo "Beat mix table change:\n";
			
			$change = Array();
			
			$change['instrument'] =   $this->_readByte();
			$change['volume'] =   $this->_readByte();
			$change['balance'] =   $this->_readByte();
			$change['chorus'] = $this->_readByte();
			$change['reverb'] =  $this->_readByte();
			$change['phaser'] =   $this->_readByte();
			$change['tremolo']  =   $this->_readByte();
			$change['tempo'] = $this->_readInt();
			
			if($change['volume'] >=0 ){
				$this->_readByte();
			}
			if($change['balance']   >=0 ){
				$this->_readByte();
			}
			if($change['chorus']  >=0 ){
				$this->_readByte();
			}
			if($change['reverb']  >=0  ){
				$this->_readByte();
			}
			if($change['phaser']  >=0 ){
				$this->_readByte();
			}
			if($change['tremolo'] >=0 ){
				$this->_readByte();
			}
			if($change['tempo']  >=0 ){
				$this->_readByte();
			}
			
			
		}
		
		function parseBend(){
			$type = $this->_readByte();
			if($type == 1)
			{
			//	echo ("Bend\n");
			}
			else if($type == 2)
			{
				//echo ("Bend and release\n");
			}
			else if($type == 3)
			{
				//echo ("Bend, release and bend\n");
			}
			else if($type == 4)
			{
				//echo ("Pre bend\n");
			}
			else if($type == 5)
			{
				//echo ("Pre bend and release\n");
			}
			else if($type == 6)
			{
				//echo ("Tremolo dip\n");
			}
			else if($type == 7)
			{
				//echo ("Tremolo dive\n");
			}
			else if($type == 8)
			{
			//	echo ("Tremolo release up\n");
			}
			else if($type == 9)
			{
			//	echo ("Tremolo inverted dip\n");
			}
			else if($type == 10)
			{
			//	echo ("Tremolo return\n");
			}
			else if($type == 11)
			{
			//	echo ("Tremolo release down\n");
			}
			//echo "height: \n";
			$this->_readInt();
			$points = $this->_readInt();
			//echo "Number of points: ". $points."\n";
			
			if($points>= 100){
				$this->error = true;
				return 1;
			}
			
			for($i=0;$i<$points;$i++){
			
				$this->_readInt();
				$this->_readInt();
				$this->_readByte();
				
			}
			
			
			
			
		}
		
		function processMeasuresAndTracks(){
			//echo "\nTOTAL - Measures {$this->musicInfo['measures_size']} - Track {$this->musicInfo['tracks_size']}\n\n";
			
			for($m=0;$m<$this->musicInfo['measures_size'];$m++){
				for($t=0;$t<$this->musicInfo['tracks_size'];$t++){
					$maxvoices =1;
					
					for($voice = 0 ;$voice < $maxvoices;$voice++){						
						$beats =  $this->_readInt();
						
						//if($m>704)
						//	echo "Number of beats: ".$beats ." | Measure:{$m} | Track:{$t} | Voice:{$voice} \n\n";
						
						
						for($beatsCount =0; $beatsCount<$beats;$beatsCount++){// for each beat
						
						if($this->error) return false;
							
							if(((time() - $this->readingTime) / 60) > 2){
							echo " Timeout ";
							$this->error = true;
							return 0;
							}
							
							//echo "Number of beats: ".$beats ." | Measure:{$m} | Track:{$t} | Voice:{$voice} | Beat: {$beatsCount} \n\n";
							
							if($beats>1000){
							//print_r($this->beats);
							return 0;
							}
							$bytemask =  $this->_readByte() & 0xFF;
							$beatInfo = Array();
							
							
							if($bytemask & 0x40){	 // rest
								//echo "(rest) Rest Type: ";
								$beatInfo['rest'] = (! $this->_readByte()) ? "empty" : "rest";
							}
							
							if($bytemask & 0x01){	//Dotted note
								$beatInfo['dotted'] = true;
							}
							
							$beatInfo['duration'] =  $this->_readByte();
							//(-2 = whole note, -1 = half note, 0 = quarter note, 1 = eighth note, 2 = sixteenth note, 3 = thirty-second note, 4 = sixty-fourth note)
							
							if($bytemask & 0x20){	//Beat is an N-tuplet
								$beatInfo['N-tuplet'] = $this->_readInt();
							}
							
							if($bytemask & 0x02 ){	//Chord diagram
								$diagram =  $this->_readByte();
								//echo "\nChord Diagram {$diagram} \n";
								if($diagram == 0){ 
									$beatInfo = array_merge($beatInfo,$this->processDiagram0($t));
									}else{						
									$beatInfo = array_merge($beatInfo,$this->processDiagram1($t));
								}
							}
							//testando novo
							
							if($bytemask & 0x04 ){	//Beat has text: 
								$this->_readInt();
								$size = $this->_readByte();
								$data = ($size>0) ? fread($this->file, $size) : "";
								$beatInfo['text'] = $data;
							}
							
							if($bytemask & 0x08 ) { 	 //Beat Has Effect 
								$this->processBeatEffects();
							}
							
							if($bytemask & 0x10){   // mix table change
								$this->processMixTableChange();
							}
							
							$usedstring = $this->_readByte() ;
							//For each of the 7 possible usable strings
							$r =0;
							for($ctr4 = 0, $bit = 64; $ctr4 < 7; $ctr4++, $bit >>= 1 ){	
								if($bit & $usedstring)	{
									$strnum = $ctr4;
									$beatInfo['group'][$strnum]['string'] = ($ctr4+1);
									$beatInfo['group'][$strnum]['duration'] = $beatInfo['duration'];
									
									$bytemask = $this->_readByte();
									if($bytemask & 0x20){
										$nt  =  $this->_readByte();
										if($nt== 1 ){
											$beatInfo['group'][$strnum]['noteType'] = "normal";
											}elseif($nt == 2 ){
											$beatInfo['group'][$strnum]['noteType'] = "tie";
											}else{
											$beatInfo['group'][$strnum]['noteType'] = "dead";
										}
									}
									
									if(($bytemask & 0x01) and $this->version <5 ){
										$beatInfo['group'][$strnum]['independentTime'] =   $this->_readByte()."|".$this->_readByte();
									}
									
									if($bytemask & 0x10){
										$dinb = $this->_readByte();
										if($dinb>0) $dinb--;
										if(isset($this->dinamics[( $dinb)%8])){
											$beatInfo['group'][$strnum]['dynamic'] =  ($this->dinamics[( $dinb)%8]);
										}else{
											$this->error =1;
											return 0;
										}
									}
									
									if($bytemask & 0x20){
										$beatInfo['group'][$strnum]['fret'] = $this->_readByte();
									}
									
									if($bytemask & 0x80){
										$beatInfo['group'][$strnum]['leftFingering'] = $this->_readByte();
										$beatInfo['group'][$strnum]['rightFingering'] = $this->_readByte();
									}
									
									if(($bytemask & 128) and ($this->version)> 5 ){
										$this->_readInt();
										$this->_readInt();
									}
									
									if(($this->version)> 5 ){
										$this->_readByte();
									}
									
									if($bytemask & 0x08){	
										$bitmsk = $this->_readByte();
										//echo "Note effect bitmask: ".$bitmsk."\n";
										//	echo "<hr>";
										$bitmsk2 = 0;
										
										if($bitmsk & 1){ // to do 
											//echo "bend\n";
											$this->parseBend();
										}
										if($bitmsk & 2){
											//echo "Hammer on/pull off from current note\n";
										}
										if($bitmsk & 4){
											//	echo "slide from current note\n";
										}
										if($bitmsk & 8){
											//echo "(Let ring)\n";
										}
										if($bitmsk & 16){ // to do
											$grace = Array();
											$grace['fret'] = $this->_readByte();
											$dinamics = $this->_readByte();
												if(!isset($this->dinamics[(($dinamics- 1) % 8)])){
												$this->error=true;
												return false;
												}
												
											$grace['dinamic'] = $this->dinamics[(($dinamics - 1) % 8)];
											$strname = $this->tracks[$t]['strings'][$strnum];
											$grace['string'] = $strnum;
											$grace['beatName'] = $this->addSemitone($strname,true,$grace['fret']-1);
											if($this->version <5){
												$this->_readByte(); // unknown data
											}
											$grace['duration'] = $this->_readByte();
											$beatInfo['group'][$strnum]['grace'] = $grace;
											//echo "(Grace note)\n";
										}
										
									}// note effects
									
								}// if string
							}//For each of the 7 possible usable strings
							
							//..................................................
							// colocar o nome da nota		
							//..................................................		
							if(isset($beatInfo['group'])){
								if(count($beatInfo['group']) == 1){
									$beatInfo = array_merge($beatInfo,$beatInfo['group'][key($beatInfo['group'])]);
									if(isset($beatInfo['fret'])){
										$stringtmp = $this->tracks[$t]['strings'];
										$tmpn = $beatInfo['string'];
										unset($beatInfo['group']);
										
										if(!isset($stringtmp[$tmpn-1])){
											$this->error=1;
											return 0;
										}
										
										$beatInfo['chordA'] = $stringtmp[$tmpn-1];
										$beatInfo['fret'] = $beatInfo['fret'];
										$beatInfo['beatName']   = $this->addSemitone($stringtmp[$tmpn-1],true,$beatInfo['fret']);	
									}
									}else{
									foreach($beatInfo['group'] as $id=> $val){
										$stringtmp = $this->tracks[$t]['strings'];
										$tmpn = $val['string'];
										
										if(!isset($val['fret']))	$val['fret'] =1;
										if(!isset($stringtmp[$tmpn-1])) $stringtmp[$tmpn-1] = "C";
										
										$beatInfo['group'][$id]['beatName']   = $this->addSemitone($stringtmp[$tmpn-1],true,$val['fret']);	
									}
								}
							}
							//..................................................
							// colocar o nome da nota		
							//..................................................
							
							//	print_r($beatInfo);
							$this->beats[$t][$m][] = $beatInfo;
						}// end each beat
						
						//echo "-->";
						
					}// each voice
					
				}//each track
				
			}// each measure
			
			
		}
		
	}
?>