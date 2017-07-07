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

class User
{
    const USER_STATUS_ACTIVE = 1;
    const USER_STATUS_INACTIVE = 2;
    const USER_STATUS_REMOVE = 0;

    const USER_ID_SUPPER_ADMIN = 1;

    public static function getUser($params){
        return DAO\User::get($params);
    }

    public static function createUser($params){
        return DAO\User::createUser($params);
    }

    public static function updateUser($params,$id){
        return DAO\User::updateUser($params,$id);
    }
}