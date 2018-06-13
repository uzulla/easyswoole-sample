<?php

namespace App;

use App\Model\Log;
use App\Model\LogSummary;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

class Index
{
    function index(Request $request, Response $response, $args)
    {
        $v = new View();
        $response->write($v->render('index.twig', ['num' => LogSummary::count()]));
        return $response;
    }

    function post(Request $request, Response $response, $args)
    {
        $req = $request;
        $res = $response;

        $post = $req->getParsedBody();
        $email = $post['email'];
        echo $email;

        $v = new View;
        $ev = new EmailValidator();
        if (!$ev->isValidEmail($email)) {
            $res->withHeader('Content-type', 'text/html;charset=utf-8');
            $res->write($v->render('invalid.twig', []));
            return;
        }

        $res->withHeader('Content-type', 'text/html;charset=utf-8');
        $res->write($v->render('done.twig', []));

        $log = new Log();
        $log->set($email);
    }
}