<?php

namespace Eva\EvaPermission\Entities;

class Roles extends \Eva\EvaEngine\Mvc\Model
{
    protected $tableName = 'permission_roles';

    /**
     *
     * @var integer
     */
    public $id;

    /**
     *
     * @var string
     */
    public $roleKey;

    /**
     *
     * @var string
     */
    public $name;

    /**
     *
     * @var string
     */
    public $description;

}
