<?php

namespace Eva\EvaFileSystem\Controllers\Admin;

class ControllerBase extends \Eva\EvaEngine\Mvc\Controller\AdminControllerBase
{
    public function initialize()
    {
        $this->view->setModuleLayout('EvaCommon', '/views/admin/layouts/layout');
        $this->view->setModuleViewsDir('EvaFileSystem', '/views');
        $this->view->setModulePartialsDir('EvaCommon', '/views');
    }
}
