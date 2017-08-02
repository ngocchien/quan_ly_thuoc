<?php
/**
 * Created by PhpStorm.
 * User: chiennn
 * Date: 26/07/2017
 * Time: 09:17
 */

namespace APP\Business;

use APP\Model;
use APP\Utils;

class Invoice
{
    public static function create($params){
        $error = false;
        $arr_warehouse_id = [];
        if(empty($params['warehouse_id'])){
            $error = true;
            $params['messages'][] = 'Vui lòng chọn thuốc!';
        }else{
            $arr_warehouse_id = $params['warehouse_id'];
        }

        $arr_quantity = [];
        $params['quantity'] = array_unique($params['quantity']);
        if(empty($params['quantity'])){
            $error = true;
            $params['messages'][] = 'Vui lòng nhập đủ số lượng ở tất cả cả các thuốc!';
        }else{
            if(count($arr_warehouse_id) != count($params['quantity'])){
                $error = true;
                $params['messages'][] = 'Vui lòng nhập đủ số lượng ở tất cả cả các thuốc!';
            }else{
                $arr_quantity = $params['quantity'];
                foreach ($arr_quantity as $item){
                    if(!$item){
                        $error = true;
                        $params['messages'][] = 'Vui lòng nhập đủ số lượng ở tất cả cả các thuốc!';
                        break;
                    }
                }
            }
        }

        if(empty($params['customer_id'])){
            $error = true;
            $params['messages'][] = 'Vui lòng chọn khách hàng!';
        }

        if($error){
            $params['error'] = true;
            return $params;
        }

        $arr_discount = empty($params['discount']) ? [] : $params['discount'];
        $arr_price = empty($params['price']) ? [] : $params['price'];
        $arr_total_price = empty($params['total_price']) ? [] : $params['total_price'];
        $customer_id = empty($params['customer_id']) ? '' : (int) $params['customer_id'];
        $sum_total_price = empty($params['sum_total_price']) ? 0 : (int)$params['sum_total_price'];

        //check quantity stock in warehouse
        $warehouses = Model\Warehouse::get([
            'in_warehouse_id' => $arr_warehouse_id
        ]);

        if(!$warehouses['total']){
            $params['error'] = true;
            $params['messages'][] = 'Chọn thuốc không hợp lệ!';
            return $params;
        }

        $arr_not_available = [];
        foreach ($warehouses['rows'] as $row){
            $key = array_search($row['warehouse_id'], $arr_warehouse_id);
            if($arr_quantity[$key] > $row['stock']){
                $arr_not_available = [$row['product_id']];
            }
        }

        if(!empty($arr_not_available)){
            $result = Model\Product::get([
                'in_product_id' => $arr_not_available,
                'limit' => 1000
            ]);

            foreach ($result['rows'] as $row){
                $params['messages'][] = 'Số lượng thuốc :'. $row['product_name']. ' còn lại trong kho không đáp ứng đủ!';
            }
            $params['error'] = true;
            return $params;
        }

        //add to table Invoice
        $invoice_id = Model\Invoice::create([
            'user_created' => USER_ID,
            'created_date' => time(),
            'customer_id' => $customer_id,
            'total_price' => $sum_total_price,
            'status' => Model\Invoice::STATUS_ACTIVE,
            'logs' => json_encode($params)
        ]);

        if(!$invoice_id){
            $params['error'] = true;
            $params['messages'] = 'Xảy ra lỗi trong quá trình xử lý! Vui lòng thử lại sau giây lát!';
            return $params;
        }

        $data_log = [];
        $data_log['Params'] = $params;
        $data_log['Invoice Id'] = $invoice_id;

        //insert to invoice warehouse
        $is_process = true;
        $arr_invoice_warehouse = [];
        for ($i=0;$i<=100; $i++){
            if(empty($arr_warehouse_id[$i])){
                break;
            }

            $id_detail = Model\InvoiceWarehouse::create([
                'invoice_id' => $invoice_id,
                'warehouse_id' => $arr_warehouse_id[$i],
                'quantity' => $arr_quantity[$i],
                'discount' => empty($arr_discount[$i]) ? 0 : $arr_discount[$i],
                'price' => empty($arr_price[$i]) ? 0 : $arr_price[$i],
                'total_price' => empty($arr_total_price[$i]) ? 0 : $arr_total_price[$i]
            ]);

            if(!$id_detail){
                //insert error => delete invoice in table invoice
                Model\Invoice::update([
                    'status' => Model\Invoice::STATUS_REMOVE,
                    'updated_date' => time(),
                    'user_updated' => USER_ID,
                    'note' => 'Insert to table invoice warehouse error'
                ], $invoice_id);
                $is_process = false;
                break;
            }

            $arr_invoice_warehouse[] = $id_detail;
        }

        if(!$is_process){
            $params['error'] = true;
            $params['messages'] = 'Xảy ra lỗi trong quá trình xử lý! Vui lòng thử lại sau giây lát!';
            return $params;
        }

        $data_log['Invoice Warehouse Id'] = $arr_invoice_warehouse;

        //update stock in warehouse
        $log_stock = [];
        foreach ($warehouses['rows'] as $row){
            $key = array_search($row['warehouse_id'], $arr_warehouse_id);
            Model\Warehouse::update([
                'stock' => $row['stock'] - $arr_quantity[$key],
                'user_updated' => USER_ID,
                'updated_date' => time()
            ], $row['warehouse_id']);

            $log_stock[] = [
                'warehouse_id' => $row['warehouse_id'],
                'from_stock' => $row['stock'],
                'input_quantity' => $arr_quantity[$key],
                'to_stock' => $row['stock'] - $arr_quantity[$key]
            ];
        }

        $data_log['Log_Stock'] = $log_stock;
        Utils::writeLog('Create_Invoice_Success_Action' , $data_log);

        return [
            'success' => true,
            'id' => $invoice_id
        ];
    }

    public static function get($params){
        $limit = empty($params['limit']) ? 10 : (int)$params['limit'];
        $page = empty($params['page']) ? 1 : (int)$params['page'];
        $offset = $limit * ($page - 1);
        $params['offset'] = $offset;
        $params['limit'] = $limit;
        $result = Model\Invoice::get($params);

        if(!empty($result['rows'])){
            $arr_customer_id = [];
            foreach ($result['rows'] as $row){
                $arr_customer_id[] = $row['customer_id'];
            }

            $result_customer = Model\Customer::get([
                'in_customer_id' => array_values($arr_customer_id),
                'limit' => 10000
            ]);

            $arr_customer_format = [];
            foreach ($result_customer['rows'] as $row){
                $arr_customer_format[$row['customer_id']] = $row;
            }

            foreach ($result['rows'] as &$row){
                $row['customer_info'] = $arr_customer_format[$row['customer_id']];
            }
        }
        return $result;
    }
}