<?php

namespace Eva\EvaPermission\Events;

use Eva\EvaEngine\Exception;
use Eva\EvaEngine\Mvc\Controller\AuthorityControllerInterface;

class DispatchListener
{
    public function beforeExecuteRoute($event)
    {
        $dispatcher = $event->getSource();
        /*
        $controller = $dispatcher->getHandlerClass();
        $action = $dispatcher->getActionName();
        $ref = new \ReflectionClass($controller);
        $interfaceNames = $ref->getInterfaceNames();

        //Not need to authority
        if(false === in_array('Eva\EvaEngine\Mvc\Controller\AuthorityControllerInterface', $interfaceNames)) {
            return true;
        }
        */
        $controller = $dispatcher->getActiveController();
        //Not need to authority
        if(!($controller instanceof AuthorityControllerInterface)) {
            return true;
        }

        $session = $dispatcher->getDI()->getSession();
        $authIdentity = $session->get('auth-identity');
        if(!$authIdentity) {
            $dispatcher->setModuleName('EvaPermission');
            $dispatcher->setNamespaceName('Eva\EvaPermission\Controllers');
            $dispatcher->setControllerName('Error');
            $dispatcher->setActionName('index');
            $dispatcher->forward(array(
                "controller" => "error",
                "action" => "index",
                "params" => array(
                    'isAdminController' => true,
                    'redirectUri' => '',
                )
            ));
            return false;
        }

    }
}
