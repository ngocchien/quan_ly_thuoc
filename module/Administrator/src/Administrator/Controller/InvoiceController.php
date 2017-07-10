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

class InvoiceController extends MyController
{
    public function indexAction()
    {

    }

    public function createAction(){
        $params = $this->params()->fromRoute();

        if ($this->request->isPost()) {
            $params = $this->params()->fromPost();
            $result = Business\Banner::create($params);
            if (!empty($result['success'])) {
                return $this->redirect()->toRoute('administratorMenu', ['action' => 'edit', 'id' => $result['menu_id']]);
            }
        }

        return [
            'params' => $params,
            'arr_status' => Model\Banner::renderStatus()
        ];
    }

    public function updateAction(){

    }

    public function deleteAction(){

    }
}