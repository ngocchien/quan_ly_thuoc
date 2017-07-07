<?php
/**
 * Created by PhpStorm.
 * User: chiennn
 * Date: 18/06/2017
 * Time: 15:35
 */

namespace APP\Model;

use APP\DAO;

class Post
{
    const POST_STATUS_ACTIVE = 1;
    const POST_STATUS_INACTIVE = 2;
    const POST_STATUS_REMOVE = 0;

    const POST_STATUS_ACTIVE_NAME = 'Hiển thị';
    const POST_STATUS_INACTIVE_NAME = 'Ẩn';
    const POST_STATUS_REMOVENAME = 'Đã xóa';

    public static function get($params)
    {
        return DAO\Post::get($params);
    }

    public static function create($params)
    {
        return DAO\Post::create($params);
    }

    public static function update($params, $id)
    {
        return DAO\Post::update($params, $id);
    }

    public static function renderStatus($status_id = '')
    {
        $arr_status = [
            self::POST_STATUS_ACTIVE => self::POST_STATUS_ACTIVE_NAME,
            self::POST_STATUS_INACTIVE => self::POST_STATUS_INACTIVE_NAME
        ];

        if ($status_id) {
            return $arr_status[$status_id];
        }

        return $arr_status;
    }

    public static function updateByCondition($params, $condition)
    {
        return DAO\Post::updateByCondition($params, $condition);
    }
}