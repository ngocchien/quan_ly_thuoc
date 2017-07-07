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

class Category
{
    const CATEGORY_STATUS_ACTIVE = 1;
    const CATEGORY_STATUS_INACTIVE = 2;
    const CATEGORY_STATUS_REMOVE = 0;

    const CATEGORY_STATUS_ACTIVE_NAME = 'Hiển thị';
    const CATEGORY_STATUS_INACTIVE_NAME = 'Ẩn';

    public static function get($params)
    {
        return DAO\Category::get($params);
    }

    public static function create($params)
    {
        return DAO\Category::create($params);
    }

    public static function update($params, $id)
    {
        return DAO\Category::update($params, $id);
    }

    public static function updateTreeCategory($params)
    {
        return DAO\Category::updateTreeCategory($params);
    }

    public static function renderStatus($status_id = '')
    {
        $arr_status = [
            self::CATEGORY_STATUS_ACTIVE => self::CATEGORY_STATUS_ACTIVE_NAME,
            self::CATEGORY_STATUS_INACTIVE_NAME => self::CATEGORY_STATUS_INACTIVE_NAME
        ];

        if ($status_id) {
            return $arr_status[$status_id];
        }

        return $arr_status;
    }
}