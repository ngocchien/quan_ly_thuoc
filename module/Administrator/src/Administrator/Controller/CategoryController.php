<?php
/**
 * Created by PhpStorm.
 * User: tuandv
 * Date: 5/31/17
 * Time: 11:50 PM
 */

namespace Administrator\Controller;

use APP\Controller\MyController;
use APP\Business;
use APP\Model;
use APP\Helper\PagingText;

class CategoryController extends MyController {

	public function indexAction() {
        $params = array_merge($this->params()->fromRoute(),$this->params()->fromQuery());
        $params['not_status'] = Model\Category::CATEGORY_STATUS_REMOVE;
        //get list
        $params['limit'] = 1;
//        $categories = Business\Category::getList($params);
        $categories = Business\Category::getList($params);

        $arr_user_id = $users = [];
        if(!empty($categories['rows'])){
            foreach ($categories['rows'] as $row){
                $arr_user_id[] = $row['user_created'];
            }

            if(!empty($arr_user_id)){
                $result = Business\User::getUSer([
                    'in_user_id' => array_values(array_unique($arr_user_id)),
                    'limit' => 100
                ]);
                if(!empty($result['rows'])){
                    foreach ($result['rows'] as $row){
                        $users[$row['user_id']] = $row;
                    }
                }
            }
        }

        $params['total'] = $categories['total'];
        //get list category
        return [
            'params' => $params,
            'categories' => $categories,
            'users' => $users
        ];
	}

	public function createAction(){
        $params = $this->params()->fromRoute();

        if($this->request->isPost()){
            $params = $this->params()->fromPost();
            $result = Business\Category::create($params);
            if(!empty($result['success'])){
                return $this->redirect()->toRoute('administratorCategory',['action' => 'edit', 'id' => $result['cate_id']]);
            }
        }

        //get list category
        $parent = Business\Category::getListParent();

		return [
		    'params' => $params,
            'parent' => $parent
        ];
	}

	public function editAction(){
        $params = $this->params()->fromRoute();
        $cate_id = $params['id'];
        if(empty($cate_id)){
            return $this->redirect()->toRoute('administrator');
        }

        //check user
        $categories = Business\Category::getList([
            'cate_id' => $cate_id,
            'not_status' => Model\Category::CATEGORY_STATUS_REMOVE
        ]);

        if(empty($categories['rows'])){
            return $this->redirect()->toRoute('administrator');
        }

        $category = $categories['rows'][0];

        if($this->request->isPost()){
            $params = $this->params()->fromPost();
            $params['cate_id'] = $cate_id;
            $params = Business\Category::update($params);
            if(!empty($params['success'])){
                return $this->redirect()->toRoute('administratorCategory',['action' => 'edit', 'id' => $cate_id]);
            }
        }

        //get list category
        $parent = Business\Category::getListParent([
            'not_like_full_sort' => $category['full_sort']
        ]);

        return [
            'params' => $params,
            'parent' => $parent,
            'category' => $category,
            'render_status' => Model\Category::renderStatus()
        ];
    }

    public function deleteAction(){
        if($this->request->isPost()){
            $params = $this->params()->fromPost();
            $result = Business\Category::delete($params);
            return $this->getResponse()->setContent(json_encode($result));
        }
    }
}