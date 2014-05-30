<?php
namespace Eva\EvaComment\Controllers\Admin;

use \Eva\EvaEngine\Mvc\Controller\ControllerBase as Base;

class ControllerBase extends Base
{
    public function initialize()
    {
        $this->view->setModuleLayout('EvaCore', '/views/admin/layouts/layout');
        $this->view->setModuleViewsDir('EvaComment', '/views');
        $this->view->setModulePartialsDir('EvaCore', '/views');
    }
}
