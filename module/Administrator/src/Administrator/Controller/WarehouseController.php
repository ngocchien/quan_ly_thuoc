<?php
/**
 * Created by PhpStorm.
 * User: chiennn
 * Date: 07/07/2017
 * Time: 10:39
 */

namespace Administrator\Controller;

use APP\Controller\MyController;
use APP\Business;
use APP\Model;

class WarehouseController extends MyController
{
    public function indexAction()
    {
        $params = array_merge($this->params()->fromRoute(), $this->params()->fromQuery());
        $params['not_status'] = Model\Warehouse::STATUS_REMOVE;

        //get list
        $user_id = $users = $properties_id = $properties = $product_id = $products = $brand_id = [];
        $warehouses = Business\Warehouse::getList($params);

        if (!empty($warehouses['rows'])) {
            foreach ($warehouses['rows'] as $row) {
                $user_id[] = $row['user_created'];
                $properties_id[] = $row['properties_id'];
                $product_id[] = $row['product_id'];
            }
        }

        if (!empty($user_id)) {
            $result = Model\User::getUser([
                'in_user_id' => array_values($user_id),
                'limit' => count($user_id),
                'offset' => 0
            ]);

            if (!empty($result['rows'])) {
                foreach ($result['rows'] as $row) {
                    $users[$row['user_id']] = $row;
                }
            }
        }

        if (!empty($properties_id)) {
            $result = Model\Properties::get([
                'in_id' => array_values($properties_id),
                'limit' => count($properties_id),
                'offset' => 0
            ]);

            if (!empty($result['rows'])) {
                foreach ($result['rows'] as $row) {
                    $properties[$row['id']] = $row;
                }
            }
        }

        if (!empty($product_id)) {
            $result = Model\Product::get([
                'in_product_id' => array_values($product_id),
                'limit' => count($product_id),
                'offset' => 0
            ]);

            if (!empty($result['rows'])) {
                foreach ($result['rows'] as $row) {
                    $products[$row['product_id']] = $row;
                    if (!empty($row['brand_id'])) {
                        $brand_id[] = $row['brand_id'];
                    }
                }
            }
        }

        $brands = [];
        if (!empty($brand_id)) {
            $result = Model\Brand::get([
                'in_brand_id' => array_values(array_unique($brand_id)),
                'limit' => 10000
            ]);

            if ($result['total']) {
                foreach ($result['rows'] as $row) {
                    $brands[$row['brand_id']] = $row;
                }
            }
        }

        $params['total'] = $warehouses['total'];

        return [
            'params' => $params,
            'warehouses' => $warehouses,
            'users' => $users,
            'properties' => $properties,
            'products' => $products,
            'brands' => $brands,
            'limit_query' => Model\Common::getListLimitQuery()
        ];
    }

    public function createAction()
    {
        $params = $this->params()->fromRoute();

        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();
            $params = Business\Warehouse::create($params);
            if (!empty($params['success'])) {
                $_SESSION['create-warehouse-success'] = true;
                return $this->redirect()->toRoute('administratorWarehouse', ['action' => 'edit', 'id' => $params['id']]);
            }
        }

        //list properties
        $properties = Business\Properties::getList([
            'not_status' => Model\Properties::PROPERTIES_STATUS_REMOVE,
            'limit' => 10000
        ]);

        $products = Business\Product::get([
            'not_status' => Model\Product::PRODUCT_STATUS_REMOVE,
            'limit' => 10000
        ]);

        $brands = [];
        $result = Model\Brand::get([
            'limit' => 10000,
//            'not_status' => Model\Brand::BRAND_STATUS_REMOVE
        ]);

        if (!empty($result['rows'])) {
            foreach ($result['rows'] as $row) {
                $brands[$row['brand_id']] = $row;
            }
        }

