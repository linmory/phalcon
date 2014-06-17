<?php

namespace Eva\EvaBlog\Controllers\Admin;

use Eva\EvaEngine\Mvc\Controller\ControllerBase as AdminControllerBase;
use Eva\EvaEngine\Mvc\Controller\AuthorityControllerInterface;

class ControllerBase extends AdminControllerBase implements AuthorityControllerInterface
{
    public function initialize()
    {
        $this->view->setModuleLayout('EvaCommon', '/views/admin/layouts/layout');
        $this->view->setModuleViewsDir('EvaBlog', '/views');
        $this->view->setModulePartialsDir('EvaCommon', '/views');
    }
}
