<?php

/**
 * @author      :   VuNCD
 * @name        :   Backend_View_Helper_Paging
 * @version     :   1.0
 * @copyright   :   FPT Online
 * @todo        :
 */

namespace APP\Helper;

use APP\Exception;
use Zend\View\Helper\AbstractHelper;
use APP\Model\Common;

class PagingText extends AbstractHelper {

    public function __invoke($params) {
        $paging = $this->paging($params);
        return $paging;
    }

    /**
     * Genegate HTML paging
     * @param <string> $strModule
     * @param <string> $strController
     * @param <string> $strAction
     * @param <array> $arrCondition
     * @param <int> $intTotal
     * @param <int> $intPage
     * @param <int> $intLimit
     * @param <string> $strRoute
     * @return <string> $result
     */
    public function paging($params) {
        try{
            $result = '';
            $intTotal = (int) $params['total'];
            $intCurrentPage = empty($params['page']) ? 1 : (int) $params['page'];
            $intLimit = empty($params['limit']) ? 10 : (int) $params['limit'];
            $strModule = strtolower($params['module']);
            $strController = strtolower($params['controller']);
            $strAction = strtolower($params['action']);
            $strRoute = $params['route'];
            $str = 'kết quả';

            if (empty($strModule) || empty($strController) || empty($strAction) || $intTotal < 0) {
                return $result;
            }

            $intTotal > 0 && $intLimit > 0 ? $intTotalPage = ceil($intTotal / $intLimit) : $intTotalPage = 0;
            if ($intTotalPage < $intCurrentPage || $intTotalPage <= 1) {
                return $result;
            }
            $urlHelper = $this->view->plugin('url');
            $arrCondition = array('controller' => $strController, 'action' => $strAction, 'page' => $intCurrentPage, 'limit' => $intLimit);
            $arrCondition = $params ? $arrCondition + $params : $arrCondition;
            $arr_limit = Common::getListLimitQuery();
            if ($strModule === 'administrator') {
                $serverUrl = $urlHelper('home', array(), array('force_canonical' => true));
                $serverUrl = substr($serverUrl, 0, -1);
                $result .= '<div class="rows">';
                $result .= '<div class="col-xs-12">';
                $result .= '<div class="pagination pull-left" style="padding: 0px">';
                $result .= '<select class="form-group limit-query" style="margin-bottom: 0px">';
                foreach ($arr_limit as $temp_limit){
                    $arr_temp = $arrCondition;
                    $arr_temp['limit'] = $temp_limit;
                    $selected = '';
                    if($temp_limit == $intLimit){
                        $selected = 'selected';
                    }
                    $link = $serverUrl . $urlHelper($strRoute,$arr_temp);
                    $result .= '<option value="'.$link.'"'.$selected.' >';
                    $result .= $temp_limit;
                    $result .= '</option>';
                }
                $result .= '</select>';
                $result .= '&#160;&#160;&#160;&#160;&#160;Dòng';
                $result .= '</div>';
                $result .= '<ul class="pagination pull-right">';
                if ($intCurrentPage == 1) {
                    $intPage = 1;
                    $intLimitPage = 10;
                } else {
                    $intPage = ($intCurrentPage > 5) ? ($intCurrentPage == $intTotalPage && $intTotalPage > 10) ? $intCurrentPage - 10 : $intCurrentPage - 5 : 1;
                    $intLimitPage = ($intTotalPage < 11) ? $intTotalPage : $intCurrentPage + 5;
                    $arrCondition['page'] = 1;
                    $result .= '<li class="paginate_button"  aria-controls="dynamic-table" tabindex="0"><a class="" href="' . $serverUrl . $urlHelper($strRoute, $arrCondition) . '">« Đầu</a></li>';
                    $arrCondition['page'] = $intCurrentPage - 1;
                    $result .= '<li  class="paginate_button" aria-controls="dynamic-table"><a class="" href="' . $serverUrl . $urlHelper($strRoute, $arrCondition) . '">← Trước</a></li>';
                }
                for ($intPage; $intPage <= $intTotalPage && $intPage <= $intLimitPage; $intPage++) {
                    $arrCondition['page'] = $intPage;
                    if ($intPage == $intCurrentPage) {
                        $result .= '<li class="paginate_button active" aria-controls="dynamic-table" tabindex="0"><a class=""  href="' . $serverUrl . $urlHelper($strRoute, $arrCondition) . '">' . $intPage . '</a></li>';
                    } else {
                        $result .= '<li class="paginate_button" aria-controls="dynamic-table" tabindex="0"><a class="" href="' . $serverUrl . $urlHelper($strRoute, $arrCondition) . '">' . $intPage . '</a></li>';
                    }
                }
                if ($intCurrentPage == $intTotalPage) {
                    $result .= '';
                } else {
                    $arrCondition['page'] = $intCurrentPage + 1;
                    $result .= '<li class="paginate_button" aria-controls="dynamic-table" tabindex="0"><a class="page-link" href="' . $serverUrl . $urlHelper($strRoute, $arrCondition) . '">Sau →</a></li>';
                    $arrCondition['page'] = $intTotalPage;
                    $result .= '<li class="paginate_button" aria-controls="dynamic-table" tabindex="0"><a class="page-link" href="' . $serverUrl . $urlHelper($strRoute, $arrCondition) . '">Cuối »</a></li>';
                }
                $result .= '</ul></div>';
                $result .='<div class="col-xs-12">';
                $result .='<div class="pull-right">';
                $from = ($intLimit * ($intCurrentPage - 1)) + 1;
                $tmp1 = 'Hiển thị từ ' . $from . ' đến ';
                $tmp2 = ($intLimit * $intCurrentPage > $intTotal) ? number_format($intTotal, 0, ',', '.') : $intLimit * $intCurrentPage;
                $tmp3 = ' trong tổng số ' . number_format($intTotal, 0, ',', '.') . ' ' . $str;
                $from == $intTotal ? $result .= $str . ' cuối cùng trong tổng số ' . number_format($intTotal, 0, ',', '.') . ' ' . $str : $result .= $tmp1 . $tmp2 . $tmp3;
                $result .= '</div></div></div>';

                ##################################################
            } elseif ($strModule === 'application') {
                $serverUrl = $urlHelper('home', array(), array('force_canonical' => true));
                $serverUrl = substr($serverUrl, 0, -1);
                $result .= '<div class="row"><div class="col-md-12 text-center">';
                $result .= '<ul class="pagination" style="width:auto;">';
                if ($intCurrentPage == 1) {
                    $intPage = 1;
                    $intLimitPage = 10;
                } else {
                    $intPage = ($intCurrentPage > 5) ? ($intCurrentPage == $intTotalPage && $intTotalPage > 10) ? $intCurrentPage - 10 : $intCurrentPage - 5 : 1;
                    $intLimitPage = ($intTotalPage < 11) ? $intTotalPage : $intCurrentPage + 5;
                    $arrCondition['page'] = 1;
                    $result .= '<li><a href="' . $serverUrl . $urlHelper($strRoute, $arrCondition) . '">đầu</a></li>';
                    $arrCondition['page'] = $intCurrentPage - 1;
                    $result .= '<li><a href="' . $serverUrl . $urlHelper($strRoute, $arrCondition) . '">trước</a></li>';

                }
                for ($intPage; $intPage <= $intTotalPage && $intPage <= $intLimitPage; $intPage++) {
                    $arrCondition['page'] = $intPage;
                    if ($intPage == $intCurrentPage) {
                        $result .= '<li class="active"><a style="cursor:pointer;">' . $intPage . '</a></li>';
                    } else {
                        $result .= '<li><a href="' . $serverUrl . $urlHelper($strRoute, $arrCondition) . '">' . $intPage . '</a></li>';
                    }
                }
                if ($intCurrentPage == $intTotalPage) {
                    $result .= '';
                } else {
                    $arrCondition['page'] = $intCurrentPage + 1;
                    $result .= '<li><a href="' . $serverUrl . $urlHelper($strRoute, $arrCondition) . '">sau</a></li>';
                    $arrCondition['page'] = $intTotalPage;
                    $result .= '<li><a href="' . $serverUrl . $urlHelper($strRoute, $arrCondition) . '">cuối</a></li>';
                }

                $result .= '</ul></div></div>';
            }
            return $result;
        }catch (Exception $ex){
            echo '<pre>';
            print_r($ex->getMessage());
            echo '</pre>';
            die();
        }

    }

}
