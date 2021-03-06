<?php

/**
 * -----------| 控制器:通知 |-----------
 * @category Betterlife
 * @package web.model.action
 * @author skygreen skygreen2001@gmail.com
 */
class Action_Notice extends ActionModel
{
    /**
     * 通知列表
     */
    public function lists()
    {
        if ($this->isDataHave(TagPageService::$linkUrl_pageFlag)) {
            $nowpage = $this->data[TagPageService::$linkUrl_pageFlag];
        } else {
            $nowpage = 1;
        }
        $count = Notice::count();
        $this->view->countNotices = $count;
        $notices = null;
        if ($count > 0) {
            $bb_page = TagPageService::init($nowpage, $count);
            $notices = Notice::queryPage($bb_page->getStartPoint(), $bb_page->getEndPoint());
        }
        $this->view->set("notices", $notices);
    }
    /**
     * 查看通知
     */
    public function view()
    {
        $noticeId = $this->data["id"];
        $notice   = Notice::getById($noticeId);
        $this->view->set("notice", $notice);
    }
    /**
     * 编辑通知
     */
    public function edit()
    {
        if (!empty($_POST)) {
            $notice = $this->model->Notice;
            $id         = $notice->getId();
            $isRedirect = true;
            if (!empty($id)) {
                $notice->update();
            } else {
                $id = $notice->save();
            }
            if ($isRedirect) {
                $this->redirect("notice", "view", "id=$id");
                exit;
            }
        }
        $noticeId = $this->data["id"];
        $notice   = Notice::getById($noticeId);
        $this->view->set("notice", $notice);
        //加载在线编辑器的语句要放在:$this->view->viewObject[如果有这一句]之后。
        $this->load_onlineditor('notice_content');
    }
    /**
     * 删除通知
     */
    public function delete()
    {
        $noticeId = $this->data["id"];
        $isDelete = Notice::deleteByID($noticeId);
        $this->redirect("notice", "lists", $this->data);
    }
}
