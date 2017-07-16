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
use APP\Helper;

class UserController extends MyController
{
    public function indexAction()
    {
        try {
            $params = array_merge($this->params()->fromRoute(),$this->params()->fromQuery());
            //get list user
            $params['not_status'] = Model\User::USER_STATUS_REMOVE;
            $params['not_user_id'] = Model\User::USER_ID_SUPPER_ADMIN;
            $users = Business\User::getUSer($params);

            //get list group
            $list_groups = Business\Group::get([
                'not_status' => Model\Group::GROUP_STATUS_REMOVE,
                'limit' => 1000
            ]);

            $groups = [];
            if(!empty($list_groups['rows'])){
                foreach ($list_groups['rows'] as $row){
                    $groups[$row['group_id']] = $row;
                }
            }

//            $intPage = empty($params['page']) ? 1 : (int) $params['page'];
//            $intLimit = empty($params['limit']) ? 10 : (int) $params['limit'];

//            $route = $this->getEvent()->getRouteMatch()->getMatchedRouteName();

            $params['total'] = $users['total'];

            return [
                'params' => $params,
                'users' => $users,
                'groups' => $groups,
//                'route' => $route
            ];

        } catch (\Exception $e) {
            echo '<pre>';
            print_r($e->getMessage());
            echo '</pre>';
            die();
            throw new \Exception($e->getMessage(), $e->getCode());
        }
    }

    public function createAction(){
        try {
            $params = $this->params()->fromRoute();
            if($this->request->isPost()){
                $params = $this->params()->fromPost();
                $result = Business\User::createUser($params);
                if(!empty($result['success'])){
                    return $this->redirect()->toRoute('administratorUser',['action' => 'edit', 'id' => $result['user_id']]);
                }
            }

            //get list group
            $groups = Business\Group::get([
                'not_status' => Model\Group::GROUP_STATUS_REMOVE,
                'not_group_id' => Model\Group::GROUP_ADMINISTRATOR,
                'limit' => 1000,
            ]);

            return [
                'params' => $params,
                'groups' => $groups
            ];
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), $e->getCode());
        }
    }

    public function editAction(){
        $params = $this->params()->fromRoute();
        $user_id = $params['id'];
        if(empty($user_id)){
            return $this->redirect()->toRoute('administrator');
        }

        //check user
        $users = Business\User::getUser([
            'user_id' => $user_id,
            'not_status' => Model\User::USER_STATUS_REMOVE
        ]);

        if(empty($users['rows'])){
            return $this->redirect()->toRoute('administrator');
        }
        $user = $users['rows'][0];

        if($this->request->isPost()){
            $params = $this->params()->fromPost();
            $result = Business\User::updateUser($params,$user_id);
            if(!empty($result['success'])){
                $params['messages_success'] = 'Cập nhật thành công!';
            }
        }

        //get list group
        $groups = Business\Group::get([
            'not_status' => Model\Group::GROUP_STATUS_REMOVE,
            'not_group_id' => Model\Group::GROUP_ADMINISTRATOR,
            'limit' => 1000
        ]);

        return [
            'params' => $params,
            'user' => $user,
            'groups' => $groups
        ];
    }

    public function deleteAction(){
        if($this->request->isPost()){
            $params = $this->params()->fromPost();
            $result = Business\User::deleteUser($params);
            return $this->getResponse()->setContent(json_encode($result));
        }
    }

    public function profileAction(){
        $user_id = USER_ID;

        //get user
        $users = Business\User::getUser([
            'user_id' => $user_id,
            'limit' => 1,
            'offset' => 0
        ]);

        if(empty($users['rows'])){
            return $this->redirect()->toRoute('administrator');
        }

        $user = $users['rows'][0];

        //get info group
        $groups = Model\Group::get([
            'group_id' => $user['group'],
            'limit'=>1,
            'offset' => 0
        ]);

        return [
            'user' => $user,
            'group' => $groups['rows'][0]
        ];
    }
}