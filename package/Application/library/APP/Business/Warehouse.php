<?php
/**
 * Created by PhpStorm.
 * User: chiennn
 * Date: 07/07/2017
 * Time: 17:11
 */

namespace APP\Business;

use APP\Model;

class Warehouse
{
    public static function create($params)
    {
        if (empty($params['nsx']) || empty($params['hsd']) || empty($params['product_id']) || empty($params['quantity']) || empty($params['flag_notify'])) {
            $params['error'] = 'Vui lòng đầy đủ thông tin';
            return $params;
        }

        $product_id = (int) $params['product_id'];
        list($day,$month,$year) = explode('/', $params['nsx']);
        $nsx = mktime(0, 0, 0, $month, $day, $year);
        list($day,$month,$year) = explode('/', $params['hsd']);
        $hsd = mktime(0, 0, 0, $month, $day, $year);
        $status = 1;
        $quantity = (int) $params['quantity'];
        $flag_notify = (int) $params['flag_notify'];
        $properties_id = $params['properties_id'];
        $production_batch = empty($params['production_batch']) ? '' : $params['production_batch'];

        if($quantity < 0){
            $params['error'] = 'Nhập số lượng không hợp lệ';
            return $params;
        }

        if($hsd < time()){
            $params['error'] = 'Hạn sử dụng không hợp lệ';
            return $params;
        }

        if($flag_notify <= 0){
            $params['error'] = 'Nhập ngày thông báo hết hạn không hợp lệ';
            return $params;
        }

        $id = Model\Warehouse::create([
            'user_created' => USER_ID,
            'created_date' => time(),
            'nsx' => $nsx,
            'status' => $status,
            'hsd' => $hsd,
            'product_id' => $product_id,
            'quantity' => $quantity,
            'flag_notify' => $flag_notify,
            'properties_id' => $properties_id,
            'production_batch' => $production_batch
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
        $params['order'] = 'warehouse_id DESC';
        $result = Model\Warehouse::get($params);
        return $result;
    }

    public static function getListExpire($params){
        $limit = empty($params['limit']) ? 100 : (int) $params['limit'];
        $page = empty($params['page']) ? 1 : (int)$params['page'];
        $offset = $limit * ($page - 1);
        $params['offset'] = $offset;
        $params['limit'] = $limit;
        $params['order'] = 'hsd ASC';
        $result = Model\Warehouse::get($params);
        return $result;
    }

    public static function update($params)
    {
        if (empty($params['nsx']) || empty($params['hsd']) || empty($params['product_id']) || empty($params['quantity']) || empty($params['flag_notify'])) {
            $params['error'] = 'Vui lòng đầy đủ thông tin';
            return $params;
        }

        $product_id = (int) $params['product_id'];
        $status = 1;
        $quantity = (int) $params['quantity'];
        $warehouse_id = $params['warehouse_id'];
        $flag_notify = $params['flag_notify'];

        list($day,$month,$year) = explode('/', $params['nsx']);
        $nsx = mktime(0, 0, 0, $month, $day, $year);
        list($day,$month,$year) = explode('/', $params['hsd']);
        $hsd = mktime(0, 0, 0, $month, $day, $year);

        $production_batch = empty($params['production_batch']) ? '' : $params['production_batch'];

        if($quantity < 0){
            $params['error'] = 'Nhập số lượng không hợp lệ';
            return $params;
        }

        if($flag_notify <= 0){
            $params['error'] = 'Nhập ngày thông báo hết hạn không hợp lệ';
            return $params;
        }

        $updated = Model\Warehouse::update([
            'user_updated' => USER_ID,
            'updated_date' => time(),
            'nsx' => $nsx,
            'status' => $status,
            'hsd' => $hsd,
            'product_id' => $product_id,
            'quantity' => $quantity,
            'flag_notify' => $flag_notify,
            'production_batch' => $production_batch
        ], $warehouse_id);

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
        $result = Model\Warehouse::get([
            'warehouse_id' => $id,
            'not_status' => Model\Warehouse::STATUS_REMOVE
        ]);

        if(empty($result['rows'])){
            return [
                'st' => -1,
                'ms' => 'Xảy ra lỗi ! Vui lòng thử lại!',
                'error' => 'error'
            ];
        }

        //delete
        $status = Model\Warehouse::update([
            'status' => Model\Warehouse::STATUS_REMOVE,
            'updated_date' => time(),
            'user_updated' => USER_ID
        ],[
            $id
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
            'ms' => 'Xóa nhập hàng thành công!',
            'success' => 'success'
        ];
    }

    public static function deleteExpire($params){
        if(empty($params['id'])){
            return [
                'st' => -1,
                'ms' => 'Xảy ra lỗi ! Vui lòng thử lại!',
                'error' => 'error'
            ];
        }

        $id = $params['id'];

        //get info warehouse
        $result = Model\Warehouse::get([
            'warehouse_id' => $id,
            'not_status' => Model\Warehouse::STATUS_REMOVE,
            'is_notify' => Model\Warehouse::IS_NOTIFY
        ]);

        if(empty($result['rows'])){
            return [
                'st' => -1,
                'ms' => 'Xảy ra lỗi ! Vui lòng thử lại!',
                'error' => 'error'
            ];
        }

        //delete
        $status = Model\Warehouse::update([
            'updated_date' => time(),
            'user_updated' => USER_ID,
            'is_notify' => Model\Warehouse::UN_NOTIFY
        ],[
            $id
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
            'ms' => 'Ngừng nhận thông báo cho thuôc này thành công!',
            'success' => 'success'
        ];
    }
}