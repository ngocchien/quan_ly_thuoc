<?php
/**
 * Created by PhpStorm.
 * User: chiennn
 * Date: 02/06/2017
 * Time: 22:25
 */

namespace APP\Controller;

use Zend\Mvc\MvcEvent,
    Zend\Mvc\Controller\AbstractActionController,
    Zend\Authentication\AuthenticationService,
    APP\Business,
    APP\Model;

class MyController extends AbstractActionController
{
    protected $serverUrl;
    private $resource;

    public function onDispatch(MvcEvent $e)
    {
        if (php_sapi_name() != 'cli') {
            $this->serverUrl = $this->request->getUri()->getScheme() . '://' . $this->request->getUri()->getHost();
            $params = array_merge($this->params()->fromRoute(), $this->params()->fromQuery());
            $arr_controller = explode('\\', $params['controller']);
            $this->params['module'] = strtolower($params['module']);
            $this->params['controller'] = strtolower(end($arr_controller));
            $this->params['action'] = strtolower($params['action']);
            $this->resource = $this->params['module'] . ':' . $this->params['controller'] . ':' . $this->params['action'];
            $auth = $this->authenticate($this->params);
            if ($this->params['module'] === 'administrator' && !$auth) {
                if (!$this->permission($this->params)) {
                    if ($this->request->isXmlHttpRequest()) {
                        die('Permission Denied!!!');
                    }
                    $this->layout('error/access-deni');
                    return false;
                }
            }
        }
        return parent::onDispatch($e);
    }

    private function authenticate($params)
    {
        define('MODULE', $params['module']);
        define('CONTROLLER', $params['controller']);
        define('ACTION', $params['action']);

        $auth = new AuthenticationService();
        $user = $auth->getIdentity();

        if ($params['module'] === 'administrator') {
            if (empty($user)) {
                return $this->redirect()->toRoute('administratorAuth', array('action' => 'login'));
            }
            define('USER_ID', $user['user_id']);
            define('USER_NAME', $user['user_name']);
            define('FULL_NAME', $user['full_name']);
            define('EMAIL', $user['email']);
            define('GROUP_ID', $user['group_id'] ? $user['group_id'] : 0);
            define('IS_ACP', empty($user['is_acp'] ? 0 : $user['is_acp']));
            define('IS_FULL_ACCESS', empty($user['is_full_access']) ? 0 : $user['is_full_access']);
            define('IS_ADMIN', empty($user['is_full_access']) ? 0 : $user['is_full_access']);
            define('PERMISSION', json_encode($user['permission']));
        }

        if ($params['module'] === 'application') {
            //get Menu
            $result = Business\Menu::getList([
                'status' => Model\Menu::MENU_STATUS_ACTIVE,
                'limit' => 100
            ]);
            $menu_parent = $menu_child = [];
            if (!empty($result['rows'])) {
                foreach ($result['rows'] as $row) {
                    if ($row['parent_id'] == 0) {
                        $menu_parent[] = $row;
                    } else {
                        $menu_child[$row['parent_id']][] = $row;
                    }
                }
            }

            define('MENU_PARENT', json_encode($menu_parent));
            define('MENU_CHILD', json_encode($menu_child));

            //get category
            $result = Business\Category::getList([
                'status' => Model\Category::CATEGORY_STATUS_ACTIVE,
                'limit' => 100
            ]);

            $cate_main = $cate_child = [];
            if (!empty($result['rows'])) {
                foreach ($result['rows'] as $row) {
                    if ($row['parent_id'] == 0) {
                        $cate_main[] = $row;
                    } else {
                        $cate_child[$row['parent_id']][] = $row;
                    }
                }
            }

            define('CATE_MAIN', json_encode($cate_main));
            define('CATE_CHILD', json_encode($cate_child));
            unset($menu_parent, $menu_child, $cate_main, $cate_child, $result);
        }
        unset($auth, $user);
    }

    private function permission($params)
    {

        //check can access CPanel
        if (IS_ACP != 1) {
            return false;
        }

        //check use in full_access role
        if (IS_FULL_ACCESS) {
            return true;
        }

        $action = $params['action'];
        $controller = $params['controller'];
        $module = $params['module'];

        $key = $module . ':' . $controller . ':' . $action;
        $permissions = json_decode(PERMISSION, true);

        if (empty($permissions)) {
            return false;
        }

        foreach ($permissions as $permission) {
            $build = $permission['module'] . ':' . $permission['controller'] . ':' . $permission['action'];
            if ($build == $key) {
                return true;
            }
        }
        return false;
    }
}