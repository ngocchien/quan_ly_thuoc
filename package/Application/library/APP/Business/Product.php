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

class Product
{
    public static function create($params){

        if(empty($params['product_name'])){
            $params['error'] = 'Tên sản phẩm không được bỏ trống!';
            return $params;
        }

        if(empty($params['description'])){
            $params['error'] = 'Nội dung sản phẩm không được bỏ trống!';
            return $params;
        }

        if(empty($params['description'])){
            $params['error'] = 'Nội dung sản phẩm không được bỏ trống!';
            return $params;
        }

        if(empty($params['cate_id'])){
            $params['error'] = 'Vui lòng chọn danh mục sản phẩm!';
            return $params;
        }

        if(empty($params['brand_id'])){
            $params['error'] = 'Vui lòng chọn nhãn hiệu cho sản phẩm!';
            return $params;
        }

        $product_name =  trim(strip_tags($params['product_name']));
        $description = $params['description'];
        $cate_id = $params['cate_id'];
        $brand_id = $params['brand_id'];
        $product_code = $params['product_code'];
        $price = empty($params['price']) ? 0 : $params['price'];
        $price_cost = empty($params['price_cost']) ? 0 : $params['price_cost'];
        $status = 1;
        $images = empty($params['fid']) ? '': implode(',',$params['fid']);
        $meta_title = empty($params['meta_title']) ? : $params['meta_title'];
        $meta_description = empty($params['meta_description']) ? : $params['meta_description'];
        $meta_keyword = empty($params['meta_keyword']) ? : $params['meta_keyword'];

        //check name
        $exist = Model\Product::get([
            'product_name' => $product_name,
            'limit' => 1,
            'offset' => 0,
            'not_status' => Model\Product::PRODUCT_STATUS_REMOVE,
            'cate_id' => $cate_id,
            'brand_id' => $brand_id
        ]);

        if(!empty($exist['rows'])){
            $params['error'] = 'Tên sản phẩm này đã tồn tại trong danh mục này!';
            return $params;
        }

        $product_id = Model\Product::create([
            'product_name' => $product_name,
            'product_slug' => Utils::getSlug($product_name),
            'status' => $status,
            'created_date' => time(),
            'user_created' => USER_ID,
            'cate_id' => $cate_id,
            'images'=> $images,
            'product_code' => $product_code,
            'price_cost' => $price_cost,
            'price' => $price,
            'description' => $description,
            'meta_keyword' => $meta_keyword,
            'meta_description' => $meta_description,
            'meta_title' => $meta_title,
            'brand_id' => $brand_id
        ]);

        if(!$product_id){
            $params['error'] = 'Xảy ra lỗi trong quá trình xử lý! Thử lại sau giây lát';
            return $params;
        }

        return [
            'success' => true,
            'product_id' => $product_id
        ];
    }

    public static function get($params){
        $limit = empty($params['limit']) ? 10 : (int)$params['limit'];
        $page = empty($params['page']) ? 1 : (int)$params['page'];
        $offset = $limit * ($page - 1);
        $params['offset'] = $offset;
        $params['limit'] = $limit;
        $result = Model\Product::get($params);
        return $result;
    }

    public static function update($params){
        if(empty($params['product_name'])){
            $params['error'] = 'Tên sản phẩm không được bỏ trống!';
            return $params;
        }

        if(empty($params['description'])){
            $params['error'] = 'Nội dung sản phẩm không được bỏ trống!';
            return $params;
        }

        if(empty($params['cate_id'])){
            $params['error'] = 'Vui lòng chọn danh mục sản phẩm!';
            return $params;
        }

        if(empty($params['brand_id'])){
            $params['error'] = 'Vui lòng chọn nhãn hiệu cho sản phẩm!';
            return $params;
        }

        $product_name =  trim(strip_tags($params['product_name']));
        $description = $params['description'];
        $cate_id = $params['cate_id'];
        $product_code = $params['product_code'];
        $price = empty($params['price']) ? 0 : $params['price'];
        $price_cost = empty($params['price_cost']) ? 0 : $params['price_cost'];
        $status = 1;
        $images = empty($params['fid']) ? '': implode(',',$params['fid']);
        $meta_title = empty($params['meta_title']) ? : $params['meta_title'];
        $meta_description = empty($params['meta_description']) ? : $params['meta_description'];
        $meta_keyword = empty($params['meta_keyword']) ? : $params['meta_keyword'];
        $product_id = $params['product_id'];
        $brand_id = $params['brand_id'];

        //check name
        $exist = Model\Product::get([
            'product_name' => $product_name,
            'limit' => 1,
            'offset' => 0,
            'not_status' => Model\Product::PRODUCT_STATUS_REMOVE,
            'cate_id' => $cate_id,
            'not_product_id' => $product_id,
            'not_brand_id' => $brand_id
        ]);

        if(!empty($exist['rows'])){
            $params['error'] = 'Tên sản phẩm này đã tồn tại trong danh mục này!';
            return $params;
        }

        //update group
        $updated = Model\Product::update([
            'product_name' => $product_name,
            'product_slug' => Utils::getSlug($product_name),
            'status' => $status,
            'updated_date' => time(),
            'user_updated' => USER_ID,
            'cate_id' => $cate_id,
            'images'=> $images,
            'product_code' => $product_code,
            'price_cost' => $price_cost,
            'price' => $price,
            'description' => $description,
            'meta_keyword' => $meta_keyword,
            'meta_description' => $meta_description,
            'meta_title' => $meta_title,
            'brand_id' => $brand_id
        ],$product_id);

        if(!$updated){
            $params['error'] = 'Xảy ra lỗi trong quá trình xử lý! Thử lại sau giây lát';
            return $params;
        }

        return [
            'success' => true
        ];
    }

    public static function delete($params){

        if(empty($params['arr_product_id'])){
            return [
                'st' => -1,
                'ms' => 'Xảy ra lỗi ! Vui lòng thử lại!',
                'error' => 'error'
            ];
        }

        $arr_product_id = $params['arr_product_id'];

        //get info product
        $products = Model\Product::get([
            'in_product_id' => $arr_product_id,
            'not_status' => Model\Product::PRODUCT_STATUS_REMOVE
        ]);

        if(empty($products['rows'])){
            return [
                'st' => -1,
                'ms' => 'Xảy ra lỗi ! Vui lòng thử lại!',
                'error' => 'error'
            ];
        }


        //delete
        $status = Model\Product::updateByCondition([
            'status' => Model\Product::PRODUCT_STATUS_REMOVE,
            'updated_date' => time(),
            'user_updated' => USER_ID
        ],[
            'in_product_id' => $arr_product_id
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
            'ms' => 'Xóa sản phẩm thành công!',
            'success' => 'success'
        ];
    }

	public static function getTags($params){
//		$search = isset($params['search']) ? $params['search'] : '';
//		$selected_id = isset($params['selected_id']) ? $params['selected_id'] : array();

		/*$data = Model\ElasticSearch::search(array(
			'object_name' => 'tags',
			'search' => $search,
			'selected_id' => $selected_id,
			'limit' => 50,
			'offset' => 0
		));*/

		//transform
//		$result = array();
//		if(isset($data['rows']) && !empty($data['rows'])){
//			foreach($data['rows'] as $row){
//				if(isset($row['tag_id'])){
//					$result[] = array(
//						'id' => $row['tag_id'],
//						'name' => $row['tag_name']
//					);
//				}
//			}
//		}
//
//		return array(
//			'rows' => $result,
//			'total' => isset($data['total']) ? $data['total'] : 0
//		);
	}
}