<?php
	/*
	Class Screenshare
	Intreviewed by Matt - 05/10/2017
	*/
	
	class ScreenShare{
		
		var $houses =  Array("condo-house","condo","condo-townhouse", "Single", "double");
		
		public function fizzBuzz($starting, $ending){
			
			//for(;$tarting<$ending;$starting++){
			while($starting<$ending){
				
				
				if ($starting %3 == 0 ){
					echo "fizz";
				}
				if( $starting % 5 == 0){
					echo "buzz";
				}
				
				$starting++;
			}
			
			return true;
		}
		
		public function isParabola($string){
			
			$inicialString = $string;
			$reversedString = "";
			
			//reversing the string
			$strcount = strlen($string)-1; 
			for($i=$strcount;$i>=0;$i--){
				$reversedString.= $inicialString[$i];
			}
			
			if($reversedString == $inicialString){
				return true;
				}else{
				return false;
			}
		}
		
		public function __construct($input=''){
			
			$result = Array();
			//$input = "condo";
			
			
			foreach($this->houses as $id=>$val){
				if(!preg_match('/'.$input.'/i',$val)){
					$result[] = $val;
				}
			}
			
			
			return $result;
			
		}
		
		
	}
	
	$screenSh = new ScreenShare();
	$screenSh->fizzBuzz(1,50);
	
	/*
		$_SERVER['REMOTE_ADDR'];
		
	*/
	
?>