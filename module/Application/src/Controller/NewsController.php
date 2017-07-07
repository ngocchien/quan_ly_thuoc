<?php
/**
 * Created by PhpStorm.
 * User: tuandv
 * Date: 5/30/17
 * Time: 9:58 PM
 */
namespace Application\Controller;

use APP\Utils;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use APP\Business;
use APP\Model;
use Zend\Session\Container;

class NewsController extends AbstractActionController {
	public function indexAction() {

		$layout = $this->layout();
		$header = new ViewModel();
		$header->setTemplate('application/header');
		$footer = new ViewModel();
		$footer->setTemplate('application/footer');

		$layout->addChild($header, 'header')->addChild($footer, 'footer');

		$view = new ViewModel(array(
			'data' => array()
		));

		$view->setTemplate('application/index/news');

		return $view;
	}
}