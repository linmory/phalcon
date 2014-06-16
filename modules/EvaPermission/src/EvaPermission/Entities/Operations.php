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
     * @var string
     */
    public $operationKey;

    /**
     *
     * @var integer
     */
    public $resourceId;

    /**
     *
     * @var string
     */
    public $resourceKey;

    /**
     *
     * @var string
     */
    public $description;

}
