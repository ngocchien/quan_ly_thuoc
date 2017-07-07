<?php
namespace APP;

use Zend;

class Exception extends \Exception
{
    public function __construct($msg = '', $code = 0, $data_exception = array(), Exception $previous = null)
    {
        parent::__construct($msg, (int)$code, $previous);
        $this->_log($msg = '', $code = 0, $data_exception, $previous);
    }

    protected function _log($msg = '', $code = 0, $data_exception = array(), Exception $previous = null)
    {
        //
        $file_path = LOG_FOLDER . '/' . date('d-m-Y') . '.log';
        //
        $stream = fopen($file_path, 'a', false);
        //
        if (is_resource($stream) && substr(sprintf('%o', fileperms($file_path)), -4) != '0666') {
            chmod($file_path, 0666);
        }
        //
        if (!$stream) {
            throw new \Exception('Failed to open stream');
        }

        //
        $writer = new Zend\Log\Writer\Stream($stream);
        $logger = new Zend\Log\Logger();
        $logger->addWriter($writer);
        $dataBinding = $this->buildRawData($data_exception);

        //
        $logger->info(implode(PHP_EOL, array(
            '- Time: ' . date('d/m/Y H:i:s'),
            '- Code: ' . $this->getCode(),
            '- Message: ' . $this->getMessage(),
            '- Raw Query: ' .str_replace('BEGINPKG','BEGIN PKG',str_replace(' ', '', str_replace(PHP_EOL,'',$dataBinding['raw_sql']))),
            '- Data: ' . json_encode($dataBinding['data']),
            '- File: ' . $this->getFile(),
            '- Line: ' . $this->getLine(),
            '- Stack Trace: ' . $this->getTraceAsString())) . str_repeat(PHP_EOL, 2)
        );
    }

    protected function buildRawData($data)
    {
        $arr_return = [];
        if (is_array($data)) {
            $data_binding = isset($data['data']) ? $data['data'] : array();
            $raw_sql = isset($data['raw_sql']) ? $data['raw_sql'] : '';
            $new_sql = '';
            $data_type = isset($data['data_type']) ? $data['data_type'] : array();
            //pr($data_type);
            if (!empty($data_type)) {
                $data_type = iterator_to_array($data_type, true);
            }
            foreach ($data_binding as $key => $value) {
                if (!is_numeric($value) && !is_array($value) && !is_null($value)) {
                    $value = "'" . $value . "'";
                }
                if (is_null($value)) {
                    $value = NULL;
                }
                if (is_array($value)) {
                    $type = $data_type[$key];
                    switch ($type) {
                        case 'arr_num': {
                            $value = 'T_ARRNUM(' . implode(',', $value) . ')';
                            break;
                        }
                        case 'arr_char': {
                            $arr_char = [];
                            foreach ($value as $a_c) {
                                $arr_char[] = "'" . $a_c . "'";
                            }
                            $value = 'T_ARRCHAR(' . implode(',', $arr_char) . ')';
                            break;
                        }
                        case 'cursor': {
                            $value = $key;
                            break;
                        }
                    }
                }
                $raw_sql = str_replace(":" . $key, $value, $raw_sql);
            }
            $arr_return = array('data' => $data_binding, 'raw_sql' => $raw_sql);
        }
        //
        return $arr_return;
    }
}