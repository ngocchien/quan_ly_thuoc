<?php
/**
 * Created by PhpStorm.
 * User: tuandv
 * Date: 8/7/16
 * Time: 15:45
 */

namespace Administrator\Controller;

use APP\Controller\MyController;

class IndexController extends MyController
{
    public function indexAction()
    {
        try {

            return [

            ];
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), $e->getCode());
        }
    }
}
