<?php

namespace Eva\EvaPermission\Events;

use Eva\EvaEngine\Exception;
use Eva\EvaEngine\Mvc\Controller\SessionAuthorityControllerInterface;

class DispatchListener
{
    public function beforeExecuteRoute($event)
    {
        $dispatcher = $event->getSource();
        $controller = $dispatcher->getActiveController();
        //Not need to authority
        if(!($controller instanceof SessionAuthorityControllerInterface)) {
            return true;
        }

        $session = $dispatcher->getDI()->getSession();
        $authIdentity = $session->get('auth-identity');

        /*
        $acl = new \Phalcon\Acl\Adapter\Memory();
        $acl->setDefaultAction(\Phalcon\Acl::DENY);
        $roleAdmins = new \Phalcon\Acl\Role("ADMIN", "Super-User role");
        $roleGuests = new \Phalcon\Acl\Role("Guests");
        $acl->addRole($roleAdmins);
        $acl->addRole($roleGuests);
        $acl->addResource("Eva\EvaUser\Controllers\Admin\UserController", array('index'));
        $acl->allow("ADMIN", "Eva\EvaUser\Controllers\Admin\UserController", "index");
        if(!$acl->isAllowed("ADMIN", get_class($controller), "index")) {
        */
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
