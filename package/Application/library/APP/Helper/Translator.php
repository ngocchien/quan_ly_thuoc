<?php

/**
 * Created by PhpStorm.
 * User: chiennn
 * Date: 17/06/2017
 * Time: 23:44
 */
namespace APP\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\Config\Reader\Json;

class Translator extends AbstractHelper
{
    public function __construct() {

    }

    public function __invoke($text, $locale = 'vn') {
        return $this->translate($text, $locale);
    }

    public function translate($text, $locale) {
        $path_language = ROOT_PATH.'/locale/language';

        switch (strtolower($locale)) {
            case 'vn':
                $path_language .= '/vi_VN.json';
                break;
            case 'en':
                $path_language .= '/en_US.json';
                break;
        }

        $reader = new Json();
        $data   = $reader->fromFile($path_language);

        if(!empty($data[$text])){
            return $data[$text];
        }

        return '';
    }
}