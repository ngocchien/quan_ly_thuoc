<?php
/**
 * Created by PhpStorm.
 * User: chiennn
 * Date: 11/07/2017
 * Time: 23:08
 */

namespace APP\Business;

use APP\Model;
use APP\Utils;

class Country
{
    public static function create($params)
    {
        if (empty($params['country_name'])) {
            $params['error'] = 'Vui lòng đầy đủ thông tin';
            return $params;
        }

        $country_name = trim(strip_tags($params['country_name']));
        $status = 1;

        //check user name is exist DB
        $result = Model\Country::get([
            'country_name' => $country_name,
            'not_status' => Model\Country::STATUS_REMOVE,
            'limit' => 1,
            'offset' => 0
        ]);

        if (!empty($result['rows'])) {
            $params['error'] = 'Quốc gia này đã tồn tại trong hệ thống!';
            return $params;
        }

        $id = Model\Country::create([
            'country_name' => $country_name,
            'slug' => Utils::getSlug($country_name),
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
        $limit = empty($params['limit']) ? 100 : (int) $params['limit'];
        $page = empty($params['page']) ? 1 : (int)$params['page'];
        $offset = $limit * ($page - 1);
        $params['offset'] = $offset;
        $params['limit'] = $limit;
        $params['order'] = 'country_id DESC';
        $result = Model\Country::get($params);
        return $result;
    }
}