<?php
/**
 * Created by PhpStorm.
 * User: chiennn
 * Date: 03/06/2017
 * Time: 08:50
 */

namespace APP\Business;

use APP\Model;
use APP\Utils;

class Category
{
    public static function create($params)
    {
        if (empty($params['cate_name'])) {
            $params['error'] = 'Vui lòng đầy đủ thông tin';
            return $params;
        }

        $cate_name = trim(strip_tags($params['cate_name']));
//        $meta_title = trim(strip_tags($params['meta_title']));
//        $meta_description = trim(strip_tags($params['meta_description']));
//        $meta_keyword = trim(strip_tags($params['meta_keyword']));
        $sort = (int)$params['sort'];
//        $status = (int)$params['status'];
        $status = 1;
        $parent_id = (int)$params['parent_id'];

        //check user name is exist DB
        $categories = Model\Category::get([
            'cate_name' => $cate_name,
            'not_status' => Model\Category::CATEGORY_STATUS_REMOVE,
            'limit' => 1,
            'offset' => 0
        ]);

        if (!empty($categories['rows'])) {
            $params['error'] = 'Danh mục này đã tồn tại!';
            return $params;
        }

        $cate_id = Model\Category::create([
            'cate_name' => $cate_name,
            'cate_slug' => Utils::getSlug($cate_name),
            'user_created' => USER_ID,
            'created_date' => time(),
//            'meta_title' => $meta_title,
//            'meta_description' => $meta_description,
//            'meta_keyword' => $meta_keyword,
            'sort' => $sort,
            'status' => $status,
            'parent_id' => $parent_id
        ]);

        if (!$cate_id) {
            $params['error'] = 'Xảy ra lỗi trong quá trình xử lý! <br/> Vui lòng thử lại sau giây lát!';
            return $params;
        }

        //build full sort
        self::buildFullSort($parent_id, $cate_id, $sort);

        return [
            'success' => true,
            'cate_id' => $cate_id
        ];

    }

    public static function getList($params)
    {
        $limit = empty($params['limit']) ? 10 : (int)$params['limit'];
        $page = empty($params['page']) ? 1 : (int)$params['page'];
        $offset = $limit * ($page - 1);
        $params['offset'] = $offset;
        $params['limit'] = $limit;
        $params['order'] = 'full_sort ASC, cate_id DESC';
        $result = Model\Category::get($params);
        return $result;
    }

    public static function getListParent($params = [])
    {
        $result = Model\Category::get(array_merge([
            'not_status' => Model\Category::CATEGORY_STATUS_REMOVE,
            'limit' => 1000,
            'offset' => 0,
            'order' => 'full_sort ASC'
        ], $params));
        return $result;
    }

    public static function update($params)
    {
        if (empty($params['cate_name'])) {
            $params['error'] = 'Vui lòng đầy đủ thông tin';
            return $params;
        }

        $cate_name = trim(strip_tags($params['cate_name']));
//        $meta_title = trim(strip_tags($params['meta_title']));
//        $meta_description = trim(strip_tags($params['meta_description']));
//        $meta_keyword = trim(strip_tags($params['meta_keyword']));
        $sort = (int)$params['sort'];
//        $status = (int)$params['status'];
        $status = 1;
        $parent_id = (int)$params['parent_id'];
        $cate_id = $params['cate_id'];

        //check user name is exist DB
        $categories = Model\Category::get([
            'cate_name' => $cate_name,
            'not_status' => Model\Category::CATEGORY_STATUS_REMOVE,
            'limit' => 1,
            'offset' => 0,
            'not_cate_id' => $cate_id
        ]);

        if (!empty($categories['rows'])) {
            $params['error'] = 'Danh mục này đã tồn tại!';
            return $params;
        }

        $updated = Model\Category::update([
            'cate_name' => $cate_name,
            'cate_slug' => Utils::getSlug($cate_name),
            'user_updated' => USER_ID,
            'updated_date' => time(),
//            'meta_title' => $meta_title,
//            'meta_description' => $meta_description,
//            'meta_keyword' => $meta_keyword,
            'sort' => $sort,
            'status' => $status,
            'parent_id' => $parent_id
        ], $cate_id);

        if (!$updated) {
            $params['error'] = 'Xảy ra lỗi trong quá trình xử lý! <br/> Vui lòng thử lại sau giây lát!';
            return $params;
        }

        //build full sort
        self::updateTreeCategory([
            'cate_id' => $cate_id,
            'parent_id' => $parent_id
        ]);

        return [
            'success' => true
        ];
    }

    public static function delete($params)
    {
        if(empty($params['cate_id'])){
            return [
                'st' => -1,
                'ms' => 'Xảy ra lỗi ! Vui lòng thử lại!',
                'error' => 'error'
            ];
        }

        $cate_id = $params['cate_id'];

        //get info product
        $result = Model\Category::get([
            'cate_id' => $cate_id,
            'not_status' => Model\Category::CATEGORY_STATUS_REMOVE
        ]);

        if(empty($result['rows'])){
            return [
                'st' => -1,
                'ms' => 'Xảy ra lỗi ! Vui lòng thử lại!',
                'error' => 'error'
            ];
        }

        //check children
        $result = Model\Category::get([
            'parent_id' => $cate_id,
            'not_status' => Model\Category::CATEGORY_STATUS_REMOVE
        ]);

        if(!empty($result['rows'])){
            return [
                'st' => -1,
                'ms' => 'Danh mục có chứa nhiều danh mục con. Vui lòng xóa hết các danh mục con trước!',
                'error' => 'error'
            ];
        }

        //delete
        $status = Model\Category::update([
            'status' => Model\Category::CATEGORY_STATUS_REMOVE,
            'updated_date' => time(),
            'user_updated' => USER_ID
        ],[
            $cate_id
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
            'ms' => 'Xóa Danh mục thành công!',
            'success' => 'success'
        ];
    }

    public static function buildFullSort($parent_id, $cate_id, $sort)
    {
        $full_sort = sprintf('%04d', $sort) . ':' . sprintf('%04d', $cate_id) . ':';
        if ($parent_id > 0) {
            $categories = Model\Category::get([
                'cate_id' => $parent_id
            ]);
            $category = $categories['rows'][0];
            $full_sort = $category['full_sort'] . $full_sort;
        }
        $updated = Model\Category::update([
            'full_sort' => $full_sort,
            'updated_date' => time(),
            'user_updated' => USER_ID
        ], $cate_id);

        return $updated;
    }

    public static function updateTreeCategory($params)
    {
        $updated = Model\Category::updateTreeCategory($params);
        return $updated;
    }

    public static function buildHtmlCate($cate_id, $arr_child, $html = '', $is_con = false, $link){
        foreach ($arr_child[$cate_id] as $cate){
            $html .= '<ul class="sub-menu">';
            $html .= '<li class="menu-item">';
            $html .= '<a href="'.$link.$cate['cate_slug'].'/">';
            $html .= $cate['cate_name'];
            $html .= '</a>';
            if(!empty($arr_child[$cate['cate_id']])){
                $html .= self::buildHtmlCate($cate['cate_id'],$arr_child, '', true, $link);
            }
            $html .= '</li>';
            $html .= '</ul>';
            return $html;
        }
    }
}