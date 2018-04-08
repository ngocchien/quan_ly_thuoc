<?php
/**
 * Created by PhpStorm.
 * User: chiennn
 * Date: 17/06/2017
 * Time: 15:55
 */

namespace APP\DAO;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Expression;
use APP\Model;
use APP\Exception;

class Warehouse extends AbstractDAO
{
    const TABLE_NAME  = 'tbl_warehouse';
    const PRIMARY_KEY = 'warehouse_id';

    public static function create($params){
        try {
            $adapter = self::getInstance();

            $sql = new Sql($adapter, self::TABLE_NAME);

            $insert = $sql->insert();
            $insert->values($params);

            $statement = $sql->prepareStatementForSqlObject($insert);

            $statement->execute();

            $result = $adapter->getDriver()->getLastGeneratedValue();

            return $result;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), $e->getCode());
        }
    }

    public static function get($params){
        try {
            $params = array_merge([
                'limit' => 10,
                'offset' => 0,
                'order' => 'warehouse_id DESC'
            ], $params);

            $adapter = self::getInstance();
            $sql = new Sql($adapter);
            $strWhere = self::buildWhere($params);
            $select = $sql->select()
                ->from(self::TABLE_NAME)
                ->order($params['order'])
                ->limit($params['limit'])
                ->offset($params['offset'])
                ->where($strWhere);
            $statement = $sql->prepareStatementForSqlObject($select);
            $result = $statement->execute();

            $arr_result = [
                'total' => 0,
                'rows' => self::_transform($result)
            ];
            unset($result);

            //get count
            $select = $sql->select()
                ->from(self::TABLE_NAME)
                ->columns(array('total' => new Expression('COUNT(*)')))
                ->where($strWhere);
            $statement = $sql->prepareStatementForSqlObject($select);
            $result = $statement->execute();

            $arr_result['total'] = $result->current()['total'];

            unset($result);
            return $arr_result;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), $e->getCode());
        }
    }

    public static function getExpire($params){
        try {
            $params = array_merge([
                'limit' => 10,
                'offset' => 0,
                'order' => 'warehouse_id DESC'
            ], $params);

            $adapter = self::getInstance();
            $sql = new Sql($adapter);
            $strWhere = self::buildWhere($params);

            $select = $sql->select()
                ->from(self::TABLE_NAME)
                ->order($params['order'])
                ->limit($params['limit'])
                ->offset($params['offset'])
                ->where($strWhere);
            $statement = $sql->prepareStatementForSqlObject($select);
            $result = $statement->execute();

            return self::_transform($result);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), $e->getCode());
        }
    }

    public static function update($params, $id){
        try {
            $adapter = self::getInstance();

            $sql = new Sql($adapter, self::TABLE_NAME);

            $update = $sql->update();

            $update->set($params);

            $update->where(array(self::PRIMARY_KEY => $id));

            $statement = $sql->prepareStatementForSqlObject($update);

            $result = $statement->execute();

            if(!$result->getAffectedRows()){
                return false;
            }
            return true;
        } catch (\Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    public static function updateByCondition($params, $condition){
        try {
            $adapter = self::getInstance();

            $sql = new Sql($adapter, self::TABLE_NAME);

            $update = $sql->update();

            $update->set($params);

            $strWhere = self::buildWhere($condition);

            $update->where($strWhere);

            $statement = $sql->prepareStatementForSqlObject($update);

            $result = $statement->execute();

            if(!$result->getAffectedRows()){
                return false;
            }
            return true;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), $e->getCode());
        }
    }

    public static function buildWhere($params){
        $strWhere = '1=1';

        if(!empty($params['warehouse_id'])){
            $strWhere .= ' AND warehouse_id = '. $params['warehouse_id'];
        }

        if(!empty($params['in_warehouse_id'])){
            $strWhere .= ' AND warehouse_id IN ('. implode(',',$params['in_warehouse_id']).')';
        }

        if(!empty($params['not_in_warehouse_id'])){
            $strWhere .= ' AND warehouse_id NOT IN ('. implode(',',$params['not_in_warehouse_id']).')';
        }

        if(!empty($params['not_warehouse_id'])){
            $strWhere .= ' AND warehouse_id != '. $params['not_warehouse_id'];
        }

        if(isset($params['status'])){
            $strWhere .= ' AND status = '.$params['status'];
        }

        if(isset($params['not_status'])){
            $strWhere .= ' AND status != '.$params['not_status'];
        }

        if(isset($params['properties_id'])){
            $strWhere .= ' AND properties_id = '.$params['properties_id'];
        }

        if(!empty($params['search'])){
            $params['search'] = trim(strip_tags($params['search']));
            $result = Model\Product::get([
                'limit' => 10000,
                'search' => $params['search']
            ]);
            if(!empty($result['rows'])){
                $arr_product_id = [];
                foreach ($result['rows'] as $row){
                    $arr_product_id[] = $row['product_id'];
                }
                $strWhere .= ' AND product_id IN ('. implode(',',$arr_product_id).')';
            }
        }

        if(isset($params['lte_stock'])){
            $strWhere .= ' AND stock >= '.$params['lte_stock'];
        }

        if(isset($params['lt_stock'])){
            $strWhere .= ' AND stock > '.$params['lt_stock'];
        }

        if(isset($params['gt_stock'])){
            $strWhere .= ' AND stock > '.$params['gt_stock'];
        }

//        if(isset($params['expire'])){
//            $strWhere .= ' AND expire = '.$params['expire'];
//        }

        if(isset($params['hsd'])){
            $strWhere .= ' AND expire = '.$params['expire'];
        }

        if(isset($params['gt_hsd'])){
            $strWhere .= ' AND hsd > '.$params['gt_hsd'];
        }

        if(isset($params['gte_hsd'])){
            $strWhere .= ' AND hsd >= '.$params['gte_hsd'];
        }

        if(isset($params['expire'])){
            $first_time_day = strtotime(date('Y-m-d'));
            $time_one_date = 60*60*24;
            $current_time = time();
            $strWhere .= ' AND hsd >= '.$first_time_day.' AND (hsd-'.$current_time.' <= flag_notify*'.$time_one_date.')';
        }

        if(isset($params['is_expired'])){
            $strWhere .= ' AND hsd < '.time();
        }

        if(isset($params['is_notify'])){
            $strWhere .= ' AND is_notify = '.$params['is_notify'];
        }

        return $strWhere;
    }

}