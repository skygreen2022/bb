<?php

/**
 * -----------| 控制器:功能信息 |-----------
 * @category Betterlife
 * @package web.model.action
 * @author skygreen skygreen2001@gmail.com
 */
class Action_Functions extends ActionModel
{
    /**
     * 功能信息列表
     */
    public function lists()
    {
        if ($this->isDataHave(TagPageService::$linkUrl_pageFlag)) {
            $nowpage = $this->data[TagPageService::$linkUrl_pageFlag];
        } else {
            $nowpage = 1;
        }
        $count = Functions::count();
        $this->view->countFunctionss = $count;
        $functionss = null;
        if ($count > 0) {
            $bb_page = TagPageService::init($nowpage, $count);
            $functionss = Functions::queryPage($bb_page->getStartPoint(), $bb_page->getEndPoint());
        }
        $this->view->set("functionss", $functionss);
    }
    /**
     * 查看功能信息
     */
    public function view()
    {
        $functionsId = $this->data["id"];
        $functions   = Functions::getById($functionsId);
        $this->view->set("functions", $functions);
    }
    /**
     * 编辑功能信息
     */
    public function edit()
    {
        if (!empty($_POST)) {
            $functions = $this->model->Functions;
            $id         = $functions->getId();
            $isRedirect = true;
            if (!empty($id)) {
                $functions->update();
            } else {
                $id = $functions->save();
            }
            if ($isRedirect) {
                $this->redirect("functions", "view", "id=$id");
                exit;
            }
        }
        $functionsId = $this->data["id"];
        $functions   = Functions::getById($functionsId);
        $this->view->set("functions", $functions);
    }
    /**
     * 删除功能信息
     */
    public function delete()
    {
        $functionsId = $this->data["id"];
        $isDelete = Functions::deleteByID($functionsId);
        $this->redirect("functions", "lists", $this->data);
    }
}
