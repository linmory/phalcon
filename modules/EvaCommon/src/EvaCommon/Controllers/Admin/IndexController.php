<?php

namespace Eva\EvaCommon\Controllers\Admin;

class IndexController extends \Eva\EvaEngine\Mvc\Controller\AdminControllerBase
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
