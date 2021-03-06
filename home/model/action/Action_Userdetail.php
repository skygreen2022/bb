<?php

/**
 * -----------| 控制器:用户详细信息 |-----------
 * @category Betterlife
 * @package web.model.action
 * @author skygreen skygreen2001@gmail.com
 */
class Action_Userdetail extends ActionModel
{
    /**
     * 用户详细信息列表
     */
    public function lists()
    {
        if ($this->isDataHave(TagPageService::$linkUrl_pageFlag)) {
            $nowpage = $this->data[TagPageService::$linkUrl_pageFlag];
        } else {
            $nowpage = 1;
        }
        $count = Userdetail::count();
        $this->view->countUserdetails = $count;
        $userdetails = null;
        if ($count > 0) {
            $bb_page = TagPageService::init($nowpage, $count);
            $userdetails = Userdetail::queryPage($bb_page->getStartPoint(), $bb_page->getEndPoint());
        }
        $this->view->set("userdetails", $userdetails);
    }
    /**
     * 查看用户详细信息
     */
    public function view()
    {
        $userdetailId = $this->data["id"];
        $userdetail   = Userdetail::getById($userdetailId);
        $this->view->set("userdetail", $userdetail);
    }
    /**
     * 编辑用户详细信息
     */
    public function edit()
    {
        if (!empty($_POST)) {
            $userdetail = $this->model->Userdetail;
            $id         = $userdetail->getId();
            $isRedirect = true;
            if (!empty($_FILES) && !empty($_FILES["profile"]["name"])) {
                $result = $this->uploadImg($_FILES, "profile", "profile", "userdetail");
                if ($result && ($result['success'] == true)) {
                    if (array_key_exists('file_name', $result)) {
                        $userdetail->profile = $result['file_name'];
                    }
                } else {
                    $isRedirect = false;
                    $this->view->set("message", $result["msg"]);
                }
            }
            if (!empty($id)) {
                $userdetail->update();
            } else {
                $id = $userdetail->save();
            }
            if ($isRedirect) {
                $this->redirect("userdetail", "view", "id=$id");
                exit;
            }
        }
        $userdetailId = $this->data["id"];
        $userdetail   = Userdetail::getById($userdetailId);
        $this->view->set("userdetail", $userdetail);
        $users = User::get("", "user_id asc");
        $this->view->set("users", $users);
    }
    /**
     * 删除用户详细信息
     */
    public function delete()
    {
        $userdetailId = $this->data["id"];
        $isDelete = Userdetail::deleteByID($userdetailId);
        $this->redirect("userdetail", "lists", $this->data);
    }
}
