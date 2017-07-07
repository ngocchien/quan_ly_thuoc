<?php
/**
 * Created by PhpStorm.
 * User: chiennn
 * Date: 27/06/2017
 * Time: 21:26
 */

namespace APP\Model;

use APP\DAO;

class Brand
{
    const BRAND_STATUS_ACTIVE = 1;
    const BRAND_STATUS_INACTIVE = 2;
    const BRAND_STATUS_REMOVE = 0;

    const BRAND_STATUS_ACTIVE_NAME = 'Hiển thị';
    const BRAND_STATUS_INACTIVE_NAME = 'Ẩn';

    public static function create($params)
    {
        return DAO\Brand::create($params);
    }

    public static function get($params)
    {
        return DAO\Brand::get($params);
    }

    public static function update($params, $id)
    {
        return DAO\Brand::update($params,$id);
    }

    public static function renderStatus(){
        $arr_status = [
            self::BRAND_STATUS_ACTIVE => self::BRAND_STATUS_ACTIVE_NAME,
            self::BRAND_STATUS_INACTIVE => self::BRAND_STATUS_INACTIVE_NAME
        ];

        if(!empty($id) && !empty($arr_status[$id])){
            return $arr_status[$id];
        }

        return $arr_status;
    }
}