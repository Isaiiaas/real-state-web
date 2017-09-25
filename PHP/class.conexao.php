<?php
	
	define('DB_HOST',  '127.0.0.1');
	define('DB_NAME',    '******');
	define('DB_USER',  '******');
	define('DB_PASS', '********');
	
	class conexao
	{
		public static $link = NULL;
		
		public static function verificar(){
			if(self::$link == NULL) self::abrir();
			
			try {
				self::$link->query("SELECT 1");
				} catch(PDOException $e) { // Caso a conexao tenha sido fechada, reconecta ...
				self::abrir();
			}
			
			return self::$link;
		}
		
		public static function fechar(){
				self::$link = NULL;
		}
		
		public static function abrir(){
			try {
				self::$link = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8', DB_USER, DB_PASS, array( PDO::ATTR_PERSISTENT => false));	
				self::$link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				self::$link->exec('SET NAMES utf8');
				return true;
				} catch (PDOException $e) { 
					exit("Ops! =( ");
					error_log($_SERVER["SCRIPT_FILENAME"].' - '.$e->getMessage()); 				
			}
		}
		
		public static function num_rows($_qry){
			try {
				$_qry = strtolower($_qry);
				
				if(preg_match('/select \*/',$_qry)){
					$_qry = str_replace("select *","SELECT *,count( * ) AS num_rows ",strtolower($_qry));
					}else{
					$_qry = str_replace("select","SELECT count( * ) AS num_rows,",strtolower($_qry));
				}
				$n =  self::$link->query($_qry)->fetch(PDO::FETCH_ASSOC);
				return $n['num_rows'];				
			}
			catch(PDOException $e){// Caso tenha algum erro - query, conexão e etc
				error_log($_SERVER["SCRIPT_FILENAME"].' - '.$_qry);
				return false;
			}
		}
		
		public static function update($_qry){
			try {
				
				return self::$link->query($_qry)->rowCount();
				
			}
			catch(PDOException $e) {
				error_log($_SERVER["SCRIPT_FILENAME"].' - '.$_qry);
				return false;
			}
		}
		
		public static function insert($_qry){
			try {			
				self::$link->query($_qry);
				return self::$link->lastInsertId();
			}
			catch(PDOException $e) {
				error_log($_SERVER["SCRIPT_FILENAME"].' - '.$_qry);
				return false;
			}
		}
		
		public static function fetch_assoc_one($_qry){
			try {
				return self::$link->query($_qry)->fetch(PDO::FETCH_ASSOC);
				
			}
			catch(PDOException $e){
				error_log($_SERVER["SCRIPT_FILENAME"].' - '.$_qry);
				return false;
			}
		} 
		
		public static function fetch_assoc($_qry){
			try {
				
				$out = array();
				
				$sql = self::$link->query($_qry);
				while($ret = $sql->fetch(PDO::FETCH_ASSOC)) $out[] = $ret;
				
				$sql->closeCursor();
				return $out;
				
			}
			catch(PDOException $e){			
				error_log($_SERVER["SCRIPT_FILENAME"].' - '.$_qry);
				return false;
			}
		}
		
	}
	
	//example usage
	
	conexao::abrir(); //open the con
	$result = conexao::fetch_assoc("select * from table");
	print_r($result);
	conexao::fechar();// closing the con 
?>