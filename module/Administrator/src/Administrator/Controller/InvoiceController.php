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
        $params = array_merge($this->params()->fromRoute(), $this->params()->fromQuery());
        //get list user
        $params['not_status'] = Model\Invoice::STATUS_REMOVE;
        $invoices = Business\Invoice::get($params);
        $params['total'] = $invoices['total'];

        return [
            'params' => $params,
            'invoices' => $invoices
        ];
    }

    public function createAction(){
        $params = $this->params()->fromRoute();

        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();
            $params = Business\Invoice::create($params);
            if (!empty($params['success'])) {
                $_SESSION['create-invoice-success'] = true;
                return $this->redirect()->toRoute('administratorInvoice', ['action' => 'edit', 'id' => $params['menu_id']]);
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

    public function editAction(){
        $params = array_merge($this->params()->fromRoute(), $this->params()->fromQuery());
        $id = $params['id'];
        if(empty($id)){
            return $this->redirect()->toRoute('administrator');
        }

        //check invoice
        $result = Business\Invoice::get([
            'invoice_id' => $id,
            'not_status' => Model\Invoice::STATUS_REMOVE
        ]);

        if(empty($result['rows'])){
            return $this->redirect()->toRoute('administrator');
        }

        $invoice = $result['rows'][0];

        if($this->request->isPost()){
            $params = $this->params()->fromPost();
            echo '<pre>';
            print_r('Chưa validate! Chức năng đang xây dựng! chưa thực hiện được thao tác update!');
            echo '</pre>';
            die();
            $params['product_id'] = $id;
            $params = Business\Product::update($params);
            if(!empty($params['success'])){
                $_SESSION['update-invoice-success'] = true;
                return $this->redirect()->toRoute('administratorInvoice', ['action' => 'edit', 'id' => $id]);
            }
        }

        //invoice detail
        $invoiceWarehouse = Model\InvoiceWarehouse::get([
            'invoice_id' => $id,
            'not_status' => Model\InvoiceWarehouse::STATUS_REMOVE,
            'limit' => 1000
        ]);

        //get list product stock in ware house
        $warehouses = Business\Warehouse::getProductStockInWarehouse([
            'limit' => 10000
        ]);

        $customer_id = empty($params['customer_id']) ? $invoice['customer_id'] : $params['customer_id'];
        $customer = [];
        if(!empty($customer_id)){
            $result = Model\Customer::get([
                'customer_id' => $customer_id
            ]);
            $customer = $result['rows'][0];
        }

        return [
            'params' => $params,
            'warehouses' => $warehouses,
            'customer' => $customer,
            'invoiceWarehouse' => $invoiceWarehouse,
            'invoice' => $invoice
        ];
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

    public function printInvoiceAction(){
        $params = $this->params()->fromRoute();
        $id = $params['id'];
        if(empty($id)){
            return $this->redirect()->toRoute('administrator');
        }

        //check invoice
        $result = Business\Invoice::get([
            'invoice_id' => $id,
            'not_status' => Model\Invoice::STATUS_REMOVE
        ]);

        if(empty($result['rows'])){
            return $this->redirect()->toRoute('administrator');
        }
    }
}