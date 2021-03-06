<?php

/**
 * -----------| 控制器:角色 |-----------
 * @category Betterlife
 * @package web.model.action
 * @author skygreen skygreen2001@gmail.com
 */
class Action_Role extends ActionModel
{
    /**
     * 角色列表
     */
    public function lists()
    {
        if ($this->isDataHave(TagPageService::$linkUrl_pageFlag)) {
            $nowpage = $this->data[TagPageService::$linkUrl_pageFlag];
        } else {
            $nowpage = 1;
        }
        $count = Role::count();
        $this->view->countRoles = $count;
        $roles = null;
        if ($count > 0) {
            $bb_page = TagPageService::init($nowpage, $count);
            $roles = Role::queryPage($bb_page->getStartPoint(), $bb_page->getEndPoint());
        }
        $this->view->set("roles", $roles);
    }
    /**
     * 查看角色
     */
    public function view()
    {
        $roleId = $this->data["id"];
        $role   = Role::getById($roleId);
        $this->view->set("role", $role);
    }
    /**
     * 编辑角色
     */
    public function edit()
    {
        if (!empty($_POST)) {
            $role = $this->model->Role;
            $id         = $role->getId();
            $isRedirect = true;
            if (!empty($id)) {
                $role->update();
            } else {
                $id = $role->save();
            }
            $roleFunctions = $this->data["functions_id"];
            Rolefunctions::saveDeleteRelateions("role_id", $id, "functions_id", $roleFunctions);
            if ($isRedirect) {
                $this->redirect("role", "view", "id=$id");
                exit;
            }
        }
        $roleId = $this->data["id"];
        $role   = Role::getById($roleId);
        $this->view->set("role", $role);
    }
    /**
     * 删除角色
     */
    public function delete()
    {
        $roleId = $this->data["id"];
        $isDelete = Role::deleteByID($roleId);
        $this->redirect("role", "lists", $this->data);
    }
}
