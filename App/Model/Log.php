<?php

namespace App\Model;

use App\Db;

class Log
{
    public function set($str)
    {
        static $stmt;

        if (!$stmt) {
            $pdo = Db::getPDO();
            $stmt = $pdo->prepare("INSERT INTO `log` (`text`) VALUES (:text);");
        }
        $stmt->bindValue('text', $str, \PDO::PARAM_STR);
        $stmt->execute();

// ホントはasyncなmysqlがSwooleにもあるんだけど、使いづらいし、
// これはタスクワーカーだし、コールバック地獄になるので…
// まあ、Promiseみたいなのをつかえばよいと思う。
//        try{
//            Db::createNewConn(function($db) {
//                $db->query(
//                    "SELECT count(*) as count from `log`",
//                    function (swoole_mysql $db, $r) {
//                    if ($r === false) {
//                        var_dump($db->error, $db->errno);
//                    } elseif ($r === true) {
//                        var_dump($db->affected_rows, $db->insert_id);
//                    }
//                    var_dump($r);
//                    $db->close();
//                });
//            }
//        }catch(\Exception $e){
//
//        }

    }
}