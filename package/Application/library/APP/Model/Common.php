<?php
/**
 * Created by PhpStorm.
 * User: chiennn
 * Date: 24/07/2017
 * Time: 11:23
 */

namespace APP\Model;


class Common
{
    public static function getListLimitQuery(){
        return [
            10 => 10,
            20 => 20,
            50 => 50,
            100 => 100
        ];
    }
}