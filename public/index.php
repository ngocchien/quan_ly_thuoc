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

//error_reporting(1);
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

    if (!defined('ZEND_FRAMEWORK_PATH')) {
        $fw_path = null;
        $dir = explode(PATH_SEPARATOR, get_include_path());

        foreach ($dir as $path) {
            if (file_exists($path . '/vendor/autoload.php')) {
                $fw_path = realpath($path);
                break;
            }
        }
        //
        define('ZEND_FRAMEWORK_PATH', $fw_path);
        unset($fw_path, $dir, $path);
    }

// Setup autoloading
    require 'init_autoloader.php';

//Load namespaces
    Zend\Loader\AutoloaderFactory::factory(array(
        'Zend\Loader\StandardAutoloader' => array(
            'namespaces' => array(
                'APP' => __DIR__ . '/../package/Application/library/APP',
                'My' => __DIR__ . '/../library/My',
            ),
        )
    ));

// Composer autoloading
    /*include __DIR__ . '/../vendor/autoload.php';

    if (!class_exists(Application::class)) {
        throw new RuntimeException(
            "Unable to load application.\n"
            . "- Type `composer install` if you are developing locally.\n"
            . "- Type `vagrant ssh -c 'composer install'` if you are using Vagrant.\n"
            . "- Type `docker-compose run zf composer install` if you are using Docker.\n"
        );
    }*/

// Run the application!
    Application::init(require __DIR__ . '/../config/application.config.php')->run();
}catch (\Exception $ex){
    echo '<pre>';
    print_r($ex->getMessage());
    echo '</pre>';
    die();
}
