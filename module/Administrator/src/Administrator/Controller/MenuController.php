<?php
/**
 * Created by PhpStorm.
 * User: tuandv
 * Date: 5/31/17
 * Time: 11:51 PM
 */

namespace Administrator\Controller;

use APP\Controller\MyController;
use APP\Business;
use APP\Model;

class MenuController extends MyController
{
    public function indexAction()
    {
        $params = array_merge($this->params()->fromRoute(), $this->params()->fromQuery());
        $params['not_status'] = Model\Menu::MENU_STATUS_REMOVE;

        //get list
        $user_id = [];
        $result = Business\Menu::getList($params);
        $category_parent = $category_child = [];
        if (!empty($result['rows'])) {
            foreach ($result['rows'] as $row) {
                $user_id[] = $row['user_created'];
                if ($row['parent_id'] == 0) {
                    $category_parent[] = $row;
                } else {
                    $category_child[$row['parent_id']][] = $row;
                }
            }
        }

        $users = [];
        if(!empty($user_id)){
            $result = Model\User::getUser([
                'in_user_id' => $user_id,
                'limit' => 100,
                'offset' => 0
            ]);

            if(!empty($result['rows'])){
                foreach ($result['rows'] as $row){
                    $users[$row['user_id']] = $row;
                }
            }
        }

        //Status
        $arr_status = Model\Menu::renderStatus();
        //get list category
        return [
            'params' => $params,
            'category_parent' => $category_parent,
            'category_child' => $category_child,
            'arr_status' => $arr_status,
            'users' => $users
        ];
    }

    public function createAction()
    {
        $params = $this->params()->fromRoute();

        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();
            $result = Business\Menu::create($params);
            if (!empty($result['success'])) {
                return $this->redirect()->toRoute('administratorMenu', ['action' => 'edit', 'id' => $result['menu_id']]);
            }
        }

        //get list category
        $parent = Business\Menu::getListParent();
        $arr_status = Model\Menu::renderStatus();

        return [
            'params' => $params,
            'parent' => $parent,
            'arr_status' => $arr_status
        ];
    }

    public function editAction(){
        $params = $this->params()->fromRoute();
        $id = $params['id'];

        if(empty($id)){
            return $this->redirect()->toRoute('administrator');
        }

        //check menu
        $result = Business\Menu::getList([
            'menu_id' => $id,
            'not_status' => Model\Category::CATEGORY_STATUS_REMOVE,
            'limit' => 1,
            'page' => 1
        ]);

        if(empty($result['rows'])){
            return $this->redirect()->toRoute('administrator');
        }

        $menu = $result['rows'][0];

        if($this->request->isPost()){
            $params = $this->params()->fromPost();
            $params['menu_id'] = $id;
            $params = Business\Menu::update($params);
            if(!empty($params['success'])){
                return $this->redirect()->toRoute('administratorMenu', ['action' => 'edit', 'id' => $id]);
//                $params['messages_success'] = 'Cập nhật thành công!';
            }
        }

        //get list category
        $parent = [];
        if(!empty($menu['parent_id'])){
            $parent = Business\Menu::getListParent();
        }

        return [
            'params' => $params,
            'parent' => $parent,
            'menu' => $menu,
            'render_status' => Model\Category::renderStatus()
        ];
    }

    public function deleteAction(){
        if($this->request->isPost()){
            $params = $this->params()->fromPost();
            $result = Business\Menu::delete($params);
            return $this->getResponse()->setContent(json_encode($result));
        }
    }
}