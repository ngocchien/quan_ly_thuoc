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


class Group extends AbstractDAO
{
    const TABLE_NAME  = 'tbl_groups';
    const PRIMARY_KEY = 'group_id';

	public static function get($params){
		try {

            $params = array_merge([
                'limit' => 10,
                'offset' => 0,
                'order' => 'group_id DESC'
            ],$params);

            $adapter = self::getInstance();
			$sql = new Sql($adapter);
			$strWhere = self::buildWhere($params);

			$select = $sql->select()
                            ->from(self::TABLE_NAME)
                            ->order($params['order'] ? $params['order'] : 'group_id DESC')
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

    public static function buildWhere($params){
        $strWhere = '1=1';

        if(!empty($params['group_id'])){
            $strWhere .= ' AND group_id = '. $params['group_id'];
        }

        if(!empty($params['not_group_id'])){
            $strWhere .= ' AND group_id != '. $params['not_group_id'];
        }

        if(!empty($params['group_name'])){
            $strWhere .= ' AND group_name = "'.$params['group_name'] . '"';
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
                $strWhere .= ' AND group_id = '.$params['search'];
            }else{
                $strWhere .= ' AND (group_name like "%'.$params['search'].'%")';
            }

        }

        return $strWhere;
    }

}