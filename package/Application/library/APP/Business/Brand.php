<?php
/**
 * Created by PhpStorm.
 * User: chiennn
 * Date: 27/06/2017
 * Time: 21:28
 */

namespace APP\Business;

use APP\Model;
use APP\Utils;

class Brand
{
    public static function create($params)
    {
        if (empty($params['brand_name'])) {
            $params['error'] = 'Vui lòng đầy đủ thông tin';
            return $params;
        }

        $brand_name = trim(strip_tags($params['brand_name']));
        $status = 1;
        $country_id = empty($params['country_id']) ? '' : $params['country_id'];

        //check user name is exist DB
        $result = Model\Brand::get([
            'brand_name' => $brand_name,
            'not_status' => Model\Brand::BRAND_STATUS_REMOVE,
            'limit' => 1,
            'offset' => 0
        ]);

        if (!empty($result['rows'])) {
            $params['error'] = 'Nhãn hiệu này đã tồn tại!';
            return $params;
        }

        $id = Model\Brand::create([
            'brand_name' => $brand_name,
            'brand_slug' => Utils::getSlug($brand_name),
            'user_created' => USER_ID,
            'created_date' => time(),
            'status' => $status,
            'country_id' => $country_id
        ]);

        if (!$id) {
            $params['error'] = 'Xảy ra lỗi trong quá trình xử lý! <br/> Vui lòng thử lại sau giây lát!';
            return $params;
        }

        return [
            'success' => true,
            'id' => $id
        ];

    }

    public static function getList($params){
        $limit = empty($params['limit']) ? 100 : (int) $params['limit'];
        $page = empty($params['page']) ? 1 : (int)$params['page'];
        $offset = $limit * ($page - 1);
        $params['offset'] = $offset;
        $params['limit'] = $limit;
        $params['order'] = 'brand_id DESC';
        $result = Model\Brand::get($params);
        return $result;
    }

    public static function update($params)
    {
        if (empty($params['brand_name'])) {
            $params['error'] = 'Vui lòng đầy đủ thông tin';
            return $params;
        }

        $brand_name = trim(strip_tags($params['brand_name']));
        $status = 1;
        $id = $params['id'];
        $country_id = empty($params['country_id']) ? '' : $params['country_id'];

        //check user name is exist DB
        $result = Model\Brand::get([
            'brand_name' => $brand_name,
            'not_status' => Model\Brand::BRAND_STATUS_REMOVE,
            'limit' => 1,
            'offset' => 0,
            'not_brand_id' => $id
        ]);

        if (!empty($result['rows'])) {
            $params['error'] = 'Nhãn hiệu này đã tồn tại!';
            return $params;
        }

        $updated = Model\Brand::update([
            'brand_name' => $brand_name,
            'brand_slug' => Utils::getSlug($brand_name),
            'user_updated' => USER_ID,
            'updated_date' => time(),
            'status' => $status,
            'country_id' => $country_id
        ], $id);

        if (!$updated) {
            $params['error'] = 'Xảy ra lỗi trong quá trình xử lý! <br/> Vui lòng thử lại sau giây lát!';
            return $params;
        }

        return [
            'success' => true
        ];
    }

    public static function delete($params){

        if(empty($params['brand_id'])){
            return [
                'st' => -1,
                'ms' => 'Xảy ra lỗi ! Vui lòng thử lại!',
                'error' => 'error'
            ];
        }

        $id = $params['brand_id'];

        //get info product
        $result = Model\Brand::get([
            'brand_id' => $id,
            'not_status' => Model\Brand::BRAND_STATUS_REMOVE
        ]);

        if(empty($result['rows'])){
            return [
                'st' => -1,
                'ms' => 'Xảy ra lỗi ! Vui lòng thử lại!',
                'error' => 'error'
            ];
        }

        //check exits in table product
        $result = Model\Product::get([
            'brand_id' => $id,
            'not_status' => Model\Product::PRODUCT_STATUS_REMOVE,
            'limit' => 1
        ]);

        if(!empty($result['rows'])){
            return [
                'st' => -1,
                'ms' => 'Nhiều loại thuốc đã sử dụng nhãn hiệu này! Không thể xóa!',
                'error' => 'error'
            ];
        }

        //delete
        $status = Model\Brand::update([
            'status' => Model\Brand::BRAND_STATUS_REMOVE,
            'updated_date' => time(),
            'user_updated' => USER_ID
        ],$id);

        if(!$status){
            return [
                'st' => -1,
                'ms' => 'Xảy ra lỗi trong quá trình xử lý! Vui lòng thử lại!',
                'error' => 'error'
            ];
        }

        return [
            'st' => 1,
            'ms' => 'Xóa nhãn hiệu thành công thành công!',
            'success' => 'success'
        ];
    }
}