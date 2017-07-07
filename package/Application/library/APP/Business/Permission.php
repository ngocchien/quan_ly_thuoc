<?php
/**
 * Created by PhpStorm.
 * User: chiennn
 * Date: 03/06/2017
 * Time: 08:50
 */
namespace APP\Business;

use APP\Model;
use Zend\Code\Scanner\DirectoryScanner;

class Permission
{
    public static function create($params){
        $id_access = (int) $params['id_access'];
        $part = $params['part'];
        $resource = $params['resource'];

        if(empty($id_access) || empty($part) || empty($resource)){
            return [
                'st' => -1,
                'ms' => 'Xảy ra lỗi! Vui lòng thử lại!'
            ];
        }

        $arr_resource = explode(':', $resource);
        $module = $arr_resource[0];
        $controller = $arr_resource[1];
        $action = $arr_resource[2];

        $result =  Model\Permission::create([
            'module' => $module,
            'controller' => $controller,
            'action' => $action,
            'group_id' => $part == 'gid' ? $id_access : '',
            'user_id' => $part == 'uid' ? $id_access : '',
            'status' => Model\Permission::PERMISSION_STATUS_ACTIVE,
            'user_created' => USER_ID,
            'created_date'  => time()
        ]);

        if(!$result){
            return [
                'st' => -1,
                'ms' => 'Xảy ra lỗi! Vui lòng thử lại!'
            ];
        }

        return [
            'st' => 1,
            'ms' => 'Phân quyền thành công!'
        ];
    }

    public static function get($params){
        $limit = empty($params['limit']) ? 10 : (int)$params['limit'];
        $page = empty($params['page']) ? 1 : (int)$params['page'];
        $offset = $limit * ($page - 1);
        $params['offset'] = $offset;
        $params['limit'] = $limit;
        $result = Model\Permission::get($params);
        return $result;
    }

    public static function delete($params){

        if(!defined('IS_ADMIN') || IS_ADMIN != 1){
            return [
                'error' => 'error',
                'st' => -1,
                'ms' => 'Permission Deni!!!'
            ];
        }

        $id_access = (int) $params['id_access'];
        $part = $params['part'];
        $resource = $params['resource'];

        if(empty($id_access) || empty($part) || empty($resource)){
            return [
                'st' => -1,
                'ms' => 'Xảy ra lỗi! Vui lòng thử lại!'
            ];
        }

        $arr_resource = explode(':', $resource);
        $module = $arr_resource[0];
        $controller = $arr_resource[1];
        $action = $arr_resource[2];

        //get detail
        $condition  = [
            'module' => $module,
            'controller' => $controller,
            'action' => $action,
            'status' => Model\Permission::PERMISSION_STATUS_ACTIVE
        ];

        if($part == 'gid'){
            $condition['group_id'] = $id_access;
        }else{
            $condition['user_id'] = $id_access;
        }

        $permissions = Model\Permission::get($condition);

        if(empty($permissions['rows'])){
            return [
                'st' => -1,
                'ms' => 'Quyền này chưa được cấp!'
            ];
        }

        $perm_id = $permissions['rows'][0]['perm_id'];

        //update quyền
        $result = Model\Permission::update([
            'status'=> Model\Permission::PERMISSION_STATUS_REMOVE,
            'user_updated' => USER_ID,
            'updated_date' => time()
        ],$perm_id);

        if(!$result){
            return [
                'st' => -1,
                'ms' => 'Xảy ra lỗi! Vui lòng thử lại!'
            ];
        }

        return [
            'st' => 1,
            'ms' => 'Xóa quyền thành công!'
        ];
    }

    public static function getAllResource() {
        $dirScanner = new DirectoryScanner();
        $dirScanner->addDirectory(ROOT_PATH . '/module/Administrator/src/Administrator/Controller/');
        foreach ($dirScanner->getClasses(true) as $classScanner) {
            list($moduleName, $tmp, $controllerName) = explode('\\', $classScanner->getName());
            $controllerName = str_replace('Controller', '', $controllerName);
            if(strpos($controllerName, 'Rest')!==false || strpos($controllerName, 'Auth')!==false){
                continue;
            }
            $action = array();
            foreach ($classScanner->getMethods(true) as $method) {
                if (strpos($method->getName(), 'Action')) {
                    $action[] = str_replace('Action', '', $method->getName());
                }
            }
            $arrData[] = array('module' => $moduleName, 'controller' => $controllerName, 'action' => $action);

        }
        return $arrData;
    }
}