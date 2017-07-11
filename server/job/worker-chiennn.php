<?php
/**
 * Created by PhpStorm.
 * User: chiennn
 * Date: 28/06/2017
 * Time: 22:27
 */


// Define root path
define('ROOT_PATH', realpath(dirname(__FILE__) . '/../..'));

// Define path to config directory
defined('CONFIG_PATH')
|| define('CONFIG_PATH', ROOT_PATH . '/config');

defined('LANGUAGE_DEFAULT')
|| define('LANGUAGE_DEFAULT', 'vi_VN');

//
require_once CONFIG_PATH . '/common/defined.php';
//
require_once CONFIG_PATH . '/common/constant.php';

/**
 * This makes our life easier when dealing with paths. Everything is relative
 * to the application root now.
 */
chdir(dirname(__DIR__) . '/..');

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
            'APP' => ROOT_PATH . '/package/Application/library/APP',
            'My' => ROOT_PATH . '/library/My',
            'TASK' => __DIR__ . '/tasks',
        ),
    )
));

//Check console params options
$opts = new Zend\Console\Getopt(array(
    'env-s' => 'environment',
    'v-i' => 'verbose option'
));

//Get info console
$env = $opts->getOption('env');
$verbose = $opts->getOption('v');

if (empty($env) || !in_array($env, array('development', 'production'))) {
    echo 'Error Environment server-name.php --env [development,production]';
    exit();
}

// Define application environment
defined('APPLICATION_ENV')
|| define('APPLICATION_ENV', $env);

//
require_once CONFIG_PATH . '/autoload/' . APPLICATION_ENV . '/global.php';

//Print information environment
if ($verbose) {
    echo "ROOT_PATH : " . ROOT_PATH . "\n";
    echo "ENVIRONMENT : " . APPLICATION_ENV . "\n";
}
use Box\Spout\Reader\ReaderFactory;
use Box\Spout\Common\Type;

$path_file_excel = UPLOAD_PATH.'/Excel/nuoc_tren_the_gioi.xlsx';

try {
    $reader = ReaderFactory::create(Type::XLSX);
    $reader->open($path_file_excel);
    foreach ($reader->getSheetIterator() as $sheet) {
        foreach ($sheet->getRowIterator() as $key => $row) {
            if($key < 3){
                continue;
            }
            if(empty($row[1])){
                continue;
            }

            $id = APP\Model\Country::create([
                'country_name' => $row[1],
                'created_date' => time(),
                'user_created' => 1,
                'status' => APP\Model\Country::STATUS_ACTIVE,
                'slug' => APP\Utils::getSlug($row[1])
            ]);

            if($id){
                echo APP\Utils::getColoredString('Insert success country name: ' . $row[1] . ' id = '.$id, 'green');
            }else{
                echo APP\Utils::getColoredString('Insert country name: ' . $row[1] . ' error', 'red');
            }

        }
    }
    echo '<pre>';
    print_r('DONE');
    echo '</pre>';
    die();

    echo '<pre>';
    print_r($reader->getSheetIterator()->current());
    echo '</pre>';
    die();

    while ($reader->hasNextSheet()) {
        $reader->nextSheet();

        while ($reader->hasNextRow()) {
            $row = $reader->nextRow();
            echo '<pre>';
            print_r($row);
            echo '</pre>';
            die();
            // do stuff
        }
    }
    echo '<pre>';
    print_r(111);
    echo '</pre>';
    die();


    $inputFileType = PHPExcel_IOFactory::identify($path_file_excel);
    $objReader = PHPExcel_IOFactory::createReader($path_file_excel);
    $objPHPExcel = $objReader->load($path_file_excel);
    $sheet = $objPHPExcel->getSheet(0);
    $highestRow = $sheet->getHighestRow();
    $highestColumn = $sheet->getHighestColumn();
    echo '<pre>';
    print_r(1);
    echo '</pre>';
    die();
//  Loop through each row of the worksheet in turn
    for ($row = 1; $row <= $highestRow; $row++){
        //  Read a row of data into an array
        $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row,
            NULL,
            TRUE,
            FALSE);
        echo '<pre>';
        print_r($rowData);
        echo '</pre>';
        die();
        //  Insert row data array into your database of choice here
    }

} catch(Exception $e) {
    die('Error loading file "'.pathinfo($path_file_excel,PATHINFO_BASENAME).'": '.$e->getMessage());
}

