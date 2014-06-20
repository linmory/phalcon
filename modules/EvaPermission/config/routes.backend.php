<?php

return array(
    '/admin/permission/resource' =>  array(
        'module' => 'EvaPermission',
        'controller' => 'Admin\Resource',
    ),
    '/admin/permission/operation' =>  array(
        'module' => 'EvaPermission',
        'controller' => 'Admin\Operation',
    ),
    '/admin/permission/role' =>  array(
        'module' => 'EvaPermission',
        'controller' => 'Admin\Role',
    ),
    '/admin/permission/role/:action(/(\d+))*' =>  array(
        'module' => 'EvaPermission',
        'controller' => 'Admin\Role',
        'action' => 1,
        'id' => 3,
    ),
);
