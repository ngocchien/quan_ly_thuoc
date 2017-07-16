<?php
namespace APP\DAO;

use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\ResultSet\ResultSet;
use APP\Database;

abstract class AbstractDAO
{
    protected static function getInstance(){
        $adapter = Database::getInstance('mysql');
        return $adapter;
    }

    protected static function _transform($result)
    {
        $rows = array();
        if ($result instanceof ResultInterface && $result->isQueryResult()) {
            $resultSet = new ResultSet;
            $resultSet->initialize($result);

            $rows = $resultSet->toArray();

            if(!empty($data)){
                foreach($data as &$value){
                    $value = array_change_key_case($value, CASE_LOWER);
                }
            }
            unset($resultSet);
        }
        return $rows;
    }
}