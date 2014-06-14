<?php

namespace Eva\EvaPermission\Entities;

class Resources extends \Eva\EvaEngine\Mvc\Model
{
    protected $tableName = 'permission_resources';

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
    public $description;
}
