<?php

namespace App\HttpController;

use App\Model\Log;
use App\Model\LogSummary;
use App\View;
use easySwoole\Cache\Cache;
use EasySwoole\Core\Http\AbstractInterface\Controller;
use EasySwoole\Core\Swoole\Task\TaskManager;
use EasySwoole\Core\Http\Request;
use EasySwoole\Core\Http\Response;

class Index extends Controller
{
    /**
     * フォームを表示
     */
    function index()
    {
        /** @var Response $res */
        $res = $this->response();

        $v = new View;
        $res->withHeader('Content-type', 'text/html;charset=utf-8');
        $res->write($v->render('index.twig', ['num' => LogSummary::get()]));
    }

    /**
     * 投稿を保存
     */
    function post()
    {
        /** @var Request $req */
        $req = $this->request();
        /** @var Response $res */
        $res = $this->response()->withHeader('Content-type', 'text/html;charset=utf-8');

        $email = $req->getParsedBody('email');

        $v = new View;

        if(!$this->isValidEmail($email)) {
            $res->withHeader('Content-type', 'text/html;charset=utf-8');
            $res->write($v->render('invalid.twig', []));
            return;
        }

        $res->withHeader('Content-type', 'text/html;charset=utf-8');
        $res->write($v->render('done.twig', []));

        TaskManager::async(function () use ($email) {
            // ここはレスポンスを返した後にTaskで非同期処理
            // クロージャ変数が引き継げるのでラク
            $log = new Log;
            $log->set($email);
        });
    }

    /**
     * Emailを検証
     *
     * @param $email
     * @return bool
     */
    protected function isValidEmail($email)
    {
        // filter_varを使う事に様々な議論はあるが、まあ…。
        if(filter_var($email, FILTER_VALIDATE_EMAIL)===false){
            echo "fail at filter_var".PHP_EOL;
            var_dump($email);
            return false;
        }

        // 本当はもっとマシに書くけど、ここではまあ…。
        list($name, $domain) = explode("@", $email);
        if(!$this->isProbablyExistsMailServer($domain)){
            echo "fail at check domain".PHP_EOL;
            return false;
        }

        return true;
    }

    /**
     * 指定のホスト名が「メールを受け取れそうか？」調べる
     *
     * MXがあれば多分届く、MXがなくてもA(やCNAME)があれば大体届く
     * MXをDNSで非同期に取りに行くものがないので、DNS over HTTPSをつかう例
     *
     * @param $host
     * @return bool
     */
    protected function isProbablyExistsMailServer($host)
    {
        $prefix = "iPEMS-";
        if (Cache::has($prefix.$host)) return Cache::get($prefix.$host);

        echo "isProbablyExistsMailServer cache miss".PHP_EOL;

        $dns_ip = static::getHostByNameWithCache("dns.google.com");

        $cli = new \Swoole\Coroutine\Http\Client($dns_ip, 443, true);
        $cli->setHeaders([
            'Host' => "dns.google.com",
            "User-Agent" => 'php',
            'Accept' => 'application/json',
            'Accept-Encoding' => 'gzip',
        ]);
        $cli->set(['timeout' => 1]);
        $encoded_host = urlencode($host);
        $cli->get("/resolve?name={$encoded_host}&type=MX");
        $mx_records = json_decode($cli->body, true);
        $cli->get("/resolve?name={$encoded_host}&type=A");
        $a_records = json_decode($cli->body, true);

        $cli->close();


        if (isset($mx_records['Answer']) ||isset($a_records['Answer'])){
            Cache::set($prefix.$host, true, 3600);
            return true;
        }else{
            Cache::set($prefix.$host, false, 3600);
            return false;
        }
    }

    /**
     * ホスト名からIPを引く
     *
     * ここではdns.google.comを引くためだけにある
     *
     * @param $host
     * @return string
     */
    function getHostByNameWithCache($host)
    {
        $prefix="gHBNWC-";
        if (Cache::has($prefix.$host)) return Cache::get($prefix.$host);

        echo "getHostByNameWithCache cache miss".PHP_EOL;

        $ip = gethostbyname($host);
        Cache::set($prefix.$host, $ip, 3600);
        return $ip;
    }

    /**
     * こんにちはこんにちは
     */
    function hello()
    {
        $res = $this->response()->withHeader('Content-type', 'text/html;charset=utf-8');
        $res->write("hello");
    }

}