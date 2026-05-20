<?php

namespace Domain\App\Helpers;

class SQL {

    public static function getQuery($sql){
        $query = str_replace(array('?'), array('\'%s\''), $sql->toSql());
        $query = vsprintf($query, $sql->getBindings());
        return $query;
    }

}
