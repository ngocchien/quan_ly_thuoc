<?php
/**
 * Created by PhpStorm.
 * User: tuandv
 * Date: 8/17/16
 * Time: 07:20
 */

namespace APP\Business;

use APP\Model;

class Upload
{
    public static $image;
    public static $image_type;
    public static $url;

    public static function uploadFromUrl($params){
        $resp = array();

        if (!file_exists(UPLOAD_URL .date('Y'). '/' . date('m'). '/big/')) {
            mkdir(UPLOAD_URL .date('Y'). '/' . date('m'). '/big/', 0775, true);
            chmod(UPLOAD_URL .date('Y'). '/' . date('m'). '/big/', 0775);
        }

        if (!file_exists(UPLOAD_URL .date('Y'). '/' . date('m'). '/med/')) {
            mkdir(UPLOAD_URL .date('Y'). '/' . date('m'). '/med/', 0775, true);
            chmod(UPLOAD_URL .date('Y'). '/' . date('m'). '/med/', 0775);
        }

        if (!file_exists(UPLOAD_URL .date('Y'). '/' . date('m'). '/small/')) {
            mkdir(UPLOAD_URL .date('Y'). '/' . date('m'). '/small/', 0775, true);
            chmod(UPLOAD_URL .date('Y'). '/' . date('m'). '/small/', 0775);
        }

        $small_url = UPLOAD_URL .date('Y'). '/' . date('m'). '/small/';
        $small_src = 'uploads/'.date('Y'). '/' . date('m'). '/small/';

        $med_url = UPLOAD_URL .date('Y'). '/' . date('m'). '/med/';
        $med_src = 'uploads/'.date('Y'). '/' . date('m'). '/med/';

        $big_url = UPLOAD_URL .date('Y'). '/' . date('m'). '/big/';
        $big_src = 'uploads/'.date('Y'). '/' . date('m'). '/big/';

        $href = isset($params['href']) ? $params['href'] : '';
        $file_name = isset($params['file_name']) ? $params['file_name'] : '';
        $filename = $file_name ? $file_name . '-'. rand(99, 999) : date('Ymd').rand(99, 999);
        if($href){
            $info = getimagesize($href);
            $file_type = '.png';
            if ($info[2] == IMAGETYPE_JPEG) {
                $file_type = '.jpg';
            } elseif ($info[2] == IMAGETYPE_GIF) {
                $file_type = '.gif';
            }

            $filename = $filename . $file_type;

            file_put_contents($big_url . $filename, file_get_contents($href));

            //small
            copy($big_url . $filename, $small_url. $filename);

            self::load($small_url . $filename);

            self::resizeImage(150, 195);

            //medium
            copy($big_url . $filename, $med_url. $filename);

            self::load($med_url . $filename);

            self::resizeImage(450, 600);

            //Add image into database
            if (file_exists($big_url . $filename)){
                $big = array(
                    'name' => $filename,
                    'file_name' => $filename,
                    'file_size' => "",
                    'parent_id' => 0,
                    'type' => 1,
                    'alt' => "",
                    'ctime' => date('Y-m-d H:i:s'),
                    'src' => $big_src . $filename
                );

                $big_id = Model\Upload::createFile($big);
            }

            if (file_exists($med_url . $filename)){
                $medium = array(
                    'name' => $filename,
                    'file_name' => $filename,
                    'file_size' => "",
                    'parent_id' => isset($big_id) ? $big_id : 0,
                    'type' => 2,
                    'alt' => "",
                    'ctime' => date('Y-m-d H:i:s'),
                    'src' => $med_src . $filename
                );

                $medium_id = Model\Upload::createFile($medium);
            }

            if (file_exists($med_url . $filename)){
                $small = array(
                    'name' => $filename,
                    'file_name' => $filename,
                    'file_size' => "",
                    'parent_id' => isset($big_id) ? $big_id : 0,
                    'type' => 3,
                    'alt' => "",
                    'ctime' => date('Y-m-d H:i:s'),
                    'src' => $small_src . $filename
                );

                $small_id = Model\Upload::createFile($small);
            }
        }

        return $resp;
    }

