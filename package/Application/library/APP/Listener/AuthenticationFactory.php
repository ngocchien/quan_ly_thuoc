<?php

namespace APP\Listener;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class AuthenticationFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $sl)
    {
        $listener = new Authentication();
        return $listener;
    }
}