<?php

return array(
    '/' =>  array(
        'module' => 'WscnWap',
        'controller' => 'index',
    ),
    '/node/(\d+)' =>  array(
        'module' => 'WscnWap',
        'controller' => 'index',
        'action' => 'node',
        'id' => 1 
    ),
);
