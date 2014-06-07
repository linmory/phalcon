<?php

namespace WscnWap\Controllers;

use Eva\EvaEngine\Exception;
use Phalcon\Http\Client\Request;


class IndexController extends ControllerBase
{
    public function beforeExecuteRoute()
    {
        $id = $this->dispatcher->getParam('id');
        $cacheKey = "node-$id";
        $this->view->cache(array(
            'lifetime' => 3600,
            'key' => $cacheKey,
        ));
        if($this->view->getCache()->exists($cacheKey)) {
            return false;
        }
    }

    public function indexAction()
    {
        return $this->response->redirect('http://wallstreetcn.com/');
    }

    public function nodeAction()
    {
        $id = $this->dispatcher->getParam('id');
        $provider  = Request::getProvider();
        $provider->setBaseUri('http://api.wallstreetcn.com/apiv1/');
        $response = $provider->get("node/$id.json");
        //$response->header->statusCode;
        $post = json_decode($response->body);
        $this->view->setVar('post', $post);
    }
}
