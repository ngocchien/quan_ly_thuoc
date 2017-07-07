<?php
/**
 * Created by PhpStorm.
 * User: tuandv
 * Date: 8/2/16
 * Time: 21:37
 */

namespace APP\DAO;

use Zend\Db\Sql\Sql;


class Product extends AbstractDAO
{
    const TABLE_NAME  = 'tbl_products';
    const PRIMARY_KEY = 'product_id';

	public static function get($params){
		try {
		    $params = array_merge([
		        'limit' => 10,
                'offset' => 0,
                'order' => 'product_id DESC'
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
		    echo '<pre>';
		    print_r($e->getMessage(), $e->getCode());
		    echo '</pre>';
		    die();
			throw new \Exception($e->getMessage(), $e->getCode());
		}
	}

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
                print_r([
                    $e->getMessage(), $e->getCode()
                ]);
                echo '</pre>';
                die();
            }
			throw new \Exception($e->getMessage(), $e->getCode());
		}
	}

    public static function buildWhere($params){
        $strWhere = '1=1';

        if(!empty($params['product_id'])){
            $strWhere .= ' AND product_id = '. $params['product_id'];
        }

        if(!empty($params['cate_id'])){
            $strWhere .= ' AND cate_id = '. $params['cate_id'];
        }

        if(!empty($params['not_product_id'])){
            $strWhere .= ' AND product_id != '. $params['not_product_id'];
        }

        if(!empty($params['product_name'])){
            $strWhere .= ' AND product_name = "'.$params['product_name'] . '"';
        }

        if(isset($params['status'])){
            $strWhere .= ' AND status = '.$params['status'];
        }

        if(isset($params['not_status'])){
            $strWhere .= ' AND status != '.$params['not_status'];
        }

        if(isset($params['not_cate_id'])){
            $strWhere .= ' AND cate_id != '.$params['not_cate_id'];
        }

        if(!empty($params['search'])){
            $params['search'] = trim(strip_tags($params['search']));
            if(is_numeric($params['search'])){
                $strWhere .= ' AND product_id = '.$params['search'];
            }else{
                $strWhere .= ' AND (product_name like "%'.$params['search'].'%")';
            }
        }

        if(!empty($params['in_product_id'])){
            $strWhere .= ' AND product_id IN ('. implode(',',$params['in_product_id']).')';
        }

        return $strWhere;
    }

}