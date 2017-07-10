<?php
/**
 * Created by PhpStorm.
 * User: GiangBeo
 * Date: 11/24/16
 * Time: 9:33 AM
 */
namespace TASK;

use APP\Model,
    APP\Utils,
    Zend\View\Model\ViewModel,
    Zend\View\Renderer\PhpRenderer;;

class Notify
{
    public function sendNotifyProductExpire($params)
    {
        date_default_timezone_set('Asia/Saigon');
        $fileNameSuccess = __CLASS__ . '_' . __FUNCTION__ . '_Success';
        $fileNameError = __CLASS__ . '_' . __FUNCTION__ . '_Error';
        $arrParam = [];
        try {
            $running = true;
            $limit = 100;
            $offset = 0;
            $path_html = ROOT_PATH.'/layout/email/notify-expire.phtml';
            while ($running){
                $result = Model\Warehouse::get([
                    'limit' => $limit,
                    'offset' => $offset,
                    'is_notify' => 1,
                    'expire' => 1,
                    'order' => 'hsd ASC'
                ]);

                if(empty($result['rows'])){
                    break;
                }

                if(count($result['rows']) < $limit){
                    $running = false;
                }

                $warehouse = $result['rows'];

                $arr_product_id = $properties_id = $user_id = [];
                foreach ($result['rows'] as $row){
                    $arr_product_id[] = $row['product_id'];
                    $properties_id[] = $row['properties_id'];
                    $user_id[] = $row['user_created'];
                }

                //get product
                $result = Model\Product::get([
                    'in_product_id' => $arr_product_id,
                    'not_status' => Model\Product::PRODUCT_STATUS_REMOVE,
                    'limit' => $limit,
                    'offset' => 0
                ]);

                if(empty($result['rows'])){
                    continue;
                }

                $products = [];
                foreach ($result['rows'] as $row){
                    $products[$row['product_id']] = $row;
                }

                $properties = [];
                if(!empty($properties_id)){
                    $result = Model\Properties::get([
                        'in_id' => $properties_id,
                        'offset' => 0,
                        'limit' => $limit
                    ]);

                    if(!empty($result)){
                        foreach ($result['rows'] as $row){
                            $properties[$row['id']] = $row;
                        }
                    }
                }

                $users = [];
                if(!empty($user_id)){
                    $result = Model\User::getUser([
                        'in_user_id' => $user_id,
                        'offset' => 0,
                        'limit' => $limit
                    ]);

                    if(!empty($result)){
                        foreach ($result['rows'] as $row){
                            $users[$row['user_id']] = $row;
                        }
                    }
                }

                Utils::runJob(
                    'info',
                    'TASK\SendMail',
                    'send',
                    'doHighBackgroundTask',
                    'admin_mail',
                    array(
                        'arr_email' => ['ngocchien01@gmail.com'],
                        'params_content' => [
                            'warehouse' => $warehouse,
                            'products' => $products,
                            'properties' => $properties,
                            'users'=> $users
                        ],
                        'template' => $path_html,
                        'title' => 'Thông báo thuốc sắp hết hạn'
                    )
                );

                die('done');

            }

            Utils::writeLog($fileNameSuccess, $arrParam);
        } catch (\Exception $e) {
            if(APPLICATION_ENV != 'production'){
                echo '<pre>';
                print_r([
                    $e->getCode(),
                    $e->getMessage()
                ]);
                echo '</pre>';
                die();
            }

            $arrParam['exc'] = [
                'code' => $e->getCode(),
                'messages' => $e->getMessage()
            ];
            Utils::writeLog($fileNameError, $arrParam);
        }
    }
}