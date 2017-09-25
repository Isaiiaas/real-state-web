<?php

namespace App\Models;

use Core\Model;


class Example extends Model
{

    public static $table = "example";

    public static function getAll() {
        return Model::_get(Self::$table);
    }

    public static function insert($data) {
        Model::_insert(self::$table, $data);
    }

    public static function update($data, $id){
        //$data = array(array("colunm" => "new value"), array("other_colunm" => "new value"));
        Model::_update(self::$table, "id={$id}", $data);
    }

}

?>