echo '<pre>';
print_r($path_file_excel);
echo '</pre>';
die();

APP\Utils::runJob(
    'info',
    'TASK\Notify',
    'sendNotifyProductExpire',
    'doHighBackgroundTask',
    'admin_process',
    array(
        'actor' => 'chiennn'
    )
);

die('1-done');

$arrConfig = APP\Config::get('mail');
echo '<pre>';
print_r($arrConfig['mail']['adapters']['smtp']);
echo '</pre>';
die();

//$redis = MT\Nosql\Redis::getInstance('caching');
//echo '<pre>';
//print_r($redis->HGETALL(My\General::KEY_ACCESS_TOKEN));
//echo '</pre>';
//die();

//MT\Utils::runJob(
//    'info',
//    'TASK\Test',
//    'uploadYt',
//    'doHighBackgroundTask',
//    'admin_process',
//    array(
//        'title' => '😄CƯỜI THỐN TẬN RỐN 54, हँसी नहीं रुकेगी, हास्य 😄 FUNNY FAILS 😄 TRY NOT TO LAUGH CHALLENGE 54',
//        'path' => '/var/www/html/mt-solution/downloads/cuoi-thon-tan-ron-54-funny-fails-try-not-to-laugh-challenge-54_kX-7jhoekac.mp4',
//        'cate_id' => 1,
//        'action' => 'cloneYoutube'
//    )
//);
//
//die('DONE');

//[title] => 😄CƯỜI THỐN TẬN RỐN 54, हँसी नहीं रुकेगी, हास्य 😄 FUNNY FAILS 😄 TRY NOT TO LAUGH CHALLENGE 54
//    [cate_id] => 1
//    [path] => /var/www/html/mt-solution/downloads/cuoi-thon-tan-ron-54-funny-fails-try-not-to-laugh-challenge-54_kX-7jhoekac.mp4
//[action] => cloneYoutube


//MT\Utils::runJob(
//    'info',
//    'TASK\Test',
//    'uploadYt',
//    'doHighBackgroundTask',
//    'admin_process',
//    [
//        'title' => ' 😱💕 TRIỆU LIKE CHO ANH CHÀNG ĐÁNH TRỐNG BẰNG LON SỮA SIÊU ĐẲNG, GREATEST HE DRUMMER WITH MILK CANS',
//        'cate_id' => 1,
//        'path' => '/var/www/html/mt-solution/downloads/trieu-like-cho-anh-chang-danh-trong-bang-lon-sua-sieu-dang-greatest-he-drummer-with-milk-cans_FL7pSc1wSTo.mp4',
//        'action' => __FUNCTION__,
//        'source_id' => 'FL7pSc1wSTo'
//    ]
//);
//
//die('DONE');

//MT\Utils::runJob(
//    'info',
//    'TASK\Test',
//    'cloneYoutube',
//    'doHighBackgroundTask',
//    'admin_process',
//    array(
//        'actor' => __FUNCTION__
//    )
//);
//
//die('DONE');

$redis = MT\Nosql\Redis::getInstance('caching');
$redis->SET(MT\Model\Common::KEY_TOTAL_DAILY_UPLOAD, 0);
$redis->SET(MT\Model\Common::KEY_TOTAL_DAILY_DOWNLOAD, 0);

MT\Utils::runJob(
    'info',
    'TASK\Test',
    'download',
    'doHighBackgroundTask',
    'admin_process',
    array(
        'actor' => __FUNCTION__,
        'cate_id' => 1
    )
);

die('DONE');