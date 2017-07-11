<?php
/**
 * Created by PhpStorm.
 * User: chiennn
 * Date: 27/06/2017
 * Time: 22:33
 */

namespace Administrator\Controller;


use APP\Controller\MyController;
use APP\Business;
use APP\Model;

class PropertiesController extends MyController
{
    public function indexAction() {
        $params = array_merge($this->params()->fromRoute(), $this->params()->fromQuery());
        $params['not_status'] = Model\Properties::PROPERTIES_STATUS_REMOVE;

        //get list
        $user_id = $users = [];
        $properties = Business\Properties::getList($params);

        if (!empty($properties['rows'])) {
            foreach ($properties['rows'] as $row) {
                $user_id[] = $row['user_created'];
            }
        }

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

        return [
            'params' => $params,
            'properties' => $properties,
            'users' => $users
        ];
    }

    public function createAction(){
        $params = $this->params()->fromRoute();

        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();
            $result = Business\Properties::create($params);
            if (!empty($result['success'])) {
                return $this->redirect()->toRoute('administratorProperties', ['action' => 'edit', 'id' => $result['id']]);
            }
        }

        return [
            'params' => $params
        ];
    }

    public function editAction(){
        $params = $this->params()->fromRoute();
        $id = $params['id'];

        if(empty($id)){
            return $this->redirect()->toRoute('administrator');
        }

        //check exist
        $result = Business\Properties::getList([
            'id' => $id,
            'not_status' => Model\Properties::PROPERTIES_STATUS_REMOVE,
            'limit' => 1,
            'page' => 1
        ]);

        if(empty($result['rows'])){
            return $this->redirect()->toRoute('administrator');
        }

        $properties = $result['rows'][0];

        if($this->request->isPost()){
            $params = $this->params()->fromPost();
            $params['id'] = $id;
            $params = Business\Properties::update($params);
            if(!empty($params['success'])){
                return $this->redirect()->toRoute('administratorProperties', ['action' => 'edit', 'id' => $id]);
            }
        }
        return [
            'params' => $params,
            'properties' => $properties
        ];
    }

    public function deleteAction(){
        if($this->request->isPost()){
            $params = $this->params()->fromPost();
            $result = Business\Properties::delete($params);
            return $this->getResponse()->setContent(json_encode($result));
        }
    }
}