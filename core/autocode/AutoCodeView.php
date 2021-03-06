<?php

/**
 * -----------| 工具类:自动生成代码-前端默认的表示层 |-----------
 * @category Betterlife
 * @package core.autocode.view
 * @author skygreen skygreen2001@gmail.com
 */
class AutoCodeView extends AutoCode
{
    /**
     * 表示层生成定义的方式
     *
     * 0.生成前台所需的表示层页面。
     *
     * 1.生成标准的增删改查模板所需的表示层页面。
     *
     * 2.生成后台所需的表示层页面。
     *
     * @var int
     */
    public static $type;
    /**
     * View生成tpl所在的应用名称，默认同网站应用的名称
     * @var string
     */
    public static $appName;
    /**
     * 表示层所在的目录
     * @var string
     */
    public static $view_core;
    /**
     * 表示层完整的保存路径
     * @var string
     */
    public static $view_dir_full;
    /**
     * 设置必需的路径
     * @return void
     */
    public static function pathset()
    {
        switch (self::$type) {
            case EnumAutoCodeViewType::FRONT:
                self::$app_dir = Gc::$appName;
                if (empty(self::$appName)) {
                    self::$appName = Gc::$appName;
                }
                break;
            case EnumAutoCodeViewType::MODEL:
                self::$app_dir = "model";
                self::$appName = "model";
                break;
            case EnumAutoCodeViewType::ADMIN:
                self::$app_dir = "admin";
                self::$appName = "admin";
                break;
        }
        self::$view_dir_full = self::$save_dir . Gc::$module_root . DS . self::$app_dir . DS . ConfigF::VIEW_VIEW . DS . Gc::$self_theme_dir . DS . ConfigF::VIEW_CORE . DS;
    }

