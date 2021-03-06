<?php

/**
 * -----------| 辅助工具类:自动生成代码 |-----------
 *
 * 自动折叠列表清单，可以更清晰看到生成代码的主干部分
 *
 * @category Betterlife
 * @package core.autocode
 * @author skygreen skygreen2001@gmail.com
 */
class AutoCodeFoldHelper extends AutoCode
{
    /**
     * 列表折叠打开的功能准备工作
     */
    public static function foldEffectReady()
    {
        $htmlContent = <<<HTMLCONTENT
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html lang="zh-CN" xml:lang="zh-CN" xmlns="http://www.w3.org/1999/xhtml">
<style type="text/css">
    .hidden {
      color:#000;
      background-color:#FFF;
    }
    #hidden_div div[id^='Content_']{
        margin-left: 32px;
    }
</style>
<script type="text/javascript">
<!--展开函数-->
function show_showdiv() {
    for (var i=0;i<=53;i++) {
        if (document.getElementById("Content_"+i))document.getElementById("Content_"+i).style.display='block';
    }
}
<!--收起函数-->
function hidden_hiddendiv() {
    for (var i=1;i<=5;i++) {
        if (document.getElementById("Content_"+i))document.getElementById("Content_"+i).style.display='block';
    }
    for (var i=11;i<=53;i++) {
        if (document.getElementById("Content_"+i))document.getElementById("Content_"+i).style.display='none';
    }
}
</script>
<span>&nbsp;</span>
<a href="javascript:show_showdiv();" style="padding-left: 16px;"><span id="_strHref" class="hidden">全部展开+</span></a>|<a href="javascript:hidden_hiddendiv();"><span id="_strSpan" class="hidden">全部收起-</span></a>
HTMLCONTENT;
        return $htmlContent;
    }

    /**
     * 通用的折叠说明
     * @param mixed $eleId
     */
    public static function foldEffectCommon($eleId)
    {
        return '<a href="javascript:" onClick="(document.getElementById(\'' . $eleId . '\').style.display=(document.getElementById(\'' . $eleId . '\').style.display==\'none\')?\'\':\'none\');">';
    }

    /**
     * 折叠前半部分:生成实体数据对象类
     */
    public static function foldbeforedomain()
    {
        $htmlContent  = '<div id="hidden_div" style="display:block; padding-left: 16px;">
                          <a href="javascript:" onclick="(document.getElementById(\'Content_1\').style.display=(document.getElementById(\'Content_1\').style.display==\'none\')?\'\':\'none\');">';
        $htmlContent .= "    <font color='#77cc6d'>&nbsp;&nbsp;[" . str_repeat("-", 40) . "生成实体数据对象类:start" . str_repeat("-", 40) . "]</font>
                          </a>";
        $htmlContent .= '  <div id="Content_1" style="display:block;">';
        $htmlContent .= '    <a href="javascript:" onclick="(document.getElementById(\'Content_11\').style.display=(document.getElementById(\'Content_11\').style.display==\'none\')?\'\':\'none\');"><font color="#237319">生成实体数据对象↓</font></a>';
        return $htmlContent;
    }

    /**
     * 折叠后半部分:生成实体数据对象类
     */
    public static function foldafterdomain()
    {

        $htmlContent  = '    <a class="after_link" href="javascript:" onClick="document.getElementById(\'Content_1\').style.display=(document.getElementById(\'Content_1\').style.display==\'none\')?\'\':\'none\';">';
        $htmlContent .= "      <font color='#7b7b7b'>[" . str_repeat("-", 40) . "生成实体数据对象类:&nbsp;&nbsp;end" . str_repeat("-", 40) . "]</font>
                            </a>";
        $htmlContent .= "  </div><br/>";
        return $htmlContent;
    }

    /**
     * 折叠前半部分:生成提供服务类
     */
    public static function foldbeforeservice()
    {
        $htmlContent  = '  <a href="javascript:" onClick="(document.getElementById(\'Content_2\').style.display=(document.getElementById(\'Content_2\').style.display==\'none\')?\'\':\'none\')">';
        $htmlContent .= "    <font color='#77cc6d'>&nbsp;&nbsp;[" . str_repeat("-", 36) . "生成提供服务类[前端Service类]:start" . str_repeat("-", 36) . "]</font>
                          </a>";
        $htmlContent .= '  <div id="Content_2" style="display:block;">';
        return $htmlContent;
    }

    /**
     * 折叠后半部分:生成提供服务类
     */
    public static function foldafterservice()
    {
        $htmlContent  = '  <a class="after_link" href="javascript:" onClick="document.getElementById(\'Content_2\').style.display=(document.getElementById(\'Content_2\').style.display==\'none\')?\'\':\'none\'">';
        $htmlContent .= "    <font color='#7b7b7b'>[" . str_repeat("-", 36) . "生成提供服务类[前端Service类]:&nbsp;&nbsp;end" . str_repeat("-", 36) . "]</font>
                          </a>";
        $htmlContent .= "  </div><br/>";
        return $htmlContent;
    }

    /**
     * 折叠前半部分:生成Action类[增删改查模板、前端和后台]
     */
    public static function foldbeforeaction()
    {
        $htmlContent  = '  <a href="javascript:" onClick="(document.getElementById(\'Content_3\').style.display=(document.getElementById(\'Content_3\').style.display==\'none\')?\'\':\'none\')">';
        $htmlContent .= "    <font color='#77cc6d'>&nbsp;&nbsp;[" . str_repeat("-", 32) . "生成Action类[增删改查模板、前端和后台]:start" . str_repeat("-", 32) . "]</font>
                          </a>";
        $htmlContent .= '  <div id="Content_3" style="display:block;">';
        return $htmlContent;
    }


    /**
     * 折叠前半部分:生成Action类[生成前端Action，继承基本Action]
     */
    public static function foldbeforeaction0()
    {
        $htmlContent  = '    <a href="javascript:" onClick="(document.getElementById(\'Content_31\').style.display=(document.getElementById(\'Content_31\').style.display==\'none\')?\'\':\'none\');"><font color="#237319">生成前端Action，继承基本Action↓</font></a>';
        $htmlContent .= '    <div id="Content_31" style="display:none;">';
        return $htmlContent;
    }

    /**
     * 折叠前半部分:生成Action类[生成标准的增删改查模板Action，继承基本Action:]
     */
    public static function foldbeforeaction1()
    {
        $htmlContent  = '    <a href="javascript:" onClick="(document.getElementById(\'Content_32\').style.display=(document.getElementById(\'Content_32\').style.display==\'none\')?\'\':\'none\');"><font color="#237319">生成标准的增删改查模板Action，继承基本Action↓</font></a>';
        $htmlContent .= '    <div id="Content_32" style="display:none;">';
        return $htmlContent;
    }

    /**
     * 折叠前半部分:生成Action类[生成后台Action，继承基本Action:]
     */
    public static function foldbeforeaction2()
    {
        $htmlContent  = '    <a href="javascript:" onClick="(document.getElementById(\'Content_33\').style.display=(document.getElementById(\'Content_33\').style.display==\'none\')?\'\':\'none\');"><font color="#237319">生成后台Action，继承基本Action↓</font></a>';
        $htmlContent .= '    <div id="Content_33" style="display:none;">';
        return $htmlContent;
    }

    /**
     * 折叠后半部分:生成Action类[增删改查模板、前端和后台]
     */
    public static function foldafteraction()
    {
        $htmlContent  = '    <a class="after_link" href="javascript:" onClick="document.getElementById(\'Content_3\').style.display=(document.getElementById(\'Content_3\').style.display==\'none\')?\'\':\'none\'">';
        $htmlContent .= "     <font color='#7b7b7b'>[" . str_repeat("-", 32) . "生成Action类[增删改查模板、前端和后台]:&nbsp;&nbsp;end" . str_repeat("-", 32) . "]</font>
                            </a>";
        $htmlContent .= "  </div><br/>";
        return $htmlContent;
    }

    /**
     * 折叠前半部分:生成前端表示层
     */
    public static function foldbeforeviewdefault()
    {
        $htmlContent  = '  <a href="javascript:" onClick="(document.getElementById(\'Content_4\').style.display=(document.getElementById(\'Content_4\').style.display==\'none\')?\'\':\'none\')">';
        $htmlContent .= "    <font color='#77cc6d'>&nbsp;&nbsp;[" . str_repeat("-", 36) . "生成增删改查模板、前端表示层:start" . str_repeat("-", 36) . "]</font>
                          </a>";
        $htmlContent .= '  <div id="Content_4" style="display:block;">';
        return $htmlContent;
    }

    /**
     * 折叠后半部分:生成前端表示层
     */
    public static function foldafterviewdefault()
    {
        $htmlContent  = '    <a class="after_link" href="javascript:" onClick="document.getElementById(\'Content_4\').style.display=(document.getElementById(\'Content_4\').style.display==\'none\')?\'\':\'none\'">';
        $htmlContent .= "      <font color='#7b7b7b'>[" . str_repeat("-", 36) . "生成增删改查模板、前端表示层:&nbsp;&nbsp;end" . str_repeat("-", 36) . "]</font>
                            </a>";
        $htmlContent .= "  </div><br/>";
        return $htmlContent;
    }
}
