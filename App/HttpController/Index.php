<?php

namespace App\HttpController;

use App\EmailValidator;
use App\Model\Log;
use App\Model\LogSummary;
use App\View;
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
        $ev = new EmailValidator();
        if (!$ev->isValidEmail($email)) {
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
     * こんにちはこんにちは
     */
    function hello()
    {
        $res = $this->response()->withHeader('Content-type', 'text/html;charset=utf-8');
        $res->write("hello");
    }

}