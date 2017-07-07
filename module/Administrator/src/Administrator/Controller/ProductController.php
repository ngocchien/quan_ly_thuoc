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

class ProductController extends MyController {

	public function indexAction() {
        try {
            $params = array_merge($this->params()->fromRoute(),$this->params()->fromQuery());
            //get list user
            $params['not_status'] = Model\Product::PRODUCT_STATUS_REMOVE;
            $products = Business\Product::get($params);

            $categories = $users = [];
            if(!empty($products['rows'])){
                $arr_cate_id = $arr_user_id = [];
                foreach ($products['rows'] as $row){
                    if(!in_array($row['cate_id'],$arr_cate_id)){
                        $arr_cate_id[] = $row['cate_id'];
                    }

                    if(!in_array($row['user_created'],$arr_user_id)){
                        $arr_user_id[] = $row['user_created'];
                    }
                }

                if(!empty($arr_cate_id)){
                    $result = Business\Category::getList([
                        'in_cate_id' => $arr_cate_id,
                        'limit' => 1000
                    ]);
                    if(!empty($result['rows'])){
                        foreach ($result['rows'] as $row){
                            $categories[$row['cate_id']] = $row;
                        }
                    }
                }

                if(!empty($arr_user_id)){
                    $result = Business\User::getUSer([
                        'in_user_id' => $arr_user_id,
                        'limit' => 1000
                    ]);
                    if(!empty($result['rows'])){
                        foreach ($result['rows'] as $row){
                            $users[$row['user_id']] = $row;
                        }
                    }
                }
            }

            return [
                'params' => $params,
                'products' => $products,
                'categories' => $categories,
                'renderStatus' => Model\Product::renderStatus(),
                'users' => $users
            ];
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), $e->getCode());
        }
	}

	public function createAction(){
	    $params = array_merge($this->params()->fromQuery(),$this->params()->fromRoute());

	    if($this->request->isPost()){
	        $params = $this->params()->fromPost();
	        $params = Business\Product::create($params);

	        if(!empty($params['success'])){
                return $this->redirect()->toRoute('administratorProduct',['action' => 'edit', 'id' => $params['product_id']]);
            }
        }

	    //get category
        $categories = Business\Category::getList([
            'not_status' => Model\Category::CATEGORY_STATUS_REMOVE,
            'limit' => 1000,
            'offset' => 0,
            'order' => 'full_sort asc'
        ]);

	    $images = [];
	    if(!empty($params['fid'])){
            $result = Business\Upload::getList([
                'IN_FID' => $params['fid']
            ]);

            if(!empty($result['rows'])){
                foreach ($result['rows'] as $row){
                    $images[$row['fid']] = $row;
                }
            }
        }

        return [
            'params' => $params,
            'categories' => $categories,
            'render_status' => Model\Product::renderStatus(),
            'images' => $images
        ];
	}

	public function deleteAction(){
	    if($this->request->isPost()){
	        $params = $this->params()->fromPost();
	        $result = Business\Product::delete($params);
            return $this->getResponse()->setContent(json_encode($result));
        }
    }

    public function editAction(){
        $params = $this->params()->fromRoute();
        $id = $params['id'];
        if(empty($id)){
            return $this->redirect()->toRoute('administrator');
        }

        //check product
        $result = Business\Product::get([
            'product_id' => $id,
            'not_status' => Model\Product::PRODUCT_STATUS_REMOVE
        ]);

        if(empty($result['rows'])){
            return $this->redirect()->toRoute('administrator');
        }

        $product = $result['rows'][0];

        if($this->request->isPost()){
            $params = $this->params()->fromPost();
            $params['product_id'] = $id;
            $params = Business\Product::update($params);
            if(!empty($params['success'])){
                return $this->redirect()->toRoute('administratorProduct', ['action' => 'edit', 'id' => $id]);
            }
        }

        //get category
        $categories = Business\Category::getList([
            'not_status' => Model\Category::CATEGORY_STATUS_REMOVE,
            'limit' => 1000,
            'offset' => 0,
            'order' => 'full_sort asc'
        ]);

        $images = [];
        if(!empty($params['fid'])){
            $result = Business\Upload::getList([
                'IN_FID' => $params['fid']
            ]);

            if(!empty($result['rows'])){
                foreach ($result['rows'] as $row){
                    $images[$row['fid']] = $row;
                }
            }
        }else{
            if(!empty($product['images'])){
                $result = Business\Upload::getList([
                    'IN_FID' => explode(',',$product['images'])
                ]);
                if(!empty($result['rows'])){
                    foreach ($result['rows'] as $row){
                        $images[$row['fid']] = $row;
                    }
                }
            }
        }

        return [
            'images' => $images,
            'product' => $product,
            'params' => $params,
            'categories' => $categories,
            'render_status' => Model\Product::renderStatus()
        ];

    }
}