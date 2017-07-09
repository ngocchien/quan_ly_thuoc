<?php
/**
 * Created by PhpStorm.
 * User: chiennn
 * Date: 17/06/2017
 * Time: 15:55
 */

namespace APP\DAO;
use Zend\Db\Sql\Sql;

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
            if(APPLICATION_ENV != 'production'){
                echo '<pre>';
                print_r($e->getMessage());
                echo '</pre>';
                die();
            }
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

            return self::_transform($result);
        } catch (\Exception $e) {
            if(APPLICATION_ENV != 'production'){
                echo '<pre>';
                print_r($e->getMessage(), $e->getCode());
                echo '</pre>';
                die();
            }
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
            if(APPLICATION_ENV != 'production'){
                echo '<pre>';
                print_r($e->getMessage(), $e->getCode());
                echo '</pre>';
                die();
            }
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
            echo '<pre>';
            print_r($e->getMessage(), $e->getCode());
            echo '</pre>';
            die();
            throw new \Exception($e->getMessage(), $e->getCode());
        }
    }

    public static function buildWhere($params){
        $strWhere = '1=1';

        if(!empty($params['warehouse_id'])){
            $strWhere .= ' AND warehouse_id = '. $params['warehouse_id'];
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

        if(!empty($params['search'])){
            $params['search'] = trim(strip_tags($params['search']));
            if(is_numeric($params['search'])){
                $strWhere .= ' AND warehouse_id = '.$params['search'];
            }else{
                $strWhere .= ' AND (banner_name like "%'.$params['search'].'%")';
            }
        }

        if(isset($params['expire'])){
            $time_one_date = 60*60*24;
            $current_time = time();
            $strWhere .= ' AND (hsd-'.$current_time.' <= flag_notify*'.$time_one_date.')';
        }

        if(isset($params['is_notify'])){
            $strWhere .= ' AND is_notify = '.$params['is_notify'];
        }

        return $strWhere;
    }

}