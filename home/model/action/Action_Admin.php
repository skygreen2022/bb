<?php

/**
 * -----------| 控制器:系统管理人员 |-----------
 * @category Betterlife
 * @package web.model.action
 * @author skygreen skygreen2001@gmail.com
 */
class Action_Admin extends ActionModel
{
    /**
     * 系统管理人员列表
     */
    public function lists()
    {
        if ($this->isDataHave(TagPageService::$linkUrl_pageFlag)) {
            $nowpage = $this->data[TagPageService::$linkUrl_pageFlag];
        } else {
            $nowpage = 1;
        }
        $count = Admin::count();
        $this->view->countAdmins = $count;
        $admins = null;
        if ($count > 0) {
            $bb_page = TagPageService::init($nowpage, $count);
            $admins = Admin::queryPage($bb_page->getStartPoint(), $bb_page->getEndPoint());
        }
        $this->view->set("admins", $admins);
    }
    /**
     * 查看系统管理人员
     */
    public function view()
    {
        $adminId = $this->data["id"];
        $admin   = Admin::getById($adminId);
        $this->view->set("admin", $admin);
    }
    /**
     * 编辑系统管理人员
     */
    public function edit()
    {
        if (!empty($_POST)) {
            $admin = $this->model->Admin;
            $id         = $admin->getId();
            $isRedirect = true;
            if (!empty($id)) {
                $admin->update();
            } else {
                $id = $admin->save();
            }
            if ($isRedirect) {
                $this->redirect("admin", "view", "id=$id");
                exit;
            }
        }
        $adminId = $this->data["id"];
        $admin   = Admin::getById($adminId);
        $this->view->set("admin", $admin);
        $departments = Department::get("", "department_id asc");
        $this->view->set("departments", $departments);
    }
    /**
     * 删除系统管理人员
     */
    public function delete()
    {
        $adminId = $this->data["id"];
        $isDelete = Admin::deleteByID($adminId);
        $this->redirect("admin", "lists", $this->data);
    }
}
