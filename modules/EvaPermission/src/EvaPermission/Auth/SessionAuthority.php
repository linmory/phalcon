<?php

namespace Eva\EvaPermission\Auth;

use Phalcon\Acl\Adapter\Memory as MemoryAcl;
use Phalcon\Acl;
use Eva\EvaEngine\Exception;
use Eva\EvaPermission\Entities;
use Eva\EvaUser\Models\Login as LoginUser;

class SessionAuthority
{
    protected $acl;

    public function getAcl()
    {
        return $this->acl;
    }

    public function setAcl(Acl $acl)
    {
        $this->acl = $acl;
        return $this;
    }

    public function setUser(LoginUser $user)
    {
        $this->user = $user;
        return $this;
    }

    public function getUser()
    {
        if(!$this->user) {
            return $this->user = new LoginUser();
        }
        return $this->user;
    }

    public function checkAuth($resource, $operation)
    {
        $roles = Entities\Roles::findFirst();
        $user = $this->getUser();
        if(!$user->isUserLoggedIn()) {
            return false;
        }
    }

    public function __construct()
    {
        $acl = new MemoryAcl();
        $acl->setDefaultAction(Acl::DENY);
        $roles = Entities\Roles::find();
        foreach($roles as $role) {
            $acl->addRole($role->roleKey, $role->description);
        }
        $resources = Entities\Resources::find();
        foreach($resources as $resource) {
            $acl->addResource($resource->resourceKey);
        }
        $operations = Entities\RolesOperations::find();
        foreach($operations as $operation) {
            $acl->addResourceAccess($operation->resourceKey, $operation->operationKey);
            if($operation->roles) {
                foreach($operation->roles as $role) {
                    $acl->allow($role->roleKey, $operation->resourceKey, $operation->operationKey);
                }
            }
        }
        $this->acl = $acl;
    }
}
