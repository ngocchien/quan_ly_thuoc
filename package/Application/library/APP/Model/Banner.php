<?php
/**
 * Created by PhpStorm.
 * User: chiennn
 * Date: 17/06/2017
 * Time: 15:50
 */

namespace APP\Model;

use APP\DAO;

class Banner
{
    const BANNER_STATUS_ACTIVE = 1;
    const BANNER_STATUS_INACTIVE = 2;
    const BANNER_STATUS_REMOVE = 0;

    const BANNER_STATUS_ACTIVE_NAME = 'Hiển thị';
    const BANNER_STATUS_INACTIVE_NAME = 'Ẩn';

    public static function create($params)
    {
        return DAO\Banner::create($params);
    }

    public static function get($params)
    {
        return DAO\Banner::get($params);
    }

    public static function update($params, $id)
    {
        return DAO\Banner::update($params,$id);
    }

    public static function renderStatus(){
        $arr_status = [
            self::BANNER_STATUS_ACTIVE => self::BANNER_STATUS_ACTIVE_NAME,
            self::BANNER_STATUS_INACTIVE => self::BANNER_STATUS_INACTIVE_NAME
        ];

        if(!empty($id) && !empty($arr_status[$id])){
            return $arr_status[$id];
        }

        return $arr_status;
    }

}