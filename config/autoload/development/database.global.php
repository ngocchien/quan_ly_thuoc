<?php
/**
 * Created by PhpStorm.
 * User: chiennn
 * Date: 18/06/2017
 * Time: 16:01
 */

return array(
    'db' => array(
        //other adapter when it needed...
        'adapters' => array(
            'mysql' => array(
                'host' => '127.0.0.1',
                'driver' => 'Mysqli',
                'database' => 'quan_ly_thuoc',
                'username' => 'root',
                'password' => '123123',
                'charset' => 'utf8',
                'options' => array(
                    'buffer_result' => true
                )
            )
        )
    )
);