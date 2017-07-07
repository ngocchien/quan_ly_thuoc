<?php
/**
 * Created by PhpStorm.
 * User: chiennn
 * Date: 18/06/2017
 * Time: 15:23
 */

namespace APP\Business;

use APP\Model;
use APP\Utils;

class Post
{
    public static function create($params){

        if(empty($params['post_name']) || empty($params['post_content']) || empty($params['meta_title']) || empty($params['meta_keyword']) ||  empty($params['meta_description'])){
            $params['error'] = 'Vui lòng nhập đầy đủ nội dung!';
            return $params;
        }

        $post_name =  trim(strip_tags($params['post_name']));
        $post_content = $params['post_content'];
        $cate_id = empty($params['cate_id']) ? 0 : $params['cate_id'];
        $status = (int)$params['status'];
        $images = empty($params['fid']) ? : $params['fid'];
        $meta_title = empty($params['meta_title']) ? : $params['meta_title'];
        $meta_description = empty($params['meta_description']) ? : $params['meta_description'];
        $meta_keyword = empty($params['meta_keyword']) ? : $params['meta_keyword'];

        //check name
        $exist = Model\Post::get([
            'post_name' => $post_name,
            'limit' => 1,
            'offset' => 0,
            'not_status' => Model\Post::POST_STATUS_REMOVE,
            'cate_id' => $cate_id
        ]);

        if(!empty($exist['rows'])){
            $params['error'] = 'Tiêu đề bài viết này đã tồn tại trong hệ thống!';
            return $params;
        }

        $id = Model\Post::create([
            'post_name' => $post_name,
            'post_slug' => Utils::getSlug($post_name),
            'status' => $status,
            'created_date' => time(),
            'user_created' => USER_ID,
            'cate_id' => $cate_id,
            'images'=> $images,
            'post_content' => $post_content,
            'meta_keyword' => $meta_keyword,
            'meta_description' => $meta_description,
            'meta_title' => $meta_title
        ]);

        if(!$id){
            $params['error'] = 'Xảy ra lỗi trong quá trình xử lý! Thử lại sau giây lát';
            return $params;
        }

        return [
            'success' => true,
            'post_id' => $id
        ];
    }

    public static function get($params){
        $limit = empty($params['limit']) ? 10 : (int)$params['limit'];
        $page = empty($params['page']) ? 1 : (int)$params['page'];
        $offset = $limit * ($page - 1);
        $params['offset'] = $offset;
        $params['limit'] = $limit;
        $result = Model\Post::get($params);
        return $result;
    }

    public static function update($params){
        if(empty($params['post_name']) || empty($params['post_content']) || empty($params['meta_title']) || empty($params['meta_keyword']) ||  empty($params['meta_description'])){
            $params['error'] = 'Vui lòng nhập đầy đủ nội dung!';
            return $params;
        }

        $post_name =  trim(strip_tags($params['post_name']));
        $post_content = $params['post_content'];
        $cate_id = empty($params['cate_id']) ? 0 : $params['cate_id'];
        $status = (int)$params['status'];
        $images = empty($params['fid']) ? : $params['fid'];
        $meta_title = empty($params['meta_title']) ? : $params['meta_title'];
        $meta_description = empty($params['meta_description']) ? : $params['meta_description'];
        $meta_keyword = empty($params['meta_keyword']) ? : $params['meta_keyword'];
        $post_id = $params['post_id'];

        //check name
        $exist = Model\Post::get([
            'post_name' => $post_name,
            'limit' => 1,
            'offset' => 0,
            'not_status' => Model\Post::POST_STATUS_REMOVE,
            'cate_id' => $cate_id,
            'not_post_id' => $post_id
        ]);

        if(!empty($exist['rows'])){
            $params['error'] = 'Tiêu đề bài viết này đã tồn tại trong hệ thống!';
            return $params;
        }

        //update
        $updated = Model\Post::update([
            'post_name' => $post_name,
            'post_slug' => Utils::getSlug($post_name),
            'status' => $status,
            'updated_date' => time(),
            'user_updated' => USER_ID,
            'cate_id' => $cate_id,
            'images'=> $images,
            'post_content' => $post_content,
            'meta_keyword' => $meta_keyword,
            'meta_description' => $meta_description,
            'meta_title' => $meta_title
        ],$post_id);

        if(!$updated){
            $params['error'] = 'Xảy ra lỗi trong quá trình xử lý! Thử lại sau giây lát';
            return $params;
        }

        return [
            'success' => true
        ];
    }

    public static function delete($params){

        if(empty($params['arr_post_id'])){
            return [
                'st' => -1,
                'ms' => 'Xảy ra lỗi ! Vui lòng thử lại!',
                'error' => 'error'
            ];
        }

        $arr_id = $params['arr_post_id'];

        //get info product
        $posts = Model\Post::get([
            'in_post_id' => $arr_id,
            'not_status' => Model\Post::POST_STATUS_REMOVE
        ]);

        if(empty($posts['rows'])){
            return [
                'st' => -1,
                'ms' => 'Xảy ra lỗi ! Vui lòng thử lại!',
                'error' => 'error'
            ];
        }


        //delete
        $status = Model\Post::updateByCondition([
            'status' => Model\Post::POST_STATUS_REMOVE,
            'updated_date' => time(),
            'user_updated' => USER_ID
        ],[
            'in_post_id' => $arr_id
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
            'ms' => 'Xóa bài viết thành công!',
            'success' => 'success'
        ];
    }
}