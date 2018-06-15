<?php

namespace App;

use easySwoole\Cache\Cache;

class EmailValidator
{
    /**
     * Emailを検証
     *
     * @param $email
     * @return bool
     */
    function isValidEmail($email)
    {
        // filter_varを使う事に様々な議論はあるが、まあ…。
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            echo "fail at filter_var" . PHP_EOL;
            var_dump($email);
            return false;
        }

        // 本当はもっとマシに書くけど、ここではまあ…。
        // ベンチに差がですぎるので、コメントアウトしてベンチしたほうが良い
        list($name, $domain) = explode("@", $email);
        if (!$this->isProbablyExistsMailServer($domain)) {
            echo "fail at check domain" . PHP_EOL;
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
        if (SWOOLE) {
            if (Cache::has($prefix . $host)) return Cache::get($prefix . $host);

            echo "isProbablyExistsMailServer cache miss" . PHP_EOL;

            $dns_ip = static::getHostByNameWithCache("dns.google.com");

            $cli = new \Swoole\Coroutine\Http\Client($dns_ip, 443, true);
            $cli->setHeaders([
                'Host' => "dns.google.com",
                "User-Agent" => 'php',
                'Accept' => 'application/json',
//            'Accept-Encoding' => 'gzip', // 動かぬ環境がある、ビルドの問題?
            ]);
            $cli->set(['timeout' => 1]);
            $encoded_host = urlencode($host);
            $cli->get("/resolve?name={$encoded_host}&type=MX");
//        var_dump($cli->body);
            $mx_records = json_decode($cli->body, true);
            $cli->get("/resolve?name={$encoded_host}&type=A");
//        var_dump($cli->body);
            $a_records = json_decode($cli->body, true);

            $cli->close();


        } else {
            $encoded_host = urlencode($host);

            $mx_records_json = file_get_contents("https://dns.google.com/resolve?name={$encoded_host}&type=A");
            $a_records_json = file_get_contents("https://dns.google.com/resolve?name={$encoded_host}&type=A");
            $mx_records = json_decode($mx_records_json, true);
            $a_records = json_decode($a_records_json, true);
        }

        if (isset($mx_records['Answer']) || isset($a_records['Answer'])) {
            if (SWOOLE) {
                Cache::set($prefix . $host, true, 3600);
            }
            return true;
        } else {
            if (SWOOLE) {
                Cache::set($prefix . $host, false, 3);
            }
            return false;
        }
    }

    /**
     * ホスト名からIPを引く
     *
     * ここではdns.google.comを引くためだけにある
     * easyswoole/cache必須
     *
     * @param $host
     * @return string
     */
    protected function getHostByNameWithCache($host)
    {
        $prefix = "gHBNWC-";

        if (Cache::has($prefix . $host)) return Cache::get($prefix . $host);
        echo "getHostByNameWithCache cache miss" . PHP_EOL;

        $ip = gethostbyname($host);
        echo "getHostByNameWithCache {$host} is {$ip}" . PHP_EOL;
        Cache::set($prefix . $host, $ip, 3600);
        return $ip;
    }

}