        return [
            'params' => $params,
            'properties' => $properties,
            'products' => $products,
            'brands' => $brands
        ];
    }

    public function editAction()
    {
        try {
            $params = $this->params()->fromRoute();
            $id = $params['id'];

            if (empty($id)) {
                return $this->redirect()->toRoute('administrator');
            }

            //check exist
            $result = Business\Warehouse::getList([
                'warehouse_id' => $id,
                'not_status' => Model\Warehouse::STATUS_REMOVE,
                'limit' => 1,
                'page' => 1
            ]);

            if (empty($result['rows'])) {
                return $this->redirect()->toRoute('administrator');
            }

            $warehouse = $result['rows'][0];

            if ($this->request->isPost()) {
                $params = $this->params()->fromPost();
                $params['warehouse_id'] = $id;
                $params = Business\Warehouse::update($params);
                if (!empty($params['success'])) {
                    $_SESSION['update-warehouse-success'] = true;
                    return $this->redirect()->toRoute('administratorWarehouse', ['action' => 'edit', 'id' => $id]);
                }
            }

            //list properties
            $properties = Business\Properties::getList([
                'not_status' => Model\Properties::PROPERTIES_STATUS_REMOVE,
                'limit' => 10000
            ]);

            $products = Business\Product::get([
                'not_status' => Model\Product::PRODUCT_STATUS_REMOVE,
                'limit' => 10000
            ]);

            return [
                'params' => $params,
                'warehouse' => $warehouse,
                'products' => $products,
                'properties' => $properties
            ];
        } catch (\Exception $ex) {
            echo '<pre>';
            print_r($ex->getMessage());
            echo '</pre>';
            die();
        }
    }

    public function deleteAction()
    {
        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();
            $result = Business\Warehouse::delete($params);
            return $this->getResponse()->setContent(json_encode($result));
        }
    }

    public function expireAction()
    {
        $params = array_merge($this->params()->fromRoute(), $this->params()->fromQuery());
        $params['not_status'] = Model\Warehouse::STATUS_REMOVE;
        $params['expire'] = 1;
        $params['is_notify'] = 1;

        //get list
        $user_id = $users = $properties_id = $properties = $product_id = $products = [];
        $warehouses = Business\Warehouse::getListExpire($params);

        if (!empty($warehouses['rows'])) {
            foreach ($warehouses['rows'] as $row) {
                $user_id[] = $row['user_created'];
                $properties_id[] = $row['properties_id'];
                $product_id[] = $row['product_id'];
            }
        }

        if (!empty($user_id)) {
            $result = Model\User::getUser([
                'in_user_id' => array_values($user_id),
                'limit' => 100,
                'offset' => 0
            ]);

            if (!empty($result['rows'])) {
                foreach ($result['rows'] as $row) {
                    $users[$row['user_id']] = $row;
                }
            }
        }

        if (!empty($properties_id)) {
            $result = Model\Properties::get([
                'in_id' => array_values($properties_id),
                'limit' => 100,
                'offset' => 0
            ]);

            if (!empty($result['rows'])) {
                foreach ($result['rows'] as $row) {
                    $properties[$row['id']] = $row;
                }
            }
        }

        if (!empty($product_id)) {
            $result = Model\Product::get([
                'in_id' => array_values($product_id),
                'limit' => 100,
                'offset' => 0
            ]);

            if (!empty($result['rows'])) {
                foreach ($result['rows'] as $row) {
                    $products[$row['product_id']] = $row;
                }
            }
        }

        $params['total'] = $warehouses['total'];

        return [
            'params' => $params,
            'warehouses' => $warehouses,
            'users' => $users,
            'properties' => $properties,
            'products' => $products
        ];
    }

    public function expiredAction()
    {
        $params = array_merge($this->params()->fromRoute(), $this->params()->fromQuery());
        //get list
        $user_id = $users = $properties_id = $properties = $product_id = $products = [];
        $warehouses = Business\Warehouse::getListExpired($params);

        if (!empty($warehouses['rows'])) {
            foreach ($warehouses['rows'] as $row) {
                $user_id[] = $row['user_created'];
                $properties_id[] = $row['properties_id'];
                $product_id[] = $row['product_id'];
            }
        }

        if (!empty($user_id)) {
            $result = Model\User::getUser([
                'in_user_id' => array_values($user_id),
                'limit' => 100,
                'offset' => 0
            ]);

            if (!empty($result['rows'])) {
                foreach ($result['rows'] as $row) {
                    $users[$row['user_id']] = $row;
                }
            }
        }

        if (!empty($properties_id)) {
            $result = Model\Properties::get([
                'in_id' => array_values($properties_id),
                'limit' => 100,
                'offset' => 0
            ]);

            if (!empty($result['rows'])) {
                foreach ($result['rows'] as $row) {
                    $properties[$row['id']] = $row;
                }
            }
        }

        if (!empty($product_id)) {
            $result = Model\Product::get([
                'in_product_id' => array_values($product_id),
                'limit' => 1000,
                'offset' => 0
            ]);

            if (!empty($result['rows'])) {
                foreach ($result['rows'] as $row) {
                    $products[$row['product_id']] = $row;
                }
            }
        }

        $params['total'] = $warehouses['total'];

        return [
            'params' => $params,
            'warehouses' => $warehouses,
            'users' => $users,
            'properties' => $properties,
            'products' => $products
        ];
    }

    public function deleteExpireAction()
    {
        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();
            $result = Business\Warehouse::deleteExpire($params);
            return $this->getResponse()->setContent(json_encode($result));
        }
    }

    public function deleteExpiredAction()
    {
        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();
            $result = Business\Warehouse::deleteExpired($params);
            return $this->getResponse()->setContent(json_encode($result));
        }
    }
}