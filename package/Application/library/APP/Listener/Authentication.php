<?php

namespace APP\Listener;

use Zend\Mvc\MvcEvent;
use Zend\EventManager\EventManager;
use APP\Model;
use APP\Token;
use APP\Nosql;

class Authentication
{
    private $is_trigger = 0;

    public function __invoke(MvcEvent $e)
    {
        if ($this->is_trigger != 0) {
            return true;
        }

        $params = null;
        $method = $e->getRequest()->getMethod();
        //
        switch ($method) {
            case 'GET':
                $params = $e->getRequest()->getQuery()->toArray();
                break;
            case 'POST':
                $params = $e->getRequest()->getQuery()->toArray();
                break;
            case 'PUT':
            case 'DELETE':
                $params = $e->getRequest()->getQuery()->toArray();
                break;
        }

        $this->is_trigger = 1;
    }
}