<?php
/**
 * Created by PhpStorm.
 * User: chiennn
 * Date: 03/06/2017
 * Time: 08:50
 */
namespace APP\Business;

use APP\Model;
use Zend\Authentication\AuthenticationService;
use Zend\Validator\EmailAddress;
use Zend\Mvc\Controller\AbstractActionController;

class Group
{
    public static function create($params){
        if(empty($params['group_name'])){
            $params['error'] = 'Tên nhóm không được bỏ trống!';
            return $params;
        }

        $group_name = trim(strip_tags($params['group_name']));
        $status = $params['status'];

        if(strlen($group_name) < 4){
            $params['error'] = 'Tên nhóm phải từ 4 ký tự trở lên!';
            return $params;
        }

        //check group
        $exist = Model\Group::get([
            'group_name' => $group_name,
            'limit' => 1,
            'offset' => 0,
            'not_status' => Model\Group::GROUP_STATUS_REMOVE
        ]);

        if(!empty($exist['rows'])){
            $params['error'] = 'Tên nhóm này đã tồn tại trong hệ thống!';
            return $params;
        }

        //tạo group
        $group_id = Model\Group::create([
            'group_name' => $group_name,
            'status' => $status,
            'created_date' => time(),
            'user_created' => USER_ID
        ]);

        if(!$group_id){
            $params['error'] = 'Xảy ra lỗi trong quá trình xử lý! Thử lại sau giây lát';
            return $params;
        }

        return [
            'success' => true,
            'group_id' => $group_id
        ];
    }

    public static function get($params){
        $limit = empty($params['limit']) ? 10 : (int)$params['limit'];
        $page = empty($params['page']) ? 1 : (int)$params['page'];
        $offset = $limit * ($page - 1);
        $params['offset'] = $offset;
        $params['limit'] = $limit;
        $result = Model\Group::get($params);
        return $result;
    }

    public static function update($params, $id){
        if(empty($params['group_name'])){
            $params['error'] = 'Tên nhóm không được bỏ trống!';
            return $params;
        }

        $group_name = trim(strip_tags($params['group_name']));
        $status = $params['status'];

        if(strlen($group_name) < 4){
            $params['error'] = 'Tên nhóm phải từ 4 ký tự trở lên!';
            return $params;
        }

        //check group
        $exist = Model\Group::get([
            'group_name' => $group_name,
            'limit' => 1,
            'offset' => 0,
            'not_status' => Model\Group::GROUP_STATUS_REMOVE,
            'not_group_id' => $id
        ]);

        if(!empty($exist['rows'])){
            $params['error'] = 'Tên nhóm này đã tồn tại trong hệ thống!';
            return $params;
        }

        //update group
        $updated = Model\Group::update([
            'group_name' => $group_name,
            'status' => $status,
            'updated_date' => time(),
            'user_updated' => USER_ID
        ],$id);

        if(!$updated){
            $params['error'] = 'Xảy ra lỗi trong quá trình xử lý! Thử lại sau giây lát';
            return $params;
        }

        return [
            'success' => true
        ];
    }

    public static function delete($params){
        if(empty($params['group_id'])){
            return [
                'st' => -1,
                'ms' => 'Xảy ra lỗi ! Vui lòng thử lại!',
                'error' => 'error'
            ];
        }

        $group_id = (int) $params['group_id'];

        if($group_id == Model\Group::GROUP_ADMINISTRATOR){
            return [
                'error' => 'error',
                'st' => -1,
                'ms' => 'Permission Deni!!!'
            ];
        }

        //get info user
        $groups = Model\Group::get([
            'group_id' => $group_id,
            'not_status' => Model\Group::GROUP_STATUS_REMOVE
        ]);

        if(empty($groups['rows'])){
            return [
                'st' => -1,
                'ms' => 'Xảy ra lỗi ! Vui lòng thử lại!',
                'error' => 'error'
            ];
        }

        //delete
        $status = Model\Group::update([
            'status' => Model\Group::GROUP_STATUS_REMOVE,
            'updated_date' => time(),
            'user_updated' => USER_ID
        ],$group_id);

        if(!$status){
            return [
                'st' => -1,
                'ms' => 'Xảy ra lỗi trong quá trình xử lý! Vui lòng thử lại!',
                'error' => 'error'
            ];
        }

        return [
            'st' => 1,
            'ms' => 'Xóa nhóm thành công!'
        ];
    }
}