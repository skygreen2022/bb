<?php

/**
 * -----------| 控制器:用户日志 |-----------
 * @category Betterlife
 * @package web.model.action
 * @author skygreen skygreen2001@gmail.com
 */
class Action_Loguser extends ActionModel
{
    /**
     * 用户日志列表
     */
    public function lists()
    {
        if ($this->isDataHave(TagPageService::$linkUrl_pageFlag)) {
            $nowpage = $this->data[TagPageService::$linkUrl_pageFlag];
        } else {
            $nowpage = 1;
        }
        $count = Loguser::count();
        $this->view->countLogusers = $count;
        $logusers = null;
        if ($count > 0) {
            $bb_page = TagPageService::init($nowpage, $count);
            $logusers = Loguser::queryPage($bb_page->getStartPoint(), $bb_page->getEndPoint());
        }
        $this->view->set("logusers", $logusers);
    }
    /**
     * 查看用户日志
     */
    public function view()
    {
        $loguserId = $this->data["id"];
        $loguser   = Loguser::getById($loguserId);
        $this->view->set("loguser", $loguser);
    }
    /**
     * 编辑用户日志
     */
    public function edit()
    {
        if (!empty($_POST)) {
            $loguser = $this->model->Loguser;
            $id         = $loguser->getId();
            $isRedirect = true;
            if (!empty($id)) {
                $loguser->update();
            } else {
                $id = $loguser->save();
            }
            if ($isRedirect) {
                $this->redirect("loguser", "view", "id=$id");
                exit;
            }
        }
        $loguserId = $this->data["id"];
        $loguser   = Loguser::getById($loguserId);
        $this->view->set("loguser", $loguser);
        $users = User::get("", "user_id asc");
        $this->view->set("users", $users);
        //加载在线编辑器的语句要放在:$this->view->viewObject[如果有这一句]之后。
        $this->load_onlineditor('log_content');
    }
    /**
     * 删除用户日志
     */
    public function delete()
    {
        $loguserId = $this->data["id"];
        $isDelete = Loguser::deleteByID($loguserId);
        $this->redirect("loguser", "lists", $this->data);
    }
}
