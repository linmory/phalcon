<?php

namespace Eva\EvaComment\Controllers\Admin;

use Eva\EvaComment\Models;
use Eva\EvaEngine\Mvc\Controller\JsonControllerInterface;
use Eva\EvaEngine\Exception;

class ProcessController extends ControllerBase implements JsonControllerInterface
{
    public function deleteAction()
    {
        if (!$this->request->isDelete()) {
            return $this->displayJsonErrorResponse(405, 'ERR_REQUEST_METHOD_NOT_ALLOW');
        }

        $id = $this->dispatcher->getParam('id');
        $post =  new Models\Post();
        try {
            $post->removePost($id);
        } catch (\Exception $e) {
            return $this->displayExceptionForJson($e, $post->getMessages());
        }

        return $this->response->setJsonContent($post);
    }

    public function statusAction()
    {
        if (!$this->request->isPut()) {
            return $this->displayJsonErrorResponse(405, 'ERR_REQUEST_METHOD_NOT_ALLOW');
        }

        $id = $this->dispatcher->getParam('id');
        $status = $this->request->getPut('status');

        $commentManager =  new Models\CommentManager();

        $comment = $commentManager->findCommentById($id);

        try {
            $commentManager->updateCommentStatus($comment,$status);
        } catch (\Exception $e) {
            return $this->displayExceptionForJson($e, $comment->getMessages());
        }

        return $this->response->setJsonContent($comment);
    }

    public function batchAction()
    {
        if (!$this->request->isPut()) {
            return $this->displayJsonErrorResponse(405, 'ERR_REQUEST_METHOD_NOT_ALLOW');
        }

        $idArray = $this->request->getPut('id');
        if (!is_array($idArray) || count($idArray) < 1) {
            return $this->displayJsonErrorResponse(401, 'ERR_REQUEST_PARAMS_INCORRECT');
        }

        $status = $this->request->getPut('status');
        $comments = array();

        $commentManager =  new Models\CommentManager();


        try {
            foreach ($idArray as $id) {

                $comment = $commentManager->findCommentById($id);

                if ($comment) {
                    $commentManager->updateCommentStatus($comment,$status);

                    $comments[] = $comment;
                }
            }
        } catch (\Exception $e) {
            return $this->displayExceptionForJson($e, $comment->getMessages());
        }

        return $this->response->setJsonContent($comments);
    }


    public function slugAction()
    {
        $slug = $this->request->get('slug');
        $exclude = $this->request->get('exclude');
        if ($slug) {
            $conditions = array(
                "columns" => array('id'),
                "conditions" => 'slug = :slug:',
                "bind" => array(
                    'slug' => $slug
                )
            );
            if($exclude) {
                $conditions['conditions'] .= ' AND id != :id:';
                $conditions['bind']['id'] = $exclude;
            }
            $post = Models\Post::findFirst($conditions);
        } else {
            $post = array();
        }

        if ($post) {
            $this->response->setStatusCode('409', 'Post Already Exists');
        }

        return $this->response->setJsonContent(array(
            'exist' => $post ? true : false,
            'id' => $post ? $post->id : 0,
        ));
    }

}
