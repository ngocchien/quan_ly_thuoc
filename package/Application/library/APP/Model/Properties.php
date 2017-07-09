<?php
/**
 * Created by PhpStorm.
 * User: chiennn
 * Date: 27/06/2017
 * Time: 22:39
 */

namespace APP\Model;

use APP\DAO;

class Properties
{
    const PROPERTIES_STATUS_ACTIVE = 1;
    const PROPERTIES_STATUS_INACTIVE = 2;
    const PROPERTIES_STATUS_REMOVE = 0;

    const PROPERTIES_STATUS_ACTIVE_NAME = 'Active';
    const PROPERTIES_STATUS_INACTIVE_NAME = 'Hidden';
    const PROPERTIES_STATUS_REMOVE_NAME = 'Remove';

    public static function create($params)
    {
        return DAO\Properties::create($params);
    }

    public static function get($params)
    {
        return DAO\Properties::get($params);
    }

    public static function update($params, $id)
    {
        return DAO\Properties::update($params,$id);
    }

    public static function renderStatus(){
        $arr_status = [
            self::PROPERTIES_STATUS_ACTIVE => self::PROPERTIES_STATUS_ACTIVE_NAME,
            self::PROPERTIES_STATUS_INACTIVE => self::PROPERTIES_STATUS_INACTIVE_NAME
        ];

        if(!empty($id) && !empty($arr_status[$id])){
            return $arr_status[$id];
        }

        return $arr_status;
    }
}