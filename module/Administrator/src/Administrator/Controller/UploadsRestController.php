<?php
/**
 * Created by PhpStorm.
 * User: tuandv
 * Date: 8/17/16
 * Time: 06:58
 */

namespace Administrator\Controller;

use Application\Controller\AbstractApplicationRestController;
use Zend\View\Model\JsonModel;
use APP;
use APP\Model;
use APP\Business;

class UploadsRestController extends AbstractApplicationRestController
{
    public function getList()
    {
        try {
            $files = Business\Upload::getAllFiles(array(
                'limit' => 100,
                'offset' => 0
            ));

            $result = new JsonModel(array(
                'success' => true,
                'message' => 'success',
                'data' => $files
            ));

            return $result;

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), $e->getCode());
        }
    }

    public function get($id)
    {
        $result = new JsonModel(array(
            'success' => true,
            'message' => 'get id',

        ));

        return $result;

    }

    public function create($data)
    {
        date_default_timezone_set("Asia/Kolkata");
        $request = $this->getRequest();
        $type = isset($data['type']) ? $data['type'] : '';
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

        if($type){
            switch ($type){
                case 'from-url':
                    $href = isset($data['href']) ? $data['href'] : '';
                    $file_name = isset($data['file_name']) ? $data['file_name'] : '';
                    $filename = $file_name ? $file_name . '-'. rand(99, 999) : date('Ymd').rand(99, 999);
                    if($href){
                        //$file = file_get_contents($href);
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

                        Business\Upload::load($small_url . $filename);

                        Business\Upload::resizeImage(150, 195);

                        //medium
                        copy($big_url . $filename, $med_url. $filename);

                        Business\Upload::load($med_url . $filename);

                        Business\Upload::resizeImage(300, 400);

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

                            if($medium_id){
                                $resp['med'] = array(
                                    'fid' => $medium_id,
                                    'src' => $med_src . $filename
                                );
                            }
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

                            if($small_id){
                                $resp['small'] = array(
                                    'fid' => $small_id,
                                    'src' => $small_src . $filename
                                );
                            }
                        }

                    }
                    break;
            }
        }else{
            if($request){

                if(!isset($_FILES["upload"])){
                    return new JsonModel(array(
                        'success' => false,
                        'message' => 'error'
                    ));
                }

                if (!is_array($_FILES["upload"]['name'])) {
                    $filename = $_FILES["upload"]['name'];

                    move_uploaded_file($_FILES["upload"]["tmp_name"], $big_url . $filename);

                    Business\Upload::load($big_url . $filename);

                }else{
                    $fileCount = count($_FILES["upload"]['name']);

                    for ($i = 0; $i < $fileCount; $i++) {
                        $filename = $_FILES["upload"]['name'][$i];

                        move_uploaded_file($_FILES["upload"]["tmp_name"][$i], $big_url . $filename);

                        //small
                        copy($big_url . $filename, $small_url. $filename);

                        Business\Upload::load($small_url . $filename);

                        Business\Upload::resizeImage(150, 150);

                        //medium
                        copy($big_url . $filename, $med_url. $filename);

                        Business\Upload::load($med_url . $filename);

                        Business\Upload::resizeImage(300, 300);

                        //1: big, 2 med, 3 small

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

                            if($big_id){
                                $resp['big'] = array(
                                    'fid' => $big_id,
                                    'src' => $big_src . $filename
                                );
                            }
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

                            if($medium_id){
                                $resp['med'] = array(
                                    'fid' => $medium_id,
                                    'src' => $med_src . $filename
                                );
                            }
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

                            if($small_id){
                                $resp['small'] = array(
                                    'fid' => $small_id,
                                    'src' => $small_src . $filename
                                );
                            }
                        }
                    }
                }
            }
        }

        $result = new JsonModel(array(
            'success' => true,
            'message' => 'success',
            'data' => $resp
        ));

        return $result;
    }

    public function update($id, $data)
    {
        $result = new JsonModel(array(
            'success' => true,
            'message' => 'update',

        ));

        return $result;
    }

    public function delete($id)
    {
        $result = new JsonModel(array(
            'success' => true,
            'message' => 'delete',

        ));

        return $result;
    }
}
