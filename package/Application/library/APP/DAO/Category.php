<?php
/**
 * Created by PhpStorm.
 * User: tuandv
 * Date: 8/2/16
 * Time: 21:37
 */

namespace APP\DAO;

use Zend\Db\Sql\Sql;

class Category extends AbstractDAO
{
    const TABLE_NAME  = 'tbl_categories';
    const PRIMARY_KEY = 'cate_id';

	public static function get($params){
		try {
		    $params = array_merge([
		        'limit' => 10,
                'offset' => 0,
                'order' => 'cate_id DESC'
            ],$params);
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

    public static function updateTreeCategory($params){
        try {
            //get info parent
            $parent_id = $params['parent_id'];
            $cate_id = $params['cate_id'];

            //get info parent
            $parent_info = [];
            if($parent_id){
                $result = self::get([
                    'cate_id' => $parent_id
                ]);
                $parent_info = $result['rows'][0];
            }

            //get current info
            $result = self::get([
                'cate_id' => $cate_id
            ]);
            $cate_info = $result['rows'][0];
            $old_sort = $cate_info['full_sort'];

            $new_sort = sprintf('%04d', $cate_info['sort']) . ':' . sprintf('%04d', $cate_id) . ':';

            if(!empty($parent_info)){
                $new_sort = $parent_info['full_sort'].$new_sort;
            }

            $adapter = self::getInstance();
            $query = "update " . self::TABLE_NAME . " set full_sort = REPLACE(full_sort,'" . $old_sort . "','" . $new_sort . "'), updated_date = ".time()." WHERE full_sort LIKE '" . $old_sort . "%'";
            $statement = $adapter->query($query);
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

        if(!empty($params['cate_id'])){
            $strWhere .= ' AND cate_id = '. $params['cate_id'];
        }

        if(isset($params['parent_id'])){
            $strWhere .= ' AND parent_id = '. $params['parent_id'];
        }

        if(!empty($params['not_cate_id'])){
            $strWhere .= ' AND cate_id != '. $params['not_cate_id'];
        }

        if(!empty($params['cate_name'])){
            $strWhere .= ' AND cate_name = "'.$params['cate_name'] . '"';
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
                $strWhere .= ' AND cate_id = '.$params['search'];
            }else{
                $strWhere .= ' AND (cate_name like "%'.$params['search'].'%")';
            }
        }

        if(isset($params['not_like_full_sort'])){
            $strWhere .= ' AND full_sort NOT LIKE "'.$params['not_like_full_sort'].'%"';
        }

        if(!empty($params['in_cate_id'])){
            $strWhere .= ' AND cate_id IN ('. implode(',',$params['in_cate_id']).')';
        }

        if(!empty($params['cate_slug'])){
            $strWhere .= ' AND cate_slug = "'. $params['cate_slug'].'"';
        }

        return $strWhere;
    }

}