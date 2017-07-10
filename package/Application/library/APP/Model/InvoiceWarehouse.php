<?php
/**
 * Created by PhpStorm.
 * User: chiennn
 * Date: 17/06/2017
 * Time: 15:50
 */

namespace APP\Model;

use APP\DAO;

class InvoiceWarehouse
{
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 2;
    const STATUS_REMOVE = 0;

    const IS_NOTIFY = 1;
    const UN_NOTIFY = 0;

    const STATUS_ACTIVE_NAME = 'Active';
    const STATUS_INACTIVE_NAME = 'Hidden';
    const STATUS_REMOVE_NAME = 'Remove';

    public static function create($params)
    {
        return DAO\InvoiceWarehouse::create($params);
    }

    public static function get($params)
    {
        return DAO\InvoiceWarehouse::get($params);
    }

    public static function update($params, $id)
    {
        return DAO\InvoiceWarehouse::update($params,$id);
    }

    public static function renderStatus(){
        $arr_status = [
            self::STATUS_ACTIVE => self::STATUS_ACTIVE_NAME,
            self::STATUS_INACTIVE => self::STATUS_INACTIVE_NAME
        ];

        if(!empty($id) && !empty($arr_status[$id])){
            return $arr_status[$id];
        }

        return $arr_status;
    }

}