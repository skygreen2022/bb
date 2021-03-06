<?php

/**
 * -----------| 控制器:评论 |-----------
 * @category Betterlife
 * @package web.model.action
 * @author skygreen skygreen2001@gmail.com
 */
class Action_Comment extends ActionModel
{
    /**
     * 评论列表
     */
    public function lists()
    {
        if ($this->isDataHave(TagPageService::$linkUrl_pageFlag)) {
            $nowpage = $this->data[TagPageService::$linkUrl_pageFlag];
        } else {
            $nowpage = 1;
        }
        $count = Comment::count();
        $this->view->countComments = $count;
        $comments = null;
        if ($count > 0) {
            $bb_page = TagPageService::init($nowpage, $count);
            $comments = Comment::queryPage($bb_page->getStartPoint(), $bb_page->getEndPoint());
        }
        $this->view->set("comments", $comments);
    }
    /**
     * 查看评论
     */
    public function view()
    {
        $commentId = $this->data["id"];
        $comment   = Comment::getById($commentId);
        $this->view->set("comment", $comment);
    }
    /**
     * 编辑评论
     */
    public function edit()
    {
        if (!empty($_POST)) {
            $comment = $this->model->Comment;
            $id         = $comment->getId();
            $isRedirect = true;
            if (!empty($id)) {
                $comment->update();
            } else {
                $id = $comment->save();
            }
            if ($isRedirect) {
                $this->redirect("comment", "view", "id=$id");
                exit;
            }
        }
        $commentId = $this->data["id"];
        $comment   = Comment::getById($commentId);
        $this->view->set("comment", $comment);
        $users = User::get("", "user_id asc");
        $this->view->set("users", $users);
        $blogs = Blog::get("", "blog_id asc");
        $this->view->set("blogs", $blogs);
        //加载在线编辑器的语句要放在:$this->view->viewObject[如果有这一句]之后。
        $this->load_onlineditor('comment');
    }
    /**
     * 删除评论
     */
    public function delete()
    {
        $commentId = $this->data["id"];
        $isDelete = Comment::deleteByID($commentId);
        $this->redirect("comment", "lists", $this->data);
    }
}
