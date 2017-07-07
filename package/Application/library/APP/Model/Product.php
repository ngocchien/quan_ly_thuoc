<?php
/**
 * Created by PhpStorm.
 * User: tuandv
 * Date: 8/7/16
 * Time: 15:41
 */

namespace APP\Model;

use APP;
use APP\DAO;

class Product
{
    const PRODUCT_STATUS_ACTIVE = 1;
    const PRODUCT_STATUS_INACTIVE = 2;
    const PRODUCT_STATUS_REMOVE = 0;

    const PRODUCT_STATUS_ACTIVE_NAME = 'Hiển thị';
    const PRODUCT_STATUS_INACTIVE_NAME = 'Ẩn';
    const PRODUCT_STATUS_REMOVENAME = 'Đã xóa';

    public static function get($params)
    {
        return DAO\Product::get($params);
    }

    public static function create($params)
    {
        return DAO\Product::create($params);
    }

    public static function update($params, $id)
    {
        return DAO\Product::update($params, $id);
    }

    public static function renderStatus($status_id = '')
    {
        $arr_status = [
            self::PRODUCT_STATUS_ACTIVE => self::PRODUCT_STATUS_ACTIVE_NAME,
            self::PRODUCT_STATUS_INACTIVE => self::PRODUCT_STATUS_INACTIVE_NAME
        ];

        if ($status_id) {
            return $arr_status[$status_id];
        }

        return $arr_status;
    }

    public static function updateByCondition($params, $condition)
    {
        return DAO\Product::updateByCondition($params, $condition);
    }
}