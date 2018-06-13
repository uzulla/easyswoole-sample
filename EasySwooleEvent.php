<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/1/9
 * Time: 下午1:04
 */

namespace EasySwoole;

use App\Model\LogSummary;
use easySwoole\Cache\Cache;
use easySwoole\Cache\Connector\Redis;
use easySwoole\Cache\Connector\Files;
use \EasySwoole\Core\AbstractInterface\EventInterface;
use \EasySwoole\Core\Swoole\ServerManager;
use \EasySwoole\Core\Swoole\EventRegister;
use \EasySwoole\Core\Http\Request;
use \EasySwoole\Core\Http\Response;
use EasySwoole\Core\Swoole\Time\Timer;

Class EasySwooleEvent implements EventInterface
{

    public static function frameInitialize(): void
    {
        echo "frameInitialize".PHP_EOL;
        date_default_timezone_set('Asia/Tokyo');

        $fileSystemOptions = [
            'expire'        => 0,     // 缓存过期时间
            'cache_subdir'  => true,  // 开启子目录存放
            'prefix'        => '',    // 缓存文件后缀名
            'path'          => __DIR__.'/cache',    // 缓存文件储存路径
            'hash_type'     => 'md5', // 文件名的哈希方式
            'data_compress' => false, // 启用缓存内容压缩
            'thread_safe'   => false, // 线程安全模式
            'lock_timeout'  => 3000,  // 文件最长锁定时间(ms)
        ];
        $FilesConnector = new Files($fileSystemOptions);
        Cache::init($FilesConnector);

        define("SWOOLE", true);
    }

    public static function mainServerCreate(ServerManager $server, EventRegister $register): void
    {
        echo "mainServerCreate".PHP_EOL;
//        var_dump(debug_backtrace());

        // ワーカー起動時に、「サマリを定期的に集計する」サービスを登録
        $register->add($register::onWorkerStart, function (\swoole_server $server, $workerId) {
            if ($workerId == 0) { // メインプロセスでのみ動くように
                Timer::loop(5000, function () {
                    // echo('.'); //tick
                    LogSummary::refresh();
                });
            }
        });
    }

    public static function onRequest(Request $request, Response $response): void
    {
        // TODO: Implement onRequest() method.
    }

    public static function afterAction(Request $request, Response $response): void
    {
        // TODO: Implement afterAction() method.
    }
}