<?php

use Phalcon\Mvc\Controller;
use Ares333\CurlMulti\Core;

class IndexController extends Controller {

    public function indexAction() {
        $url = array(
            'http://china.nba.com/nuggets/',
            'http://china.nba.com/static/data/league/playerlist_A.json'
        );
        $curl = new Core ();
        foreach ($url as $v) {
            $curl->add(array(
                'url' => $v,
                'args' => array(
                    'test' => 'this is user arg for ' . $v
                )
                    ), function ($r, $args) {
                echo "success, url=" . $r ['info'] ['url'] . "\n";
                print_r(array_keys($r));
                print_r($args);
            });
        }
        // start spider
        $curl->start();

        exit();
    }

}
