<?php
/**
 * Created by PhpStorm.
 * User: tuandv
 * Date: 10/2/16
 * Time: 3:39 PM
 */

namespace Administrator\Controller;

use APP\Business;
use APP\Controller\MyController;
use APP\Model;

class PermissionController extends MyController
{
    public function indexAction()
    {
        try {
            $params = array_merge($this->params()->fromRoute(),$this->params()->fromQuery());
            $arr_resource_list = Business\Permission::getAllResource();

            return [
                'params' => $params,
                'arrResourceList' => $arr_resource_list
            ];

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), $e->getCode());
        }
    }

    public function grantAction(){
        try {
            if(!defined('IS_ADMIN') || IS_ADMIN != Model\Group::GROUP_ADMINISTRATOR){
                return $this->redirect()->toRoute('administrator');
            }

            $params = $this->params()->fromRoute();
            $group_id = empty($params['gid']) ? 0 : (int) $params['gid'];
            $user_id = empty($params['uid']) ? 0 :  (int) $params['uid'];

            if (empty($group_id) && empty($user_id)) {
                return $this->redirect()->toRoute('administrator');
            }

            $arr_condition = [
                'not_status' => Model\Permission::PERMISSION_STATUS_REMOVE,
                'limit' => 1000
            ];

            //get info
            $part = $id = '';
            if($group_id){
                $groups = Business\Group::get([
                    'group_id' => $group_id,
                    'not_status' => Model\Group::GROUP_STATUS_REMOVE
                ]);

                if(empty($groups['rows'])){
                    return $this->redirect()->toRoute('administrator');
                }

                $part = 'gid';
                $id = $group_id;
                $arr_condition['group_id'] = $group_id;
                $params['group'] = $groups['rows'][0];
            }

            if($user_id){
                $users = Business\User::getUSer([
                    'user_id' => $user_id,
                    'not_status' => Model\User::USER_STATUS_REMOVE
                ]);
                if(empty($users['rows'])){
                    return $this->redirect()->toRoute('administrator');
                }

                $part = 'gid';
                $id = $user_id;
                $arr_condition['user_id'] = $user_id;
                $params['user'] = $users['rows'][0];
            }
            $arr_permission_list = Business\Permission::get($arr_condition);
            $arr_allowed_resource = array();
            if (!empty($arr_permission_list['rows'])) {
                foreach ($arr_permission_list['rows'] as $permission) {
                    $arr_allowed_resource[] = strtolower($permission['module']) . ':' . strtolower($permission['controller']) . ':' . strtolower($permission['action']);
                }
            }

            $arr_resource_list = Business\Permission::getAllResource();

            return [
                'part' => $part,
                'id' => $id,
                'params' => $params,
                'arrResourceList' => $arr_resource_list,
                'arrPermissionList' => $arr_permission_list,
                'arrAllowedResource' => $arr_allowed_resource
            ];
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), $e->getCode());
        }
    }

    public function addAction(){
        if($this->request->isPost()){
            $params = $this->params()->fromPost();
            $result = Business\Permission::create($params);
            return $this->getResponse()->setContent(json_encode($result));
        }
    }

    public function deleteAction(){
        if($this->request->isPost()){
            $params = $this->params()->fromPost();
            $result = Business\Permission::delete($params);
            return $this->getResponse()->setContent(json_encode($result));
        }
    }
}