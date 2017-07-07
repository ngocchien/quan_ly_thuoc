<?php
/**
 * Created by PhpStorm.
 * User: tuandv
 * Date: 6/13/17
 * Time: 9:56 PM
 */
namespace APP\Model;

use APP;
use APP\DAO;

class Upload
{
	public static function getAllFiles($params){
		try {
			$result = DAO\Upload::getAllFiles($params);

			return $result;
		} catch (\Exception $e) {
			throw new \Exception($e->getMessage(), $e->getCode());
		}
	}

	public static function createFile($params){
		try {
			$result = DAO\Upload::createFile($params);

			return $result;
		} catch (\Exception $e) {
			throw new \Exception($e->getMessage(), $e->getCode());
		}
	}

    public static function get($params)
    {
        try {
            return DAO\Upload::get($params);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), $e->getCode());
        }

    }
}