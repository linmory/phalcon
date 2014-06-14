<?php

namespace Eva\EvaPermission\Entities;

class Operations extends \Eva\EvaEngine\Mvc\Model
{
    protected $tableName = 'permission_operations';

    /**
     *
     * @var integer
     */
    public $id;

    /**
     *
     * @var string
     */
    public $name;

    /**
     *
     * @var integer
     */
    public $resourceId;

}
