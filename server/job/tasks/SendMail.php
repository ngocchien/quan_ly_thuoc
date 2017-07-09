<?php
/**
 * Created by PhpStorm.
 * User: chiennn
 * Date: 09/07/2017
 * Time: 00:17
 */

namespace TASK;
use APP\Utils,
    Zend\View\Model\ViewModel,
    Zend\View\Renderer\PhpRenderer,
    Zend\View\Resolver\TemplateMapResolver;

class SendMail
{
    public function send($params){
        date_default_timezone_set('Asia/Saigon');
        $fileNameSuccess = __CLASS__ . '_' . __FUNCTION__ . '_Success';
        $fileNameError = __CLASS__ . '_' . __FUNCTION__ . '_Error';
        $arrParam = [];
        $arrParam['Data'] = $params;

        try{
            if(empty($params['template'])){
                $arrParam['Error'] = 'Not input template';
                Utils::writeLog($fileNameError, $arrParam);
                return false;
            }

            if(empty($params['arr_email'])){
                $arrParam['Error'] = 'Not input arr_email';
                Utils::writeLog($fileNameError, $arrParam);
                return false;
            }

            if(empty($params['params_content'])){
                $arrParam['Error'] = 'Not input content';
                Utils::writeLog($fileNameError, $arrParam);
                return false;
            }

            if(empty($params['title'])){
                $arrParam['Error'] = 'Not input title';
                Utils::writeLog($fileNameError, $arrParam);
                return false;
            }

            $renderer = new PhpRenderer();
            $resolver = new TemplateMapResolver();
            $resolver->setMap(array(
                'mail_template' => $params['template']
            ));

            $renderer->setResolver($resolver);
            $viewModel = new ViewModel();
            $viewModel->setTemplate('mail_template')
                ->setVariables([
                    'params' => $params['params_content']
                ]);
            $html = $renderer->render($viewModel);

            if(empty($html)){
                $arrParam['Error'] = 'Render html error';
                Utils::writeLog($fileNameError, $arrParam);
                return false;
            }

            $send = Utils::sendMail($params['arr_email'],$params['title'],$html);

            if(!$send){
                $arrParam['Error'] = 'Send mail error';
                Utils::writeLog($fileNameError, $arrParam);
                return false;
            }

            Utils::writeLog($fileNameSuccess, $arrParam);

            return true;
        } catch (\Exception $e) {
            if(APPLICATION_ENV != 'production'){
                echo '<pre>';
                print_r([
                    $e->getCode(),
                    $e->getMessage()
                ]);
                echo '</pre>';
                die();
            }

            $arrParam['exc'] = [
                'code' => $e->getCode(),
                'messages' => $e->getMessage()
            ];
            Utils::writeLog($fileNameError, $arrParam);

            return false;
        }
    }
}