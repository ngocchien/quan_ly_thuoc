<?php
/**
 * Created by PhpStorm.
 * User: chiennn
 * Date: 27/06/2017
 * Time: 22:35
 */

namespace APP\DAO;

use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Expression;

class Properties extends AbstractDAO
{
    const TABLE_NAME  = 'tbl_properties';
    const PRIMARY_KEY = 'id';

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
                'order' => 'id DESC'
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
            if(APPLICATION_ENV != 'production'){
                echo '<pre>';
                print_r($e->getMessage(), $e->getCode());
                echo '</pre>';
                die();
            }
            throw new \Exception($e->getMessage(), $e->getCode());
        }
    }

    public static function buildWhere($params){
        $strWhere = '1=1';

        if(!empty($params['id'])){
            $strWhere .= ' AND id = '. $params['id'];
        }

        if(!empty($params['in_id'])){
            $strWhere .= ' AND id IN ('. implode(',',$params['in_id']).')';
        }

        if(!empty($params['not_id'])){
            $strWhere .= ' AND id != '. $params['not_id'];
        }

        if(!empty($params['properties_name'])){
            $strWhere .= ' AND properties_name = "'.$params['properties_name'] . '"';
        }

        if(isset($params['status'])){
            $strWhere .= ' AND status = '.$params['status'];
        }

        if(isset($params['not_status'])){
            $strWhere .= ' AND status != '.$params['not_status'];
        }

        if(isset($params['not_id'])){
            $strWhere .= ' AND id != '.$params['not_id'];
        }

        if(!empty($params['search'])){
            $params['search'] = trim(strip_tags($params['search']));
            $strWhere .= ' AND (properties_name like "%'.$params['search'].'%")';
        }

        return $strWhere;
    }
}