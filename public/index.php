<?php
use Zend\Mvc\Application;

try{
    //Root path
    define('ROOT_PATH', realpath(dirname(__FILE__) . '/..'));

    define('CONFIG_PATH', ROOT_PATH . '/config');

    date_default_timezone_set('Asia/Ho_Chi_Minh');

// Define application environment
    defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'development'));


    if (APPLICATION_ENV != 'production') {
        error_reporting(E_ALL);
        ini_set("display_errors", 1);
    }

    define('SESSION_EXPIRED', 60 * 60 * 24 * 7);

//
    require_once CONFIG_PATH . '/common/defined.php';
//
    require_once CONFIG_PATH . '/common/constant.php';

    /**
     * This makes our life easier when dealing with paths. Everything is relative
     * to the application root now.
     */
    chdir(dirname(__DIR__));

// Decline static file requests back to the PHP built-in webserver
    if (php_sapi_name() === 'cli-server') {
        $path = realpath(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
        if (__FILE__ !== $path && is_file($path)) {
            return false;
        }
        unset($path);
    }

// Setup autoloading
    require 'init_autoloader.php';

// Run the application!
    Application::init(require __DIR__ . '/../config/application.config.php')->run();
}catch (\Exception $ex){
    echo '<pre>';
    print_r($ex->getMessage());
    echo '</pre>';
    die();
}
