<?php
/**
 * Created by PhpStorm.
 * User: tuandv
 * Date: 5/30/17
 * Time: 9:58 PM
 */

namespace Application\Controller;

use APP\Controller\MyController;
use APP\Utils;
use APP\Business;
use APP\Model;

class CategoryController extends MyController
{
    public function indexAction()
    {
        try {
            $params = $this->params()->fromRoute();
            if(empty($params['name'])){
                return $this->redirect()->toRoute('home');
            }
            $slug = $params['name'];

            $result = Business\Category::getList([
                'cate_slug' => $slug,
                'not_status' => Model\Category::CATEGORY_STATUS_REMOVE
            ]);

            if(empty($result['rows'])){
                return $this->redirect()->toRoute('home');
            }

            $category = $result['rows'][0];

            //get product in category
            $products = Business\Product::get([
                'cate_id' => $category['cate_id'],
                'page' => empty($params['page']) ? 1 : $params['page'],
                'limit' => empty($params['limit']) ? 10 : $params['limit'],
                'status' => Model\Product::PRODUCT_STATUS_ACTIVE
            ]);

            return [
                'params' => $params,
                'category' => $category,
                'products' => $products
            ];
        } catch (\Exception $ex) {
            echo '<pre>';
            print_r($ex->getMessage());
            echo '</pre>';
            die();
        }
    }
}