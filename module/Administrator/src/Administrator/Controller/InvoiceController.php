<?php
/**
 * Created by PhpStorm.
 * User: chiennn
 * Date: 09/07/2017
 * Time: 22:38
 */

namespace Administrator\Controller;

use APP\Controller\MyController;
use APP\Business;
use APP\Model;
use Zend\View\Model\ViewModel;

class InvoiceController extends MyController
{
    public function indexAction()
    {

    }

    public function createAction(){
        $params = $this->params()->fromRoute();

        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();
            $params = Business\Invoice::create($params);
            if (!empty($params['success'])) {
                return $this->redirect()->toRoute('administratorMenu', ['action' => 'edit', 'id' => $params['menu_id']]);
            }
        }

        //get list product stock in ware house
        $warehouses = Business\Warehouse::getProductStockInWarehouse([
            'limit' => 10000
        ]);

        $customer = [];
        if(!empty($params['customer_id'])){
            $result = Model\Customer::get([
                'customer_id' => $params['customer_id']
            ]);
            $customer = $result['rows'][0];
        }

        return [
            'params' => $params,
            'warehouses' => $warehouses,
            'customer' => $customer
        ];
    }

    public function updateAction(){

    }

    public function deleteAction(){

    }

    public function loadWarehouseAction(){
        try{
            $params = array_merge($this->params()->fromRoute(),$this->params()->fromQuery());
            $result = Business\Warehouse::getWarehouseForInvoice($params);
            $viewModel = new ViewModel();
            $viewModel->setVariables([
                'warehouses' => $result,
                'params' => $params
            ]);
            $viewModel->setTerminal(true);
            return $viewModel;
        }catch (\Exception $e){
            if(APPLICATION_ENV != 'production'){
                echo '<pre>';
                print_r($e->getMessage());
                echo '</pre>';
                die();
            }
            throw new \Exception($e->getMessage(), $e->getCode());
        }
    }

    public function loadCustomerAction(){
        $params = array_merge($this->params()->fromRoute(), $this->params()->fromQuery());
        $params['not_status'] = Model\Customer::STATUS_REMOVE;
        $params['limit'] = 1000;
        $result = Business\Customer::getList($params);
        return $this->getResponse()->setContent(json_encode(['st'=> 1, 'data' => $result]));
    }
}