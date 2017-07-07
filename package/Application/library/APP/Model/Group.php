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

class Group
{
    const GROUP_STATUS_ACTIVE = 1;
    const GROUP_STATUS_INACTIVE = 2;
    const GROUP_STATUS_REMOVE = 0;

    const GROUP_ADMINISTRATOR = 1;

    public static function get($params){
        return DAO\Group::get($params);
    }

    public static function create($params){
        return DAO\Group::create($params);
    }

    public static function update($params,$id){
        return DAO\Group::update($params,$id);
    }
}