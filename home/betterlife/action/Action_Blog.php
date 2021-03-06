<?php

/**
 * -----------| 控制器:博客 |-----------
 * @category Betterlife
 * @package web.front.action
 * @author skygreen skygreen2001@gmail.com
 */
class Action_Blog extends Action
{
    /**
     * 博客一览
     */
    public function index()
    {
    }

    /**
     * 显示博客列表
     */
    public function display()
    {
        $this->keywords    .= "-博客";
        $this->description .= "-显示博客列表";
        if ($this->isDataHave(TagPageService::$linkUrl_pageFlag)) {
            $nowpage = $this->data[TagPageService::$linkUrl_pageFlag];
        } else {
            $nowpage = 1;
        }

        $count   = Blog::count();
        $bb_page = TagPageService::init($nowpage, $count);
        $blogs   = Blog::queryPage($bb_page->getStartPoint(), $bb_page->getEndPoint());
        if (!$blogs) {
            $this->redirect("blog", "write");
        } else {
            $view                   = new View_Blog($this);
            $view->blogs            = $blogs;
            $view->countBlogs       = $count;
            $this->view->viewObject = $view;
        }
    }
    /**
     * 编写博客
     */
    public function write()
    {
        $this->view->color = "green";
        $blog_content      = "";
        if (!empty($_POST)) {
            $blog          = $this->model->Blog;
            $blog->user_id = HttpSession::get('user_id');
            $blog_id = $blog->getId();
            if (!empty($blog_id)) {
                $blog->update();
                $view  = new View_Blog($this);
                $view->blog             = $blog;
                $this->view->viewObject = $view;
            } else {
                $blog->save();
                $this->redirect("blog", "display");
            }
            $blog_content        = $blog->blog_content;
            $this->view->message = "博客提交成功";
            $this->view->color   = "green";
        } else {
            $blog_id = @$this->data["blog_id"];
            $view    = new View_Blog($this);
            if (count($_GET) > 0 && $blog_id) {
                $blog       = Blog::getById($blog_id);
                $view->blog = $blog;
            }
            $this->view->viewObject = $view;
        }
        //加载在线编辑器的语句要放在:$this->view->viewObject[如果有这一句]之后。
        $this->loadJs("js/edit.js");
        $this->load_onlineditor("blog_content");
    }
    /**
     * 删除博客
     */
    public function delete()
    {
        $blog_id = $this->data["blog_id"];
        $blog    = new Blog();
        $blog->setId($blog_id);
        $blog->delete();
        foreach ($blog->comments() as $comment) {
            $comment->delete();
        }
        $this->redirect("blog", "display", $this->data);
    }
}

/**
 *  Blog表示层对象
 */
class View_Blog extends ViewObject
{
    public $blog;
    public $blogs;
    public $countBlogs;
    public function count_comments($blog_id)
    {
        return Comment::count("blog_id=" . $blog_id);
    }
}
