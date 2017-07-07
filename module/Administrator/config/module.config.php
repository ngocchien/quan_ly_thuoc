<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Administrator;

use Zend\Router\Http\Literal;
use Zend\Router\Http\Segment;

return [
    'router' => [
        'routes' => [
	        'administrator' => array(
		        'type' => Segment::class,
		        'options' => array(
			        'route' => '/admin[/:action]',
			        'defaults' => array(
			            'module' => 'administrator',
				        'controller' => 'Administrator\Controller\Index',
				        'action'        => 'index'
			        ),
		        ),
	        ),
	        'administratorUser' => array(
		        'type' => Segment::class,
		        'options' => array(
			        'route' => '/admin/user[/:action][/:id][/:page][/:limit]',
			        'constraints' => array(
				        'id' => '[0-9]+',
				        'page' => '[0-9]+',
				        'limit' => '[0-9]+',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*'
			        ),
			        'defaults' => array(
                        'module' => 'Administrator',
				        'controller' => 'Administrator\Controller\User',
				        'action'        => 'index',
                        'page' => 1,
                        'limit' => 10,
			        ),
		        ),
	        ),
	        'administratorBanner' => array(
		        'type' => Segment::class,
		        'options' => array(
			        'route' => '/admin/banner[/:action][/:id]',
			        'constraints' => array(
				        'id' => '[0-9]+',
			        ),
			        'defaults' => array(
                        'module' => 'administrator',
				        'controller' => 'Administrator\Controller\Banner',
				        'action'        => 'index'
			        ),
		        ),
	        ),
	        'administratorCategory' => array(
		        'type' => Segment::class,
		        'options' => array(
			        'route' => '/admin/category[/:action][/:id]',
			        'constraints' => array(
				        'id' => '[0-9]+',
			        ),
			        'defaults' => array(
                        'module' => 'administrator',
				        'controller' => 'Administrator\Controller\Category',
				        'action'        => 'index'
			        ),
		        ),
	        ),
	        'administratorMenu' => array(
		        'type' => Segment::class,
		        'options' => array(
			        'route' => '/admin/menu[/:action][/:id]',
			        'constraints' => array(
				        'id' => '[0-9]+',
			        ),
			        'defaults' => array(
                        'module' => 'administrator',
				        'controller' => 'Administrator\Controller\Menu',
				        'action'        => 'index'
			        ),
		        ),
	        ),
	        'administratorPost' => array(
		        'type' => Segment::class,
		        'options' => array(
			        'route' => '/admin/post[/:action][/:id]',
			        'constraints' => array(
				        'id' => '[0-9]+',
			        ),
			        'defaults' => array(
                        'module' => 'administrator',
				        'controller' => 'Administrator\Controller\Post',
				        'action'        => 'index'
			        ),
		        ),
	        ),
	        'administratorProduct' => array(
		        'type' => Segment::class,
		        'options' => array(
			        'route' => '/admin/product[/:action][/:id]',
			        'constraints' => array(
				        'id' => '[0-9]+',
			        ),
			        'defaults' => array(
                        'module' => 'administrator',
				        'controller' => 'Administrator\Controller\Product',
				        'action'        => 'index'
			        ),
		        ),
	        ),
	        'administratorIndexRestApi' => array(
		        'type' => Segment::class,
		        'options' => array(
                    'module' => 'administrator',
			        'route' => '/api/administrator/index[/:id]',
			        'constraints' => array(
				        'id' => '[0-9]+',
			        ),
			        'defaults' => array(
				        'controller' => 'Administrator\Controller\IndexRest',
			        ),
		        ),
	        ),
            'administratorAuth' => array(
                'type' => Segment::class,
                'options' => array(
                    'module' => 'administrator',
                    'route' => '/admin/auth[/:action]',
                    'defaults' => array(
                        'module' => 'administrator',
                        'controller' => 'Administrator\Controller\Auth',
                        'action'        => 'login'
                    ),
                ),
            ),
            'administratorGroup' => array(
                'type' => Segment::class,
                'options' => array(
                    'module' => 'administrator',
                    'route' => '/admin/group[/:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+',
                    ),
                    'defaults' => array(
                        'module' => 'administrator',
                        'controller' => 'Administrator\Controller\Group',
                        'action'        => 'index'
                    ),
                ),
            ),
            'administratorPermission' => array(
                'type' => Segment::class,
                'options' => array(
                    'module' => 'administrator',
                    'route' => '/admin/permission[/:action][/uid/:uid][/gid/:gid]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'uid' => '[0-9]+',
                        'gid' => '[0-9]+',
                    ),
                    'defaults' => array(
                        'module' => 'administrator',
                        'controller' => 'Administrator\Controller\Permission',
                        'action'        => 'index'
                    ),
                ),
            ),
	        'administratorUploadsRestApi' => array(
		        'type' => Segment::class,
		        'options' => array(
			        'route' => '/api/administrator/uploads[/:id]',
			        'constraints' => array(
				        'id' => '[0-9]+',
			        ),
			        'defaults' => array(
				        'controller' => 'Administrator\Controller\UploadsRest',
			        ),
		        ),
	        ),
            'administratorBrand' => array(
                'type' => Segment::class,
                'options' => array(
                    'module' => 'administrator',
                    'route' => '/admin/brand[/:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+',
                    ),
                    'defaults' => array(
                        'module' => 'administrator',
                        'controller' => 'Administrator\Controller\Brand',
                        'action'        => 'index'
                    ),
                ),
            ),
            'administratorProperties' => array(
                'type' => Segment::class,
                'options' => array(
                    'module' => 'administrator',
                    'route' => '/admin/properties[/:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z0-9_-]*',
                        'id' => '[0-9]+',
                    ),
                    'defaults' => array(
                        'module' => 'administrator',
                        'controller' => 'Administrator\Controller\Properties',
                        'action'        => 'index'
                    ),
                ),
            ),
        ],
    ],
    'console' => array(
        'router' => array(
            'routes' => array(
                'list-users' => array(
                    'options' => array(
                        'route'    => 'show',
                        'defaults' => array(
                            'controller' => 'Administrator\Controller\ConsoleController',
                            'action'     => 'index'
                        )
                    )
                )
            )
        )
    ),
    'controllers' => [
        'invokables' => array(
	        'Administrator\Controller\Index' => 'Administrator\Controller\IndexController',
	        'Administrator\Controller\User' => 'Administrator\Controller\UserController',
	        'Administrator\Controller\Group' => 'Administrator\Controller\GroupController',
	        'Administrator\Controller\Banner' => 'Administrator\Controller\BannerController',
	        'Administrator\Controller\Category' => 'Administrator\Controller\CategoryController',
	        'Administrator\Controller\Menu' => 'Administrator\Controller\MenuController',
	        'Administrator\Controller\Post' => 'Administrator\Controller\PostController',
	        'Administrator\Controller\Product' => 'Administrator\Controller\ProductController',
	        'Administrator\Controller\IndexRest' => 'Administrator\Controller\IndexRestController',
	        'Administrator\Controller\Auth' => 'Administrator\Controller\AuthController',
            'Administrator\Controller\Permission' => 'Administrator\Controller\PermissionController',
            'Administrator\Controller\Brand' => 'Administrator\Controller\BrandController',
            'Administrator\Controller\Properties' => 'Administrator\Controller\PropertiesController',
            'Administrator\Controller\UploadsRest' => 'Administrator\Controller\UploadsRestController',
            'Administrator\Controller\Console' => 'Administrator\Controller\ConsoleController'
        )
    ],
    'module_layouts' => array(
        'Administrator' => 'administrator/layout',
    ),
    'view_helpers' => array(
        'invokables' => array(
            'translator' => 'APP\Helper\Translator',
            'paging' => 'APP\Helper\Paging',
        )
    ),
    'view_manager' => [
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'json_exceptions' => array(
            'display' => true,
            'ajax_only' => true,
            'show_trace' => true
        ),
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => [
	        'administrator/layout'    => __DIR__ . '/../view/layout/layout.phtml',
	        'administrator/header'    => __DIR__ . '/../view/layout/header.phtml',
	        'administrator/left_menu'    => __DIR__ . '/../view/layout/left-menu.phtml',
	        'administrator/footer'    => __DIR__ . '/../view/layout/footer.phtml',
	        'error/404'               => __DIR__ . '/../view/error/404.phtml',
	        'error/index'             => __DIR__ . '/../view/error/index.phtml',
	        'error/access-deni'             => __DIR__ . '/../view/error/access-deni.phtml',
            'administrator/login'    => __DIR__ . '/../view/layout/login.phtml',
        ],
        'template_path_stack' => [
            'administrator' => __DIR__ . '/../view',
        ],
        'strategies' => array(
            'ViewJsonStrategy',
        )
    ],
];