    /**
     * 自动生成代码-前端默认的表示层
     *
     * 示例如下:
     *
     *    1.array:array('bb_user_admin','bb_core_blog')
     *
     *    2.字符串:'bb_user_admin,bb_core_blog'
     *
     * @param array|string $table_names
     */
    public static function autoCode($table_names = "")
    {
        self::pathset();
        self::init();
        if (self::$isOutputCss) {
            self::$showReport .= UtilCss::form_css() . HH;
        }
        switch (self::$type) {
            case EnumAutoCodeViewType::FRONT:
                self::$showReport .= AutoCodeFoldHelper::foldEffectCommon("Content_41");
                self::$showReport .= '<font color="#237319">生成前台所需的表示层页面↓</font></a>';
                self::$showReport .= '<div id="Content_41" style="display:none;">';

                $link_view_dir     = "file:///" . str_replace("\\", "/", self::$view_dir_full);
                self::$showReport .= "<font color='#AAA'>存储路径:<a target='_blank' href='" . $link_view_dir . "'>" . self::$view_dir_full . "</a></font><br/><br/>";

                AutoCodeViewModel::createModelIndexFile($table_names);
                self::createFrontModelPages($table_names);
                self::$showReport .= "</div>" . BR;
                break;
            case EnumAutoCodeViewType::MODEL:
                self::$showReport .= AutoCodeFoldHelper::foldEffectCommon("Content_42");
                self::$showReport .= '<font color="#237319">生成标准的增删改查模板表示层页面↓</font></a>';
                self::$showReport .= '<div id="Content_42" style="display:none;">';

                $link_view_dir     = "file:///" . str_replace("\\", "/", self::$view_dir_full);
                self::$showReport .= "<font color='#AAA'>存储路径:<a target='_blank' href='" . $link_view_dir . "'>" . self::$view_dir_full . "</a></font><br/><br/>";

                AutoCodeViewModel::createModelIndexFile($table_names);
                $fieldInfos = self::fieldInfosByTable_names($table_names);
                foreach ($fieldInfos as $tablename => $fieldInfo) {
                    $tpl_listsContent = AutoCodeViewModel::tpl_lists($tablename, $fieldInfo);
                    $filename         = "lists" . ConfigF::SUFFIX_FILE_TPL;
                    $tplName          = self::saveTplDefineToDir($tablename, $tpl_listsContent, $filename);
                    self::$showReport .= "生成导出完成:$tablename => $tplName!<br/>";

                    $tpl_viewContent  = AutoCodeViewModel::tpl_view($tablename, $fieldInfo);
                    $filename         = "view" . ConfigF::SUFFIX_FILE_TPL;
                    $tplName          = self::saveTplDefineToDir($tablename, $tpl_viewContent, $filename);
                    self::$showReport .= "生成导出完成:$tablename => $tplName!<br/>";

                    $tpl_editContent  = AutoCodeViewModel::tpl_edit($tablename, $fieldInfo);
                    $filename         = "edit" . ConfigF::SUFFIX_FILE_TPL;
                    $tplName          = self::saveTplDefineToDir($tablename, $tpl_editContent, $filename);
                    self::$showReport .= "生成导出完成:$tablename => $tplName!<br/>";
                }
                self::$showReport .= "</div>" . BR;
                break;
            case EnumAutoCodeViewType::ADMIN:
                self::$showReport .= AutoCodeFoldHelper::foldEffectCommon("Content_43");
                self::$showReport .= '<font color="#237319">生成后台所需的表示层页面↓</font></a>';
                self::$showReport .= '<div id="Content_43" style="display:none;">';

                $link_view_dir     = "file:///" . str_replace("\\", "/", self::$view_dir_full);
                self::$showReport .= "<font color='#AAA'>存储路径:<a target='_blank' href='" . $link_view_dir . "'>" . self::$view_dir_full . "</a></font><br/><br/>";

                $fieldInfos = self::fieldInfosByTable_names($table_names);
                foreach ($fieldInfos as $tablename => $fieldInfo) {
                    $tpl_listsContent = AutoCodeViewAdmin::tpl_lists($tablename, $fieldInfo);
                    $filename         = "lists" . ConfigF::SUFFIX_FILE_TPL;
                    $tplName          = self::saveTplDefineToDir($tablename, $tpl_listsContent, $filename);
                    self::$showReport .= "生成导出完成:$tablename => $tplName!<br/>";

                    $tpl_viewContent  = AutoCodeViewAdmin::tpl_view($tablename, $fieldInfo);
                    $filename         = "view" . ConfigF::SUFFIX_FILE_TPL;
                    $tplName          = self::saveTplDefineToDir($tablename, $tpl_viewContent, $filename);
                    self::$showReport .= "生成导出完成:$tablename => $tplName!<br/>";

                    $tpl_editContent  = AutoCodeViewAdmin::tpl_edit($tablename, $fieldInfo);
                    $filename         = "edit" . ConfigF::SUFFIX_FILE_TPL;
                    $tplName          = self::saveTplDefineToDir($tablename, $tpl_editContent, $filename);
                    self::$showReport .= "生成导出完成:$tablename => $tplName!<br/>";

                    $tpl_editContent  = AutoCodeViewAdmin::js_core($tablename, $fieldInfo);
                    $jsName           = self::saveJsDefineToDir($tablename, $tpl_editContent);
                    self::$showReport .= "生成导出完成:$tablename => $jsName!<br/>";

                    $api_editContent  = AutoCodeAjax::api_web_admin($tablename, $fieldInfo);
                    $phpName          = AutoCodeAjax::saveApiWebDefineToDir($tablename, $api_editContent);
                    self::$showReport .= "生成导出完成:$tablename => $phpName!<br/>";
                    AutoCodeViewAdmin::save_layout();
                    AutoCodeAjax::save_select_web_admin($tablename);
                }
                self::$showReport .= "</div>" . BR;
                break;
        }
    }

    /**
     * 用户输入需求
     * @param $default_value 默认值
     */
    public static function UserInput($default_value = "", $title = "", $inputArr = null, $more_content = "")
    {
        $inputArr = array(
            EnumAutoCodeViewType::FRONT => "生成前台所需的表示层页面",
            EnumAutoCodeViewType::MODEL => "生成标准的增删改查模板所需的表示层页面",
            EnumAutoCodeViewType::ADMIN => "生成后台管理所需的表示层页面"
        );
        return parent::UserInput("一键生成表示层页面", $inputArr, $default_value);
    }

    /**
     * 将表列定义转换成表示层tpl文件定义的内容
     * @param string $contents 页面内容
     */
    public static function tableToViewTplDefine($contents)
    {
        $result = "{extends file=\"\$template_dir/layout/normal/layout.tpl\"}" . HH .
                  "{block name=body}" . HH .
                  "$contents" . HH .
                  "{/block}";
        return $result;
    }

