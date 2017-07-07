<?php
/**
 * Created by PhpStorm.
 * User: chiennn
 * Date: 03/06/2017
 * Time: 10:04
 */

namespace Administrator\Controller;

use Zend\Mvc\MvcEvent,
    Zend\Mvc\Controller\AbstractActionController,
    APP\Business,
    Zend\View\Model\ViewModel,
    Zend\Authentication\AuthenticationService;

class AuthController extends AbstractActionController{

    public function loginAction()
    {
        try {
            $auth = new AuthenticationService();
            if($auth->hasIdentity()){
                return $this->redirect()->toRoute('administrator');
            }

            $result = array();
            $resp = array();
            if($this->request->isPost()){
                $params = $this->params()->fromPost();
                $result = Business\User::checkLogin($params);
                if(!empty($result['success'])){
                    return $this->redirect()->toRoute('administrator');
                }
            }
            //Render admin layout
            self::renderLayout($resp);

            return new ViewModel(array(
                'params' => $result
            ));
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), $e->getCode());
        }
    }

    public function logoutAction(){
        Business\User::userLogout();
        return $this->redirect()->toRoute('administrator');
    }

    public function renderLayout($resp){
        //Render admin layout
        $layout = $this->layout();
        $layout->setTemplate('administrator/login');
    }
}