    public static function load($filename)
    {
        self::$url = $filename;
        $image_info = getimagesize($filename);
        self::$image_type = $image_info[2];
        if (self::$image_type == IMAGETYPE_JPEG) {
            self::$image = imagecreatefromjpeg($filename);
        } elseif (self::$image_type == IMAGETYPE_GIF) {

            self::$image = imagecreatefromgif($filename);
        } elseif (self::$image_type == IMAGETYPE_PNG) {

            self::$image = imagecreatefrompng($filename);
        }
    }

    public static function save($filename, $image_type = IMAGETYPE_JPEG, $compression = 75, $permissions = null)
    {

        if ($image_type == IMAGETYPE_JPEG) {
            imagejpeg(self::$image, $filename, $compression);
        } else if ($image_type == IMAGETYPE_GIF) {

            imagegif(self::$image, $filename);
        } else if ($image_type == IMAGETYPE_PNG) {

            imagepng(self::$image, $filename);
        }
        if ($permissions != null) {

            chmod($filename, $permissions);
        }
    }

    public static function output($image_type = IMAGETYPE_JPEG)
    {

        if ($image_type == IMAGETYPE_JPEG) {
            imagejpeg(self::$image);
        } elseif ($image_type == IMAGETYPE_GIF) {

            imagegif(self::$image);
        } elseif ($image_type == IMAGETYPE_PNG) {

            imagepng(self::$image);
        }
    }

    public static function getWidth()
    {
        return imagesx(self::$image);
    }

    public static function getHeight()
    {
        return imagesy(self::$image);
    }

    public static function resizeToHeight($height)
    {
        $ratio = $height / self::getHeight();
        $width = self::getWidth() * $ratio;
        self::resize($width, $height);
    }

    public static function resizeToWidth($width)
    {
        $ratio = $width / self::getWidth();
        $height = self::getheight() * $ratio;
        self::resize($width, $height);
    }

    public static function scale($scale)
    {
        $width = self::getWidth() * $scale / 100;
        $height = self::getheight() * $scale / 100;
        self::resize($width, $height);
    }

    public static function resize($width, $height)
    {
        $new_image = imagecreatetruecolor($width, $height);
        imagefill($new_image, 0, 0, imagecolorallocate($new_image, 255, 255, 255));
        imagealphablending($new_image, TRUE);
        imagecopyresampled($new_image, self::$image, 0, 0, 0, 0, $width, $height, self::getWidth(), self::getHeight());
        self::$image = $new_image;
    }

    public static function resizeImage($width, $height)
    {
        if (self::getWidth() > $width && self::getHeight() > $height) {
            $ratio = $width / self::getWidth();
            $height = self::getheight() * $ratio;
            self::resize($width, $height);
            self::save(self::$url);
        } else if (self::getWidth() > $width && self::getHeight() < $height) {
            self::resizeToWidth($width);
            self::save(self::$url);
        } else if (self::getWidth() < $width && self::getHeight() > $height) {
            self::resizeToHeight($height);
            self::save(self::$url);
        }
    }

	public static function getAllFiles($params){
		$files = Model\Upload::getAllFiles($params);

		$result = array();
		if(isset($files['rows']) && !empty($files['rows'])){
			foreach($files['rows'] as &$file){
				if(isset($file['ctime'])){
					$ctime = strtotime($file['ctime']);
					$month = date('m', $ctime);
					$year = date('Y', $ctime);

					//set ctime
					$create_date = new \DateTime(date($file['ctime']));
					$file['ctime'] = $create_date->format("d-m-Y H:i:s");

					if(isset($file['parent_id']) && $file['parent_id'] == 0){
						$result[$month . '/' . $year][] =  $file;
					}
				}
			}
		}

		return $result;
	}

    public static function getList($params){
        $limit = empty($params['limit']) ? 100 : (int) $params['limit'];
        $page = empty($params['page']) ? 1 : (int)$params['page'];
        $offset = $limit * ($page - 1);
        $params['offset'] = $offset;
        $params['limit'] = $limit;
        $params['order'] = 'FID ASC';
        $result = Model\Upload::get($params);
        return $result;
    }
}