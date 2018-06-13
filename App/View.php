<?php

// Twigのシングルトンを作る
namespace App;

class View
{
    /** @var \Twig_Environment|null */
    static $twig = null;

    public function __construct()
    {
        if(static::$twig===null) {
            echo "New twig instance".PHP_EOL;
            $loader = new \Twig_Loader_Filesystem(EASYSWOOLE_ROOT."/templates");
            $twig = new \Twig_Environment($loader, array(
                'cache' => EASYSWOOLE_ROOT.'/twig_cache', // 有効にすると、cacheを消した上でrestartが必要
            ));
            static::$twig = $twig;
        }
    }

    public function render(string $path, array $param):string
    {
        $template = static::$twig->load($path);
        return $template->render($param);
    }
}
