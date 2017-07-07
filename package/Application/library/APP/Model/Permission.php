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

class Permission
{
    const PERMISSION_STATUS_ACTIVE = 1;
    const PERMISSION_STATUS_INACTIVE = 2;
    const PERMISSION_STATUS_REMOVE = 0;

    public static function get($params){
        return DAO\Permission::get($params);
    }

    public static function create($params){
        return DAO\Permission::create($params);
    }

    public static function update($params,$id){
        return DAO\Permission::update($params,$id);
    }
}