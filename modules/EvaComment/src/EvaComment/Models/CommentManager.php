<?php
namespace Eva\EvaComment\Models;

use Eva\EvaComment\Entities\Comments;

use Eva\EvaEngine\Mvc\Model as BaseModel;

use InvalidArgumentException;

class CommentManager extends BaseModel
{
    function test()
    {
        $phql = 'SELECT * FROM Eva\EvaComment\Entities\Comment';


        $manager = $this->getModelsManager();
        $data = $manager->executeQuery($phql);
        foreach ($data as $k => $comment) {
            echo $comment->id;
        }
//        var_dump($data);
    }

    /**
     * {@inheritdoc}
     */
    public function createComment($thread, $parent = null)
    {
        $comment = new Comments;

        $comment->threadId = $thread->id;

        if (null !== $parent) {
            $comment->parentId = $parent->id;
            $comment->rootId = $parent->rootId ? $parent->rootId : $parent->id;

            $comment->parentPath = $parent->parentPath ? $parent->parentPath.'/'.$parent->id : $parent->id;

            $comment->depth = $comment->depth+1;
        }

//        $event = new CommentEvent($comment);
//        $this->dispatcher->dispatch(Events::COMMENT_CREATE, $event);

        return $comment;
    }

    /**
     * {@inheritdoc}
     */
    public function saveComment($comment)
    {
        $thread = $comment->getThread();
        if (null === $thread) {
            throw new InvalidArgumentException('The comment must have a thread');
        }

        $comment->save();

        $threadManager = new ThreadManager();
        $threadManager->addCommentNumber($thread);

        return true;
    }

    function findComments($query=array())
    {
//        $phql = 'SELECT * FROM Eva\EvaComment\Entities\Comments AS c ORDER BY c.createdAt DESC';

//        $manager = $this->getModelsManager();
//        $comments = $manager->createQuery($phql);

//        return $comments;

        $itemQuery = $this->getModelsManager()->createBuilder();

        $itemQuery->from('Eva\EvaComment\Entities\Comments');

        $orderMapping = array(
            'id' => 'id ASC',
            '-id' => 'id DESC',
            'created_at' => 'createdAt ASC',
            '-created_at' => 'createdAt DESC',
        );

        if (!empty($query['columns'])) {
            $itemQuery->columns($query['columns']);
        }

        if (!empty($query['q'])) {
            $itemQuery->andWhere('title LIKE :q:', array('q' => "%{$query['q']}%"));
        }

        if (!empty($query['status'])) {
            $itemQuery->andWhere('status = :status:', array('status' => $query['status']));
        }

        if (!empty($query['uid'])) {
            $itemQuery->andWhere('userId = :uid:', array('uid' => $query['uid']));
        }

        if (!empty($query['cid'])) {
            $itemQuery->join('Eva\EvaBlog\Entities\CategoriesPosts', 'id = r.postId', 'r')
                ->andWhere('r.categoryId = :cid:', array('cid' => $query['cid']));
        }

        $order = 'createdAt DESC';
        if (!empty($query['order'])) {
            $order = empty($orderMapping[$query['order']]) ? 'createdAt DESC' : $orderMapping[$query['order']];
        }
        $itemQuery->orderBy($order);

        return $itemQuery;
    }

    function findCommentById($id)
    {
        $comment = Comments::findFirstById($id);
        return $comment;
    }


    function findCommentsByThread($thread, $sorter, $displayDepth)
    {
        $phql = 'SELECT * FROM Eva\EvaComment\Entities\Comments AS c WHERE c.threadId = :threadId: ORDER BY c.createdAt DESC';

        $manager = $this->getModelsManager();
        $comments = $manager->executeQuery($phql, array('threadId' => $thread->id));

        return $comments;
    }

    function findCommentTreeByThread($thread, $sorter, $displayDepth)
    {
        $phql = 'SELECT * FROM Eva\EvaComment\Entities\Comments AS c
                WHERE c.threadId = :threadId: AND c.rootId = 0 ORDER BY c.createdAt DESC';

        $manager = $this->getModelsManager();
        $comments = $manager->executeQuery($phql, array('threadId' => $thread->id));

        return $comments;
    }
} 