<?php
/**
 * Created by PhpStorm.
 * User: chiennn
 * Date: 07/07/2017
 * Time: 17:11
 */

namespace APP\Business;

use APP\Model;

class Customer
{
    public static function create($params)
    {
        $error = false;
        if(empty($params['full_name'])){
            $error = true;
            $params['messages'][] = 'Tên khách hàng không được bỏ trống!';
        }

        if($error){
            $params['error'] = true;
            return $params;
        }

        $full_name = trim(strip_tags($params['full_name']));
        $address = empty($params['customer_address']) ? '' : $params['customer_address'];
        $phone = empty($params['phone']) ? '' : $params['phone'];
        $note = empty($params['note']) ? '' : $params['note'];
        $status = Model\Customer::STATUS_ACTIVE;

        //check exits
        $result = Model\Customer::get([
            'full_name' => $full_name,
            'address' => $address,
            'phone' => $phone,
            'not_status' => Model\Customer::STATUS_REMOVE
        ]);

        if($result['total']){
            $params['error'] = true;
            $params['messages'][] = 'Khách hàng đã tồn tại trong hệ thống! Vui lòng kiểm tra lại';
            return $params;
        }

        $id = Model\Customer::create([
            'user_created' => USER_ID,
            'created_date' => time(),
            'status' => $status,
            'note' => $note,
            'full_name' => $full_name,
            'phone' => $phone,
            'address' => $address
        ]);

        if (!$id) {
            $params['error'] = true;
            $params['messages'][] = 'Xảy ra lỗi trong quá trình xử lý! Vui lòng thử lại sau giây lát!';
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
        $params['order'] = 'customer_id DESC';
        $result = Model\Customer::get($params);
        return $result;
    }

    public static function getListExpire($params){
        $limit = empty($params['limit']) ? 100 : (int) $params['limit'];
        $page = empty($params['page']) ? 1 : (int)$params['page'];
        $offset = $limit * ($page - 1);
        $params['offset'] = $offset;
        $params['limit'] = $limit;
        $params['order'] = 'hsd ASC';
        $params['lt_stock'] = 0;
        $result = Model\Warehouse::get($params);
        return $result;
    }

    public static function getListExpired($params){
        $limit = empty($params['limit']) ? 100 : (int) $params['limit'];
        $page = empty($params['page']) ? 1 : (int)$params['page'];
        $offset = $limit * ($page - 1);
        $params['offset'] = $offset;
        $params['limit'] = $limit;
        $params['order'] = 'hsd DESC';
        $params['lt_stock'] = 0;
        $params['is_expired'] = true;
        $params['lt_stock'] = 0;
        $result = Model\Warehouse::get($params);
        return $result;
    }

    public static function update($params)
    {
        $error = false;
        $nsx = '';
        if(empty($params['nsx'])){
            $error = true;
            $params['messages'][] = 'Ngày sản xuất không được bỏ trống!';
        }else{
            list($day,$month,$year) = explode('/', $params['nsx']);
            $nsx = mktime(0, 0, 0, $month, $day, $year);
            if($nsx > time()){
                $error = true;
                $params['messages'][] = 'Nhập ngày sản xuất không hợp lệ!';
            }
        }

        $hsd = '';
        if(empty($params['hsd'])){
            $error = true;
            $params['messages'][] = 'Hạn sử dụng không được bỏ trống!';
        }else{
            list($day,$month,$year) = explode('/', $params['hsd']);
            $hsd = mktime(23, 59, 59, $month, $day, $year);
        }

        if(empty($params['quantity'])){
            $error = true;
            $params['messages'][] = 'Số lượng không được bỏ trống!';
        }elseif ($params['quantity'] <= 0){
            $error = true;
            $params['messages'][] = 'Nhập số lượng không hợp lệ! Số lượng phải > 0!';
        }

        if(empty($params['flag_notify'])){
            $error = true;
            $params['messages'][] = 'Ngày bật thông báo hết hạn không được bỏ trống!';
        }elseif($params['flag_notify'] <= 0){
            $error = true;
            $params['messages'][] = 'Ngày bật thông báo hết hạn không hợp lệ! Ngày bật thông báo hết hạn phải > 0';
        }

        if(!isset($params['production_batch'])){
            $error = true;
            $params['messages'][] = 'Số lô sản xuất không được bỏ trống!';
        }

        if($error){
            $params['error'] = true;
            return $params;
        }
        $product_id = (int) $params['product_id'];
        $status = 1;
        $stock = $quantity = (int) $params['quantity'];
        $flag_notify = (int) $params['flag_notify'];
        $properties_id = $params['properties_id'];
        $production_batch = empty($params['production_batch']) ? '' : $params['production_batch'];
        $unit_price = isset($params['unit_price']) ? (int) $params['unit_price'] : 0 ;
        $total_price = isset($params['total_price']) ? (int) ($params['total_price']) : 0;
        $discount = isset($params['discount']) ? (float) ($params['discount']) : 0;
        $note = isset($params['note']) ? ($params['note']) : null;
        $warehouse_id = $params['warehouse_id'];

        //check thuoc da ban
        $invoice_warehouse = Model\InvoiceWarehouse::get([
            'not_status' => Model\InvoiceWarehouse::STATUS_REMOVE,
            'warehouse_id' => $warehouse_id,
            'limit' => 10000
        ]);

        if(!empty($invoice_warehouse['total'])){
            $total_quantity_in_invoice = 0;
            foreach ($invoice_warehouse['rows'] as $row){
                $total_quantity_in_invoice += (int) $row['quantity'];
            }

            if($total_quantity_in_invoice > $quantity){
                $params['error'] = true;
                $params['messages'][] = 'Số lượng thuốc này đã bán ra nhiều hơn số lượng cập nhật! Vui lòng kiểm tra lại!';
                return $params;
            }
            $stock = $quantity-$total_quantity_in_invoice;
        }

        $updated = Model\Warehouse::update([
            'user_created' => USER_ID,
            'created_date' => time(),
            'nsx' => $nsx,
            'status' => $status,
            'hsd' => $hsd,
            'product_id' => $product_id,
            'quantity' => $quantity,
            'flag_notify' => $flag_notify,
            'properties_id' => $properties_id,
            'production_batch' => $production_batch,
            'unit_price' => $unit_price,
            'total_price' => $total_price,
            'discount' => $discount,
            'note' => $note,
            'stock' => $stock
        ],$warehouse_id);

        if (!$updated) {
            $params['error'] = true;
            $params['messages'] = 'Xảy ra lỗi trong quá trình xử lý! Vui lòng thử lại sau giây lát!';
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

    public static function getProductStockInWarehouse($params){
        $params = array_merge([
            'gt_stock' => 0,
            'not_status' => Model\Warehouse::STATUS_REMOVE,
            'gt_hsd' => time(),
            'limit' => 10000
        ], $params);
        $result = Model\Warehouse::get($params);

        if(!empty($result['rows'])){
            $arr_product_id = $properties_id = $products_format = $properties_format = $arr_brand_id = $brand_format = [];
            foreach ($result['rows'] as $row){
                $arr_product_id[] = $row['product_id'];
                $properties_id[] = $row['properties_id'];
            }

            //get list product name
            $products = Model\Product::get([
                'in_product_id' => array_values($arr_product_id),
                'columns' => [
                    'product_id' , 'product_name' , 'brand_id'
                ],
                'limit' => 10000
            ]);

            foreach ($products['rows'] as $row){
                $products_format[$row['product_id']] = $row;
                if(!empty($row['brand_id'])){
                    $arr_brand_id[] = $row['brand_id'];
                }
            }

            $brands =  Model\Brand::get([
                'in_brand_id' => array_values($arr_brand_id),
                'columns' => [
                    'brand_id' , 'brand_name'
                ],
                'limit' => 10000
            ]);

            foreach ($brands['rows'] as $row){
                $brand_format[$row['brand_id']] = $row;
            }

            $properties = Model\Properties::get([
                'in_id' => array_values($properties_id),
                'columns' => [
                    'id' , 'properties_name'
                ],
                'limit' => 10000
            ]);

            foreach ($properties['rows'] as $row){
                $properties_format[$row['id']] = $row;
            }

            foreach ($result['rows'] as &$row){
                $row['product_name'] = $products_format[$row['product_id']]['product_name'];
                $row['properties_name'] = $properties_format[$row['properties_id']]['properties_name'];
                $row['brand_name'] = empty($products_format[$row['product_id']]['brand_id']) ? '' : $brand_format[$products_format[$row['product_id']]['brand_id']]['brand_name'];
            }
        }

        return $result;
    }

    public static function getWarehouseForInvoice($params){
        $condition = [
            'not_status' => Model\Warehouse::STATUS_REMOVE,
            'lt_stock' => 0,
            'lt_hsd' => time(),
            'limit' => 10000,
            'offset' => 0
        ];

        if(!empty($params['warehouse_id_selected']) && is_array($params['warehouse_id_selected'])){
            $condition['not_in_warehouse_id'] = $params['warehouse_id_selected'];
        }
        return Model\Warehouse::get($condition);

    }
}