<?php

require __DIR__ . '/../vendor/autoload.php';

require_once "../../App/Db.php";
require_once "../../App/EmailValidator.php";
require_once "../../App/Model/LogSummary.php";
require_once "../../App/Model/Log.php";
require_once "../../App/View.php";
const EASYSWOOLE_ROOT = "../../";
const SWOOLE = false;

$app = new \Slim\App();

$app->get('/', '\App\Index:index');

$app->post('/post', '\App\Index:post');

$app->get('/hello', function ($req, $res) {
    $res->write("hello");
});

$app->run();