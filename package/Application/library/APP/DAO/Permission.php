<?php
/**
 * Created by PhpStorm.
 * User: tuandv
 * Date: 8/2/16
 * Time: 21:37
 */

namespace APP\DAO;

use APP\Database;
use Zend\Db\Sql\Sql;
use Zend\Validator\Digits;


class Permission extends AbstractDAO
{
    const TABLE_NAME  = 'tbl_permission';
    const PRIMARY_KEY = 'perm_id';

	public static function get($params){
		try {
            $params = array_merge([
                'limit' => 10,
                'offset' => 0
            ],$params);

            $adapter = self::getInstance();
			$sql = new Sql($adapter);
			$strWhere = self::buildWhere($params);

			$select = $sql->select()
                            ->from(self::TABLE_NAME)
                            ->order(empty($params['order']) ? 'perm_id DESC' : $params['order'])
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
            if(APPLICATION_ENV != 'production'){
                echo '<pre>';
                print_r($e->getMessage(), $e->getCode());
                echo '</pre>';
                die();
            }
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
                print_r($e->getMessage(), $e->getCode());
                echo '</pre>';
                die();
            }
			throw new \Exception($e->getMessage(), $e->getCode());
		}
	}

    public static function buildWhere($params){
        $strWhere = '1=1';

        if(!empty($params['user_id'])){
            $strWhere .= ' AND user_id = '. $params['user_id'];
        }

        if(!empty($params['not_user_id'])){
            $strWhere .= ' AND user_id != '. $params['not_user_id'];
        }

        if(isset($params['status'])){
            $strWhere .= ' AND status = '.$params['status'];
        }

        if(isset($params['not_status'])){
            $strWhere .= ' AND status != '.$params['not_status'];
        }

        if(isset($params['group_id'])){
            $strWhere .= ' AND group_id = '.$params['group_id'];
        }

        if(isset($params['not_group_id'])){
            $strWhere .= ' AND group_id = '.$params['not_group_id'];
        }

        if(!empty($params['perm_id'])){
            $strWhere .= ' AND perm_id = '. $params['perm_id'];
        }

        if(!empty($params['not_perm_id'])){
            $strWhere .= ' AND perm_id = '. $params['not_perm_id'];
        }

        if (isset($params['or_group_id']) && isset($params['or_user_id'])) {
            $strWhere .= ' AND ( group_id = ' . $params['or_group_id'] . ' OR user_id = ' . $params['or_user_id'] . ')';
        }

        return $strWhere;
    }

}