<?php

namespace Eva\EvaUser\Controllers;

use Eva\EvaUser\Models;
use Eva\EvaUser\Forms;

class RegisterController extends ControllerBase
{
    public function indexAction()
    {
        if (!$this->request->isPost()) {
            return;
        }

        $form = new Forms\RegisterForm();
        if ($form->isValid($this->request->getPost()) === false) {
            $this->validHandler($form);
            return $this->response->redirect($this->getDI()->get('config')->user->registerFailedRedirectUri);
        }
        $user = new Models\Login();
        $user->assign(array(
            'username' => $this->request->getPost('username'),
            'email' => $this->request->getPost('email'),
            'password' => $this->request->getPost('password'),
        ));
        try {
            $user->register();
        } catch(\Exception $e) {
            $this->errorHandler($e, $user->getMessages());
            return $this->response->redirect($this->getDI()->get('config')->user->registerFailedRedirectUri);
        }
        $this->flashSession->success('Register Success');
        return $this->response->redirect($this->getDI()->get('config')->user->registerFailedRedirectUri);
    }

    public function checkAction()
    {
        $username = $this->request->get('username');
        $email = $this->request->get('email');
        
        if($username) {
            $userinfo = Models\Login::findFirst(array("username = '$username'"));
        } elseif ($email) {
            $userinfo = Models\Login::findFirst(array("email = '$email'"));
        } else {
            $userinfo = array();
        }
        $this->view->disable();
        if($userinfo) {
            $this->response->setStatusCode('401', 'User Already Exists');
        }
        echo json_encode(array(
            'exist' => $userinfo ? true : false,
            'id' => $userinfo ? $userinfo->id : 0,
            'status' => $userinfo ? $userinfo->status : null,
        ));
    
    }

}
