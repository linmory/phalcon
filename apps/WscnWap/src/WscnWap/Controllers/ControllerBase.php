<?php

namespace WscnWap\Controllers;

class ControllerBase extends \Eva\EvaEngine\Mvc\Controller\ControllerBase
{
    public function initialize()
    {
        $this->view->setModuleLayout('WscnWap', '/views/layouts/default');
        $this->view->setModuleViewsDir('WscnWap', '/views');
        $this->view->setModulePartialsDir('WscnWap', '/views');
    }

}
