<?php
/**
 * Created by PhpStorm.
 * User: chiennn
 * Date: 26/07/2017
 * Time: 09:17
 */

namespace APP\Business;

use APP\Model;

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
        if(empty($params['quantity'])){
            $error = true;
            $params['messages'][] = 'Vui lòng nhập đủ số lượng ở tất cả cả các thuốc!';
        }else{
            if(count($arr_warehouse_id) != count($params['quantity'])){
                $params['messages'][] = 'Vui lòng nhập đủ số lượng ở tất cả cả các thuốc!';
            }else{
                $arr_quantity = $params['quantity'];
            }
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

        //insert to invoice warehouse
        $is_process = true;
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
        }

        if(!$is_process){
            $params['error'] = true;
            $params['messages'] = 'Xảy ra lỗi trong quá trình xử lý! Vui lòng thử lại sau giây lát!';
            return $params;
        }

        return [
            'success' => true,
            'id' => $invoice_id
        ];
    }
}