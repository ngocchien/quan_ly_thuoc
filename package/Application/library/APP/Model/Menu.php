<?php
/**
 * Created by PhpStorm.
 * User: chiennn
 * Date: 17/06/2017
 * Time: 10:17
 */

namespace APP\Model;
use APP\DAO;

class Menu
{
    const MENU_STATUS_ACTIVE = 1;
    const MENU_STATUS_INACTIVE = 2;
    const MENU_STATUS_REMOVE = 0;

    const MENU_STATUS_ACTIVE_NAME = 'Hiển thị';
    const MENU_STATUS_INACTIVE_NAME = 'Ẩn';


    public static function create($params)
    {
        return DAO\Menu::create($params);
    }

    public static function get($params)
    {
        return DAO\Menu::get($params);
    }

    public static function update($params, $id)
    {
        return DAO\Menu::update($params,$id);
    }

    public static function renderStatus($id = ''){
        $arr_status = [
            self::MENU_STATUS_ACTIVE => self::MENU_STATUS_ACTIVE_NAME,
            self::MENU_STATUS_INACTIVE => self::MENU_STATUS_INACTIVE_NAME
        ];

        if(!empty($id) && !empty($arr_status[$id])){
            return $arr_status[$id];
        }

        return $arr_status;
    }
}