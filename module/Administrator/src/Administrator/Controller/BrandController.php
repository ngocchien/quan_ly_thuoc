<?php
/**
 * Created by PhpStorm.
 * User: chiennn
 * Date: 27/06/2017
 * Time: 20:59
 */

namespace Administrator\Controller;

use APP\Controller\MyController;
use APP\Business;
use APP\Model;

class BrandController extends MyController
{
    public function indexAction() {
        $params = array_merge($this->params()->fromRoute(), $this->params()->fromQuery());
        $params['not_status'] = Model\Brand::BRAND_STATUS_REMOVE;

        //get list
        $user_id = $users = $country_id = $countries = [];
        $brands = Business\Brand::getList($params);
        if (!empty($brands['rows'])) {
            foreach ($brands['rows'] as $row) {
                $user_id[] = $row['user_created'];
                if(empty($row['country_id'])){
                    continue;
                }
                $country_id[] = $row['country_id'];
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

        if(!empty($country_id)){
            $result = Model\Country::get([
                'in_country_id' => array_values($country_id),
                'limit' => 1000,
                'offset' => 0
            ]);

            if(!empty($result)){
                foreach ($result['rows'] as $row){
                    $countries[$row['country_id']] = $row;
                }
            }
        }

        return [
            'params' => $params,
            'brands' => $brands,
            'arr_status' => Model\Banner::renderStatus(),
            'users' => $users,
            'countries' => $countries
        ];
    }

    public function createAction(){
        $params = $this->params()->fromRoute();

        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();
            $result = Business\Brand::create($params);
            if (!empty($result['success'])) {
                return $this->redirect()->toRoute('administratorBrand', ['action' => 'edit', 'id' => $result['id']]);
            }
        }

        $countries = Business\Country::getList([
            'not_status' => Model\Country::STATUS_REMOVE,
            'limit' => 1000,
            'offset' => 0,
            'order' => 'country_id desc'
        ]);

        return [
            'params' => $params,
            'countries' => $countries
        ];
    }

    public function editAction(){
        $params = $this->params()->fromRoute();
        $id = $params['id'];

        if(empty($id)){
            return $this->redirect()->toRoute('administrator');
        }

        //check exist
        $result = Business\Brand::getList([
            'brand_id' => $id,
            'not_status' => Model\Brand::BRAND_STATUS_REMOVE,
            'limit' => 1,
            'page' => 1
        ]);

        if(empty($result['rows'])){
            return $this->redirect()->toRoute('administrator');
        }

        $brand = $result['rows'][0];

        if($this->request->isPost()){
            $params = $this->params()->fromPost();
            $params['id'] = $id;
            $params = Business\Brand::update($params);
            if(!empty($params['success'])){
                return $this->redirect()->toRoute('administratorBrand', ['action' => 'edit', 'id' => $id]);
            }
        }

        $countries = Business\Country::getList([
            'not_status' => Model\Country::STATUS_REMOVE,
            'limit' => 1000,
            'offset' => 0,
            'order' => 'country_id desc'
        ]);
        return [
            'params' => $params,
            'brand' => $brand,
            'countries' => $countries
        ];
    }

    public function deleteAction(){
        if($this->request->isPost()){
            $params = $this->params()->fromPost();
            $result = Business\Brand::delete($params);
            return $this->getResponse()->setContent(json_encode($result));
        }
    }
}