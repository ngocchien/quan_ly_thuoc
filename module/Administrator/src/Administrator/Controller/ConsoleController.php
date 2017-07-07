<?php
/**
 * Created by PhpStorm.
 * User: chiennn
 * Date: 06/07/2017
 * Time: 22:44
 */

namespace Administrator\Controller;

use Zend\Mvc\Controller\AbstractActionController;

class ConsoleController extends AbstractActionController
{
    public function __construct()
    {
        echo '<pre>';
        print_r('chiennnn');
        echo '</pre>';
        die();
        if (PHP_SAPI !== 'cli') {
            die('Only use this controller from command line!');
        }
        ini_set('default_socket_timeout', -1);
        ini_set('max_execution_time', -1);
        ini_set('mysql.connect_timeout', -1);
        ini_set('memory_limit', -1);
        ini_set('output_buffering', 0);
        ini_set('zlib.output_compression', 0);
        ini_set('implicit_flush', 1);
    }

    public function indexAction()
    {
        echo '<pre>';
        print_r(222);
        echo '</pre>';
        die();
        die();
    }

    public function migrateAction()
    {
        echo '<pre>';
        print_r(222);
        echo '</pre>';
        die();
        die();
    }
}