    /**
     * 生成前台所需的表示层页面
     * @param array|string $table_names
     * 示例如下:
     *  1.array:array('bb_user_admin','bb_core_blog')
     *  2.字符串:'bb_user_admin,bb_core_blog'
     */
    private static function createFrontModelPages($table_names = "")
    {
        $fieldInfos = self::fieldInfosByTable_names($table_names);
        foreach ($fieldInfos as $tablename => $fieldInfo) {
            if (self::$type == EnumAutoCodeViewType::FRONT) {
                $classname = self::getClassname($tablename);
                if ($classname == "Admin") {
                    continue;
                }
            }
            $table_comment = self::tableCommentKey($tablename);
            $appname       = self::$appName;
            $instancename  = self::getInstancename($tablename);
            $link          = "    <div align=\"center\"><my:a href=\"{\$url_base}index.php?go={$appname}.{$instancename}.view\">查看</my:a>|<my:a href=\"{\$url_base}index.php?go={$appname}.{$instancename}.edit\">修改</my:a>";
            $back_index    = "    <my:a href='{\$url_base}index.php?go={$appname}.index.index'>返回首页</my:a></div>";
            $tpl_content   = self::tableToViewTplDefine("    <div><h1>" . $table_comment . "列表</h1></div><br/>" . HH . "{$link}<br/>" . HH . "{$back_index}");
            $filename      = "lists" . ConfigF::SUFFIX_FILE_TPL;
            $tplName       = self::saveTplDefineToDir($tablename, $tpl_content, $filename);
            self::$showReport .= "生成导出完成:$tablename => $tplName!<br/>";

            $link        = "     <div align=\"center\"><my:a href=\"{\$url_base}index.php?go={$appname}.{$instancename}.lists\">返回列表</my:a>";
            $tpl_content = self::tableToViewTplDefine("    <div><h1>查看" . $table_comment . "</h1></div><br/>" . HH . "{$link}<br/>" . HH . "{$back_index}");
            $filename    = "view" . ConfigF::SUFFIX_FILE_TPL;
            $tplName     = self::saveTplDefineToDir($tablename, $tpl_content, $filename);
            self::$showReport .= "生成导出完成:$tablename => $tplName!<br/>";

            $tpl_content = self::tableToViewTplDefine("    <div><h1>编辑" . $table_comment . "</h1></div><br/>" . HH . "{$link}<br/>" . HH . "{$back_index}");
            $filename    = "edit" . ConfigF::SUFFIX_FILE_TPL;
            $tplName     = self::saveTplDefineToDir($tablename, $tpl_content, $filename);
            self::$showReport .= "生成导出完成:$tablename => $tplName!<br/>";
        }
    }

    /**
     * 保存生成的tpl代码到指定命名规范的文件中
     * @param string $tablename 表名称
     * @param string $defineTplFileContent 生成的代码
     * @param string $filename 文件名称
     */
    private static function saveTplDefineToDir($tablename, $defineTplFileContent, $filename)
    {
        $package       = self::getInstancename($tablename);
        $dir           = self::$view_dir_full . $package . DS;
        $classname     = self::getClassname($tablename);
        $relative_path = str_replace(self::$save_dir, "", $dir . $filename);
        switch (self::$type) {
            case EnumAutoCodeViewType::FRONT:
                AutoCodePreviewReport::$view_front_files[$classname . $filename] = $relative_path;
                break;
            case EnumAutoCodeViewType::MODEL:
                AutoCodePreviewReport::$view_model_files[$classname . $filename] = $relative_path;
                break;
            case EnumAutoCodeViewType::ADMIN:
                AutoCodePreviewReport::$view_admin_files[$classname . $filename] = $relative_path;
                break;
        }
        return self::saveDefineToDir($dir, $filename, $defineTplFileContent);
    }

    /**
     * 保存生成的JS代码到指定命名规范的文件中
     * @param string $tablename 表名称
     * @param string $defineJsFileContent 生成的代码
     * @param string $filename 文件名称
     */
    private static function saveJsDefineToDir($tablename, $defineJsFileContent)
    {
        $dir           = dirname(self::$view_dir_full) . DS . "js" . DS . "core" . DS;
        $classname     = self::getClassname($tablename);
        $filename      = self::getInstancename($tablename) . ConfigF::SUFFIX_FILE_JS;
        $relative_path = str_replace(self::$save_dir, "", $dir . $filename);
        AutoCodePreviewReport::$js_admin_files[$classname . $filename] = $relative_path;
        return self::saveDefineToDir($dir, $filename, $defineJsFileContent);
    }
}
