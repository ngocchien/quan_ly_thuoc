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


class User extends AbstractDAO
{
    const TABLE_NAME  = 'tbl_users';
    const PRIMARY_KEY = 'user_id';

	public static function get($params){
		try {
			$params = array_merge(array(
				'limit' => 10,
				'offset' => 0,
                'order' => 'user_id DESC'
			), $params);

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

	public static function updateUser($params, $id){
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

	public static function createUser($params){
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

	public static function deleteMovieTagByMovieId($params){
		try {
			$params = array_merge(array(
				'movie_id' => null
			), $params);

			$adapter = Database::getInstance('mysql');

			$sql = new Sql($adapter, 'movie_tag');

			$delete = $sql->delete();

			$delete->where(array('movie_id' => $params['movie_id']));

			$statement = $sql->prepareStatementForSqlObject($delete);

			return $statement->execute();
		} catch (\Exception $e) {
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

        if(!empty($params['user_name'])){
            $strWhere .= ' AND user_name = "'.$params['user_name'] . '"';
        }

        if(isset($params['status'])){
            $strWhere .= ' AND status = '.$params['status'];
        }

        if(isset($params['not_status'])){
            $strWhere .= ' AND status != '.$params['not_status'];
        }

        if(isset($params['user_id'])){
            $strWhere .= ' AND user_id = '.$params['user_id'];
        }

        if(isset($params['email'])){
            $strWhere .= ' AND email = "'.$params['email'].'"';
        }

        if(!empty($params['search'])){
            $params['search'] = trim(strip_tags($params['search']));
            if(is_numeric($params['search'])){
                $strWhere .= ' AND user_id = '.$params['search'];
            }else{
                $strWhere .= ' AND (user_name like "%'.$params['search'].'%" OR full_name like "%'.$params['search'].'%" OR email like "%'.$params['search'].'%")';
            }

        }

        if(!empty($params['in_user_id'])){
            $strWhere .= ' AND user_id IN ('. implode(',',$params['in_user_id']).')';
        }

        return $strWhere;
    }

}