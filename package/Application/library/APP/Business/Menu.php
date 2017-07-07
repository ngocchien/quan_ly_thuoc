<?php
/**
 * Created by PhpStorm.
 * User: chiennn
 * Date: 17/06/2017
 * Time: 10:16
 */

namespace APP\Business;
use APP\Model;
use APP\Utils;


class Menu
{
    public static function create($params)
    {
        if (empty($params['menu_name']) || empty($params['url'])) {
            $params['error'] = 'Vui lòng đầy đủ thông tin';
            return $params;
        }

        $menu_name = trim(strip_tags($params['menu_name']));
        $sort = empty($params['sort']) ? 0 : (int)$params['sort'];
        $status = (int)$params['status'];
        $parent_id = (int)$params['parent_id'];
        $url = $params['url'];

        //check user name is exist DB
        $menus = Model\Menu::get([
            'menu_name' => $menu_name,
            'not_status' => Model\Menu::MENU_STATUS_REMOVE,
            'limit' => 1,
            'offset' => 0
        ]);

        if (!empty($menus['rows'])) {
            $params['error'] = 'Menu này đã tồn tại!';
            return $params;
        }

        $menu_id = Model\Menu::create([
            'menu_name' => $menu_name,
            'menu_slug' => Utils::getSlug($menu_name),
            'user_created' => USER_ID,
            'created_date' => time(),
            'sort' => $sort,
            'status' => $status,
            'parent_id' => $parent_id,
            'url' => $url
        ]);

        if (!$menu_id) {
            $params['error'] = 'Xảy ra lỗi trong quá trình xử lý! <br/> Vui lòng thử lại sau giây lát!';
            return $params;
        }

        return [
            'success' => true,
            'menu_id' => $menu_id
        ];

    }

    public static function getListParent(){
        $menu = Model\Menu::get([
            'parent_id' => 0,
            'not_status' => Model\Menu::MENU_STATUS_REMOVE,
            'limit' => 100,
            'offset' => 0,
            'order' => 'sort ASC'
        ]);
        return $menu;
    }

    public static function getList($params){
        $limit = empty($params['limit']) ? 100 : (int) $params['limit'];
        $page = empty($params['page']) ? 1 : (int)$params['page'];
        $offset = $limit * ($page - 1);
        $params['offset'] = $offset;
        $params['limit'] = $limit;
        $params['order'] = 'menu_id ASC, sort ASC';
        $result = Model\Menu::get($params);
        return $result;
    }

    public static function update($params)
    {
        if (empty($params['menu_name']) || empty($params['url'])) {
            $params['error'] = 'Vui lòng đầy đủ thông tin';
            return $params;
        }

        $menu_name = trim(strip_tags($params['menu_name']));
        $sort = empty($params['sort']) ? 0 : (int)$params['sort'];
        $status = (int)$params['status'];
        $parent_id = (int)$params['parent_id'];
        $url = $params['url'];
        $menu_id = $params['menu_id'];

        //check is exist DB
        $result = Model\Menu::get([
            'menu_name' => $menu_name,
            'not_status' => Model\Menu::MENU_STATUS_REMOVE,
            'limit' => 1,
            'offset' => 0,
            'not_menu_id' => $menu_id
        ]);

        if (!empty($result['rows'])) {
            $params['error'] = 'Menu này đã tồn tại!';
            return $params;
        }

        $updated = Model\Menu::update([
            'menu_name' => $menu_name,
            'menu_slug' => Utils::getSlug($menu_name),
            'user_updated' => USER_ID,
            'updated_date' => time(),
            'sort' => $sort,
            'status' => $status,
            'parent_id' => $parent_id,
            'url' => $url
        ], $menu_id);

        if (!$updated) {
            $params['error'] = 'Xảy ra lỗi trong quá trình xử lý! <br/> Vui lòng thử lại sau giây lát!';
            return $params;
        }

        return [
            'success' => true
        ];
    }

    public static function delete($params){
        if(empty($params['menu_id'])){
            return [
                'st' => -1,
                'ms' => 'Xảy ra lỗi ! Vui lòng thử lại!',
                'error' => 'error'
            ];
        }

        $menu_id = $params['menu_id'];

        //get info product
        $result = Model\Menu::get([
            'menu_id' => $menu_id,
            'not_status' => Model\Menu::MENU_STATUS_REMOVE
        ]);

        if(empty($result['rows'])){
            return [
                'st' => -1,
                'ms' => 'Xảy ra lỗi ! Vui lòng thử lại!',
                'error' => 'error'
            ];
        }

        //check children
        $result = Model\Menu::get([
            'parent_id' => $menu_id,
            'not_status' => Model\Menu::MENU_STATUS_REMOVE
        ]);

        if(!empty($result['rows'])){
            return [
                'st' => -1,
                'ms' => 'Menu có chứa nhiều menu con. Vui lòng xóa hết các menu con trước!',
                'error' => 'error'
            ];
        }

        //delete
        $status = Model\Menu::update([
            'status' => Model\Menu::MENU_STATUS_REMOVE,
            'updated_date' => time(),
            'user_updated' => USER_ID
        ],[
            $menu_id
        ]);

        if(!$status){
            return [
                'st' => -1,
                'ms' => 'Xảy ra lỗi trong quá trình xử lý! Vui lòng thử lại!',
                'error' => 'error'
            ];
        }

        return [
            'st' => 1,
            'ms' => 'Xóa menu thành công!',
            'success' => 'success'
        ];
    }
}