<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use APP\Controller\MyController;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use APP\Business;
use APP\Nosql;
use Zend\Session\Container;
use Zend\Http\Header\SetCookie;

class IndexController extends MyController
{
    public function indexAction()
    {
        return [];
        $result = array();

        //Layout
        $layout = $this->layout();

        $left_menu = new ViewModel();
        $left_menu->setTemplate('application/category');
        $header = new ViewModel();
        $header->setTemplate('application/header');
        $footer = new ViewModel();
        $footer->setTemplate('application/footer');

        $layout->addChild($header, 'header')->addChild($footer, 'footer');

        return new ViewModel(array(
            'result' => $result
        ));
    }
}
