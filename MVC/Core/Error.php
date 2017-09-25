<?php

namespace Core;

class Error{

  /*
  Esta seria o metodo que seria chamado pelo proprio sistema quando ocorrer um erro, aqui ele irá receber 4 variaveis, o nivel do erro, a mensagem do erro, o arquivo que deu erro e a linha de onde esta o erro
  */
  public static function errorHandle($level, $message, $file, $line){
	  if(error_reporting() !== 0){
		  throw new \ErrorException($message, 0, $level, $file, $line);
	  }//Tenha em mente que qualquer erro que acontece no sistema, vem parar primeiro aqui, em seguida usamos o throw new chamando a classe ErrorException, esta classe é do proprio sistema. Quando ele recebe esses dados, o sistema automaticamente busca pela classe que declaramos logo abaixo.
  }
  
  /*
  Esta tambem seria um metodo que seria chamado pelo proprio sistema quando uma excessão ocorrer, aqui iremos mostrar para o nosso usuario o erro em um formato na qual ele possa entender. 
  */
  public static function exceptionHandle($exception){
	  
	  //Aqui pegamos o codigo que nos foi informado, este seria o codigo de HTTP
	  $code = $exception->getCode();
	  if($code != 404){
		  $code = 500;//Caso o erro for diferente de 404 (nao encontrado), ele se transforma em um erro 500 (problema interno)
	  }
	  http_response_code($code);//Informamos ao navegador o tipo de erro que ocorreu no sistema!
	  
	  if(\App\Config::SHOW_ERRORS){//Aqui verificamos se a constante de erros é verdadeira, se for acreditamos estar no desenvolvimento, sendo assim precisamos ver os erros de cara na tela.
	  
	  echo "<h1>Erro Fatal</h1>";
	  echo "<p>Uncaught exception: '" . get_class($exception) . "'</p>";
	  echo "<p>Message: '" . $exception->getMessage() . "'</p>";
	  echo "<p>Stack trace: <pre>" . $exception->getTraceAsString() . "</pre></p>";
	  echo "<p>Thrown in '" . $exception->getFile() . "' on line " . $exception->getLine() . "</p>";
	  
	  }else{//Caso nao ele esconde o erro e salva este erro em um arquivo de log na pasta log
		  
		$log = dirname(__DIR__) . '/logs/' . date('Y-m-d') . '.txt';
		ini_set('error_log', $log);
		  
	    $message = "Uncaught exception: '" . get_class($exception) . "'";
	    $message .= " with Message: '" . $exception->getMessage() . "'";
	    $message .= "\nStack trace: " . $exception->getTraceAsString() . "";
	    $message .= "\nThrown in '" . $exception->getFile() . "' on line " . $exception->getLine();
		
		error_log($message);
		
		//echo "<h1>Erro Fatal</h1>";
		
		//Aqui criamos uma estrutura de if else para mostrar como tratamos os erros por aqui, se for 404 mostra uma pagina difernete caso for um erro de 500
		/*
		if($code == 404){
			echo "<h1>Pagina nao encontrada</h1>";
		}else{
			echo "<h1>Um erro interno ocorreu</h1>"
		}
		*/
		
		View::renderTemplate("$code.html");//Aqui vamos puxar os arquivos de erro, se a variavel code estiver armazenando 404 ele puxa 404.html, caso for diferente só pode ser um erro 500, sendo assim ele puxa o 500.html
		  
	  }
  }

}

//Lembre-se de que esta classe de erro e esses metodos só serão executados quando registrarmos o tratamento de erros no nosso front controller.

?>