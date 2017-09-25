<?php
	
	function flautaDoce($str){
		return $str;
		return str_replace("\n ","",str_replace("  "," ",str_replace(explode(",","DO2,RE2,MI2,FA2,SOL2,LA2,SI2,DO#2,RE#2,MI#2,FA#2,SOL#2,LA#2,SI#2"),"\n",$str)));
	}
	
	function getFinalFormat($str){
		$i = 0;
		$finalNotes='';
		foreach($str as $v){
			$finalNotes.= ($i++ % 2	 == 0) ? " \n " : "";
			foreach($v as $val){
				if(isset($val['rest'])){
					$finalNotes.= " \n ";
					$i=1;
					}else{
					if(!isset($val['group'])){
						$finalNotes.= $val['beatName']." ";
						}else{
						$finalNotes.= reset($val['group'])['beatName']." ";
					}
				}
				
			}
		}
		return $finalNotes;
	}
	
	function changeFormat($str){
		$return = '';
		
		$str =  str_replace(explode(",","C,D,E,F,G,B,A"),explode(",","do,re,mi,fa,sol,si,la"),$str);
		$str = explode(" ",$str);
		
		foreach($str as $val){
			if(preg_match('/\d+/', $val,$n)){
				if($n[0] == 3){
					$val = str_replace("3","",$val);
					$return.= strtolower($val)." ";
					}else{
					$val = str_replace("4","",$val);
					$return.= strtoupper($val)." ";
				}
				}else{
				$return.= $val." ";
			}
		}
		
		return $return;
	}
	
	function increaseSemitone($note,$recursive = false, $repeatN=''){
		
		$number = '';
		
		if($recursive){
			for($i = 0 ;$i<$repeatN;$i++){
				$note = increaseSemitone($note);
			}
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
	
	function reduceSemitone($note,$recursive = false, $repeatN=''){
		$number = '';
		
		if($recursive){
			
			for($i = 0 ;$i<$repeatN;$i++)
			$note = reduceSemitone($note);
			
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
			if($index == 0){
				if(empty($numbers)) {
					return $notes[count($notes)-1]."1";
					}else{
					return $notes[count($notes)-1].($numbers-1);
				}
				}else{
				return ($notes[$index-1]).$numbers;
			}
			
		}
		
	}
	
	function reduceFinalSemitone($str,$times=1){
		$return = '';
		$str = explode(" ",$str);
		foreach($str as $val){
			if($val == "\n"){
				$return.="\n";
				}elseif(empty($val)){
				
				}else{
				$return.= reduceSemitone($val,true,$times)." ";
			}
		}
		return $return;
	}
	
	function increaseFinalSemitone($str,$times=1){
		$return = '';
		$str = explode(" ",$str);
		foreach($str as $val){
			if($val == "\n"){
				$return.="\n";
				}elseif(empty($val)){
				
				}else{
				$return.= increaseSemitone($val,true,$times)." ";
			}
		}
		return $return;
	}
	
?>