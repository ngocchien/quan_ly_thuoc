<?php
/**
 * Created by PhpStorm.
 * User: chiennn
 * Date: 27/06/2017
 * Time: 22:41
 */

namespace APP\Business;

use APP\Model;
use APP\Utils;

class Properties
{
    public static function create($params)
    {
        if (empty($params['properties_name'])) {
            $params['error'] = 'Vui lòng đầy đủ thông tin';
            return $params;
        }

        $properties_name = trim(strip_tags($params['properties_name']));
        $status = 1;

        //check user name is exist DB
        $result = Model\Properties::get([
            'properties_name' => $properties_name,
            'not_status' => Model\Properties::PROPERTIES_STATUS_REMOVE,
            'limit' => 1,
            'offset' => 0
        ]);

        if (!empty($result['rows'])) {
            $params['error'] = 'Thuộc tính này đã tồn tại!';
            return $params;
        }

        $id = Model\Properties::create([
            'properties_name' => $properties_name,
            'slug' => Utils::getSlug($properties_name),
            'user_created' => USER_ID,
            'created_date' => time(),
            'status' => $status
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
        $limit = empty($params['limit']) ? 10 : (int) $params['limit'];
        $page = empty($params['page']) ? 1 : (int)$params['page'];
        $offset = $limit * ($page - 1);
        $params['offset'] = $offset;
        $params['limit'] = $limit;
        $params['order'] = 'id DESC';
        $result = Model\Properties::get($params);
        return $result;
    }

    public static function update($params)
    {
        if (empty($params['properties_name'])) {
            $params['error'] = 'Vui lòng đầy đủ thông tin';
            return $params;
        }

        $properties_name = trim(strip_tags($params['properties_name']));
        $status = 1;
        $id = $params['id'];

        //check user name is exist DB
        $result = Model\Properties::get([
            'properties_name' => $properties_name,
            'not_status' => Model\Properties::PROPERTIES_STATUS_REMOVE,
            'limit' => 1,
            'offset' => 0,
            'not_id' => $id
        ]);

        if (!empty($result['rows'])) {
            $params['error'] = 'Thuộc tính này đã tồn tại!';
            return $params;
        }

        $updated = Model\Properties::update([
            'properties_name' => $properties_name,
            'slug' => Utils::getSlug($properties_name),
            'user_updated' => USER_ID,
            'updated_date' => time(),
            'status' => $status
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
        if(empty($params['id'])){
            return [
                'st' => -1,
                'ms' => 'Xảy ra lỗi ! Vui lòng thử lại!',
                'error' => 'error'
            ];
        }

        $id = $params['id'];

        //get info product
        $result = Model\Properties::get([
            'id' => $id,
            'not_status' => Model\Properties::PROPERTIES_STATUS_REMOVE
        ]);

        if(empty($result['rows'])){
            return [
                'st' => -1,
                'ms' => 'Xảy ra lỗi ! Vui lòng thử lại!',
                'error' => 'error'
            ];
        }

        //check kiem tra thuoc tinh da su dung de nhap hang cho thuoc nao chua
        $result = Model\Warehouse::get([
            'properties_id' => $id,
            'not_status' => Model\Warehouse::STATUS_REMOVE,
            'limit' => 1
        ]);

        if(!empty($result['rows'])){
            return [
                'st' => -1,
                'ms' => 'Thuộc tính này đã dùng nhập kho cho nhiều loại thuốc, không thể xóa!',
                'error' => 'error'
            ];
        }

        //delete
        $status = Model\Properties::update([
            'status' => Model\Properties::PROPERTIES_STATUS_REMOVE,
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
            'ms' => 'Xóa thuộc tính thành công thành công!',
            'success' => 'success'
        ];
    }
}