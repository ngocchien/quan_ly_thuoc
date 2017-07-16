<?php
/**
 * Created by PhpStorm.
 * User: tuandv
 * Date: 10/2/16
 * Time: 3:39 PM
 */

namespace Administrator\Controller;

use Zend\View\Model\ViewModel;
use APP\Business;
use APP\Controller\MyController;
use APP\Model;

class GroupController extends MyController
{
    public function indexAction()
    {
        try {
            $params = array_merge($this->params()->fromRoute(),$this->params()->fromQuery());
            //get list user
            $params['not_status'] = Model\Group::GROUP_STATUS_REMOVE;
            $params['not_group_id'] = Model\Group::GROUP_ADMINISTRATOR;
            $groups = Business\Group::get($params);

            $users = [];
            if(!empty($groups['rows'])){
                $user_id = [];
                foreach ($groups['rows'] as $row){
                    $user_id[] = $row['user_created'];
                }

                $result = Business\User::getUSer([
                    'in_user_id' =>array_values(array_unique($user_id))
                ]);

                if(!empty($result['rows'])){
                    foreach ($result['rows'] as $row){
                        $users[$row['user_id']] = $row;
                    }
                }
            }

            $params['total'] = $groups['total'];
            return [
                'params' => $params,
                'groups' => $groups,
                'users' => $users
            ];
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), $e->getCode());
        }
    }

    public function createAction(){
        try {
            $params = $this->params()->fromRoute();
            if($this->request->isPost()){
                $params = $this->params()->fromPost();
                $result = Business\Group::create($params);
                if(!empty($result['success'])){
                    return $this->redirect()->toRoute('administratorGroup',['action' => 'edit', 'id' => $result['group_id']]);
                }
            }
            //Render layout
            $layout = $this->layout();
            $layout->setTemplate('administrator/layout');

            $header = new ViewModel();
            $header->setTemplate('administrator/header');
            $footer = new ViewModel();
            $footer->setTemplate('administrator/footer');
            $left_menu = new ViewModel();
            $left_menu->setTemplate('administrator/left_menu');

            $layout->addChild($header, 'header')
                ->addChild($footer, 'footer')
                ->addChild($left_menu, 'left_menu');
            return new ViewModel(array(
                'params' => $params
            ));
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), $e->getCode());
        }
    }

    public function editAction(){
        $params = $this->params()->fromRoute();
        $id = $params['id'];

        if(empty($id) || $id == Model\Group::GROUP_ADMINISTRATOR){
            return $this->redirect()->toRoute('administrator');
        }

        //check user
        $groups = Business\Group::get([
            'group_id' => $id,
            'not_status' => Model\Group::GROUP_STATUS_REMOVE
        ]);

        if(empty($groups['rows'])){
            return $this->redirect()->toRoute('administrator');
        }
        $group = $groups['rows'][0];

        if($this->request->isPost()){
            $params = $this->params()->fromPost();
            $result = Business\Group::update($params,$id);
            if(!empty($result['success'])){
                $params['messages_success'] = 'Cập nhật thành công!';
            }
        }

        return array(
            'params' => $params,
            'group' => $group
        );
    }

    public function deleteAction(){
        if($this->request->isPost()){
            $params = $this->params()->fromPost();
            $result = Business\Group::delete($params);
            return $this->getResponse()->setContent(json_encode($result));
        }
    }
}