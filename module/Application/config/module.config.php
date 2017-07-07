<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

use Zend\Router\Http\Literal;
use Zend\Router\Http\Segment;

return [
    'router' => [
        'routes' => [
            'home' => [
                'type' => Literal::class,
                'options' => [
                    'route'    => '/',
                    'defaults' => [
                        'module' => 'Application',
                        'controller' => 'Application\Controller\Index',
                        'action'     => 'index',
                    ],
                ],
            ],
            'application' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/application[/:action]',
                    'defaults' => [
                        'module' => 'Application',
                        'controller'    => 'Application\Controller\Index',
                        'action'        => 'index',
                    ],
                ],
            ],
            'products' => [
	            'type'    => Segment::class,
	            'options' => [
		            'route'    => '/san-pham[/:name]/',
		            'constraints' => array(
			            'name' => '[a-zA-Z0-9_-]*'
		            ),
		            'defaults' => [
                        'module' => 'Application',
			            'controller'    => 'Application\Controller\Product',
			            'action'        => 'index',
		            ],
	            ],
            ],
            'news' => [
	            'type'    => Segment::class,
	            'options' => [
		            'route'    => '/tin-tuc[/:name]/',
		            'constraints' => array(
			            'name' => '[a-zA-Z0-9_-]*'
		            ),
		            'defaults' => [
			            'controller'    => 'Application\Controller\News',
			            'action'        => 'index',
		            ],
	            ],
            ],
            'category' => [
	            'type'    => Segment::class,
	            'options' => [
		            'route'    => '/the-loai[/:name]/',
		            'constraints' => array(
			            'name' => '[a-zA-Z0-9_-]*'
		            ),
		            'defaults' => [
                        'module' => 'Application',
			            'controller'    => 'Application\Controller\Category',
			            'action'        => 'index',
		            ],
	            ],
            ],
        ],
    ],
    'controllers' => [
        'invokables' => array(
            'Application\Controller\Index' => 'Application\Controller\IndexController',
            'Application\Controller\Product' => 'Application\Controller\ProductController',
            'Application\Controller\News' => 'Application\Controller\NewsController',
            'Application\Controller\Category' => 'Application\Controller\CategoryController',
        )
    ],
    'module_layouts' => array(
        'Application' => 'application/layout',
    ),
    'view_manager' => [
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => [
            'application/layout'    => __DIR__ . '/../view/layout/layout.phtml',
            'application/header'      => __DIR__ . '/../view/layout/header.phtml',
            'application/footer'      => __DIR__ . '/../view/layout/footer.phtml',
            'application/left-menu'      => __DIR__ . '/../view/layout/left-menu.phtml',
//            'error/404'               => __DIR__ . '/../view/error/404.phtml',
//            'error/index'             => __DIR__ . '/../view/error/index.phtml',
        ],

        'template_path_stack' => [
            'application' => __DIR__ . '/../view'
        ],
        'strategies' => array(
            'ViewJsonStrategy',
        )
    ],
];
