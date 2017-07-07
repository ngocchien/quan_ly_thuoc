<?php
/**
 * Created by PhpStorm.
 * User: chiennn
 * Date: 17/06/2017
 * Time: 15:46
 */

namespace APP\Business;

use APP\Model;
use APP\Utils;

class Banner
{
    public static function create($params)
    {
        if (empty($params['banner_name']) || empty($params['redirect_url']) || empty($params['fid'])) {
            $params['error'] = 'Vui lòng đầy đủ thông tin';
            return $params;
        }

        $banner_name = trim(strip_tags($params['banner_name']));
        $sort = empty($params['sort']) ? 0 : (int)$params['sort'];
        $status = (int)$params['status'];
        $redirect_url = $params['redirect_url'];
        $fid = $params['fid'];

        //check user name is exist DB
        $result = Model\Banner::get([
            'banner_name' => $banner_name,
            'not_status' => Model\Banner::BANNER_STATUS_REMOVE,
            'limit' => 1,
            'offset' => 0
        ]);

        if (!empty($result['rows'])) {
            $params['error'] = 'Banner này đã tồn tại!';
            return $params;
        }

        $id = Model\Banner::create([
            'banner_name' => $banner_name,
            'banner_slug' => Utils::getSlug($banner_name),
            'user_created' => USER_ID,
            'created_date' => time(),
            'sort' => $sort,
            'status' => $status,
            'redirect_url' => $redirect_url,
            'fid' => $fid
        ]);

        if (!$id) {
            $params['error'] = 'Xảy ra lỗi trong quá trình xử lý! <br/> Vui lòng thử lại sau giây lát!';
            return $params;
        }

        return [
            'success' => true,
            'banner_id' => $id
        ];

    }

    public static function getList($params){
        $limit = empty($params['limit']) ? 100 : (int) $params['limit'];
        $page = empty($params['page']) ? 1 : (int)$params['page'];
        $offset = $limit * ($page - 1);
        $params['offset'] = $offset;
        $params['limit'] = $limit;
        $params['order'] = 'sort ASC, banner_id DESC';
        $result = Model\Banner::get($params);
        return $result;
    }

    public static function update($params)
    {
        if (empty($params['banner_name']) || empty($params['redirect_url']) || empty($params['fid'])) {
            $params['error'] = 'Vui lòng đầy đủ thông tin';
            return $params;
        }

        $banner_name = trim(strip_tags($params['banner_name']));
        $sort = empty($params['sort']) ? 0 : (int)$params['sort'];
        $status = (int)$params['status'];
        $redirect_url = $params['redirect_url'];
        $fid = $params['fid'];
        $banner_id = $params['banner_id'];



        //check user name is exist DB
        $result = Model\Banner::get([
            'banner_name' => $banner_name,
            'not_status' => Model\Banner::BANNER_STATUS_REMOVE,
            'limit' => 1,
            'offset' => 0,
            'not_banner_id' => $banner_id
        ]);

        if (!empty($result['rows'])) {
            $params['error'] = 'Banner này đã tồn tại!';
            return $params;
        }
        $updated = Model\Banner::update([
            'banner_name' => $banner_name,
            'banner_slug' => Utils::getSlug($banner_name),
            'user_updated' => USER_ID,
            'updated_date' => time(),
            'sort' => $sort,
            'status' => $status,
            'redirect_url' => $redirect_url,
            'fid' => $fid
        ], $banner_id);

        if (!$updated) {
            $params['error'] = 'Xảy ra lỗi trong quá trình xử lý! <br/> Vui lòng thử lại sau giây lát!';
            return $params;
        }

        return [
            'success' => true
        ];
    }

    public static function delete($params){
        if(empty($params['banner_id'])){
            return [
                'st' => -1,
                'ms' => 'Xảy ra lỗi ! Vui lòng thử lại!',
                'error' => 'error'
            ];
        }

        $banner_id = $params['banner_id'];

        //get info product
        $result = Model\Banner::get([
            'banner_id' => $banner_id,
            'not_status' => Model\Banner::BANNER_STATUS_REMOVE
        ]);

        if(empty($result['rows'])){
            return [
                'st' => -1,
                'ms' => 'Xảy ra lỗi ! Vui lòng thử lại!',
                'error' => 'error'
            ];
        }

        //delete
        $status = Model\Banner::update([
            'status' => Model\Banner::BANNER_STATUS_REMOVE,
            'updated_date' => time(),
            'user_updated' => USER_ID
        ],[
            $banner_id
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
            'ms' => 'Xóa banner thành công!',
            'success' => 'success'
        ];
    }
}