<?php

//Essa classe Model funciona da mesma forma que as classes Controller e Views existente dentro dessa mesma pasta, o unico intuito de criar esta classe é para usar uma função chamada getDB responsavel por retornar uma instancia do banco de dados.

//Com isso basta fazermos com que qualquer classe existente na pasta 'App/Models' tenha herança dessa classe (extends), com isso todas terão acesso ao metodo getDB e basta chama-lo para fazer uma conexão com banco de dados.

namespace Core;

use PDO;

use App\Config;//Temos que usar o arquivo confi para poder selecionar as constantes abaixo

abstract class Model
{
    //Classe responsavel por fazer uma conexão com banco de dados e retornar esta conexão.
    protected static function getDB() {

        static $db = null;

        if ($db === null) {

            try {

                $host = Config::DB_HOST;
                $dbname = Config::DB_NAME;

                $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8";//montamos na variavel a configuração aonde aqui eu seleciono aquelas constantes declaradas no arquivo de config.php

                $db = new PDO($dsn, Config::DB_USER, Config::DB_PASSWORD);

                $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);//Para que consiga captar todos os erros gerados pelo banco de dados precisamos setar somente este atributo no PDO, e assim que o mesmo encontrar um erro, ele mostra esse erro e as funções existentes na classe Error.php serão chamadas.

            } catch (PDOExeception $e) {
                echo $e->getMessage();
            }

        }

        return $db;

    }


    /*Helper classes*/

    public static function allByQuery($query){
        $db = Self::getDB();
        $link = $db->query($query);
        $results = $link->fetchAll(PDO::FETCH_ASSOC);

        return $results;
    }
	
	public static function allByQueryInsert($query){
        $db = Self::getDB();
        $link = $db->query($query);
        return $db->lastInsertId();
    }
	
	public static function allByQueryUpdate($query){
        $db = Self::getDB();
        $link = $db->query($query);
        return $link->rowCount();
    }

    public static function _get($table, $where = "", $data = "*") {

        $db = Self::getDB();
        $query = "SELECT {$data} FROM {$table}";
        $query .= empty($where) ? "" : " WHERE " . $where;

        //print $query;
        $link = $db->query($query);

        $results = $link->fetchAll(PDO::FETCH_ASSOC);

        return $results;


    }

    public static function _insert($table, $values) {

        $db = Self::getDB();
        $values = self::arrayToSql($values);
        $query = "INSERT INTO {$table} SET {$values}";
        $db->query($query);
        return $db->lastInsertId();


    }

    public static function _update($table, $where, $values) {
        $db = Self::getDB();
        $values = self::arrayToSql($values);
        $query = "UPDATE {$table} SET {$values}";
		if(empty($where)){
			return false;
		}
        $query .= " WHERE " . $where;
        $query = $db->query($query);
        return $query->rowCount();
    }
	
	public static function _delete($table, $where) {
        $db = Self::getDB();
        $query = "DELETE FROM {$table}";
		if(empty($where)){
			return false;
		}
        $query .= " WHERE " . self::arrayToSql($where);
        $query = $db->query($query);
        return $query->rowCount();
    }

    private static function arrayToSql($data) {
        $fields = array();
        foreach ($data as $key => $value) {
            $fields[] = $key . " = '" . addslashes($value) . "'";
        }

        $fields = implode(', ', $fields);
        return $fields;
    }

}

?>