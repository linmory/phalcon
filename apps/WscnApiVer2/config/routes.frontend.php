<?php

return array(
    '/' =>  array(
        'module' => 'WscnApiVer2',
        'controller' => 'index',
        'action' => 'index'
    ),
    '/v2/resources/(\w+)' =>  array(
        'module' => 'WscnApiVer2',
        'controller' => 'index',
        'action' => 'resource',
        'id' => 1,
    ),
    '/v2/resources' =>  array(
        'module' => 'WscnApiVer2',
        'controller' => 'index',
        'action' => 'resources',
    ),
    'postlist' =>  array(
        'pattern' => '/v2/post',
        'paths' => array(
            'module' => 'WscnApiVer2',
            'controller' => 'post',
            'action' => 'index',
        ),
        'httpMethods' => 'GET'
    ),
    'createpost' =>  array(
        'pattern' => '/v2/post',
        'paths' => array(
            'module' => 'WscnApiVer2',
            'controller' => 'post',
            'action' => 'post',
        ),
        'httpMethods' => 'POST'
    ),
    'getpost' =>  array(
        'pattern' => '/v2/post/(\d+)',
        'paths' => array(
            'module' => 'WscnApiVer2',
            'controller' => 'post',
            'action' => 'get',
            'id' => 1,
        ),
        'httpMethods' => 'GET'
    ),
    'putpost' =>  array(
        'pattern' => '/v2/post/(\d+)',
        'paths' => array(
            'module' => 'WscnApiVer2',
            'controller' => 'post',
            'action' => 'put',
            'id' => 1,
        ),
        'httpMethods' => 'PUT'
    ),
    'deletepost' =>  array(
        'pattern' => '/v2/post/(\d+)',
        'paths' => array(
            'module' => 'WscnApiVer2',
            'controller' => 'post',
            'action' => 'delete',
            'id' => 1,
        ),
        'httpMethods' => 'DELETE'
    ),
    'userlist' =>  array(
        'pattern' => '/v2/user',
        'paths' => array(
            'module' => 'WscnApiVer2',
            'controller' => 'user',
            'action' => 'index',
        ),
        'httpMethods' => 'GET'
    ),
    'createuser' =>  array(
        'pattern' => '/v2/user',
        'paths' => array(
            'module' => 'WscnApiVer2',
            'controller' => 'user',
            'action' => 'user',
        ),
        'httpMethods' => 'POST'
    ),
    'getuser' =>  array(
        'pattern' => '/v2/user/(\d+)',
        'paths' => array(
            'module' => 'WscnApiVer2',
            'controller' => 'user',
            'action' => 'get',
            'id' => 1,
        ),
        'httpMethods' => 'GET'
    ),
    'putuser' =>  array(
        'pattern' => '/v2/user/(\d+)',
        'paths' => array(
            'module' => 'WscnApiVer2',
            'controller' => 'user',
            'action' => 'put',
            'id' => 1,
        ),
        'httpMethods' => 'PUT'
    ),
    'deleteuser' =>  array(
        'pattern' => '/v2/user/(\d+)',
        'paths' => array(
            'module' => 'WscnApiVer2',
            'controller' => 'user',
            'action' => 'delete',
            'id' => 1,
        ),
        'httpMethods' => 'DELETE'
    ),
);
