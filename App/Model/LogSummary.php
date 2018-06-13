<?php

namespace App\Model;

use App\Db;
use easySwoole\Cache\Cache;

class LogSummary
{
    static $num = 0;

    /**
     * キャッシュ上のサマリを保存する
     */
    public static function refresh()
    {
        /** @var \PDO $pdo */
        $pdo = Db::getPDO();
        $stmt = $pdo->prepare("SELECT count(*) AS count FROM `log` ");
        $stmt->execute();

        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        Cache::set('count', $row['count']);
    }

    /**
     * キャッシュ上のサマリを取得する
     * @return mixed
     */
    public static function get()
    {
        return Cache::get('count');
    }

}