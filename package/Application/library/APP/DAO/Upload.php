<?php
/**
 * Created by PhpStorm.
 * User: tuandv
 * Date: 6/13/17
 * Time: 9:56 PM
 */

namespace APP\DAO;

use APP\Database;
use Zend\Db\Sql\Sql;
use Zend\Validator\Digits;


class Upload extends AbstractDAO
{
    const TABLE_NAME  = 'tbl_files';
    const PRIMARY_KEY = 'FID';

	public static function getAllFiles($params){
		try {
			$adapter = Database::getInstance('mysql');

			$sql = new Sql($adapter);

			$select = $sql->select();

			$select->from('tbl_files');

			$select->limit($params['limit']);

			$select->offset($params['offset']);

			$select->order('FID DESC');

			$statement = $sql->prepareStatementForSqlObject($select);

			$result = $statement->execute();

			return self::_transform($result);
		} catch (\Exception $e) {
			throw new \Exception($e->getMessage(), $e->getCode());
		}
	}

	public static function createFile($params){
		try {
			$params = array_merge(array(
				'name' => null,
				'file_name' => null,
				'file_size' => null,
				'parent_id' => null,
				'type' => null,
				'alt' => null,
				'ctime' => null,
				'src' => null
			), $params);

			$adapter = Database::getInstance('mysql');

			$sql = new Sql($adapter, 'tbl_files');

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
                'order' => 'FID DESC'
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

    public static function buildWhere($params){
        $strWhere = '1=1';

        if(!empty($params['FID'])){
            $strWhere .= ' AND FID = '. $params['FID'];
        }

        if(!empty($params['NOT_FID'])){
            $strWhere .= ' AND FID != '. $params['NOT_FID'];
        }

        if(!empty($params['IN_FID'])){
            $strWhere .= ' AND FID IN ('. implode(',',$params['IN_FID']).')';
        }

        return $strWhere;
    }
}