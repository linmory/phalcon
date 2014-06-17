<?php

namespace Eva\EvaCommon\Controllers\Admin;

use Eva\EvaEngine\Mvc\Controller\ControllerBase as AdminControllerBase;
use Eva\EvaEngine\Mvc\Controller\AuthorityControllerInterface;

class IndexController extends AdminControllerBase
{

    /**
     * Index action
     */
    public function indexAction()
    {
        $this->view->disable();

        return $this->response->redirect('/admin/login');
    }

    public function dashboardAction()
    {
        $this->view->setModuleLayout('EvaCommon', '/views/admin/layouts/layout');
        $this->view->setModuleViewsDir('EvaCommon', '/views');
        $this->view->setModulePartialsDir('EvaCommon', '/views');
    }
}
