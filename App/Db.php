<?php

namespace App;

class Db
{
    static $pdo = null;

    public static function getPDO()
    {
        // 本当はPoolの例を書きたかったが時間切れ
        if(is_null(static::$pdo)) {
            echo "New DB Conn".PHP_EOL;
            $pdo = new \PDO('mysql:host=127.0.0.1;dbname=swoole', 'swoole', 'swoolepass');
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
            $pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
            static::$pdo = $pdo;
        }

        return static::$pdo;
    }

}