{extends file="$template_dir/layout/normal/layout.tpl"}
{block name=body}
<div class="block">
    <div><h1>用户收到通知列表(共计{$countUsernotices}个)</h1></div>
    <table class="viewdoblock">
        <tr class="entry">
            <th class="header">标识</th>
            <th class="header">用户</th>
            <th class="header">用户编号</th>
            <th class="header">通知</th>
            <th class="header">通知编号</th>
            <th class="header">操作</th>
        </tr>
        {foreach item=usernotice from=$usernotices}
        <tr class="entry">
            <td class="content">{$usernotice.usernotice_id}</td>
            <td class="content">{$usernotice.user.username}</td>
            <td class="content">{$usernotice.user_id}</td>
            <td class="content">{$usernotice.notice.noticeType}</td>
            <td class="content">{$usernotice.notice_id}</td>
            <td class="btnCol"><my:a href="{$url_base}index.php?go=model.usernotice.view&amp;id={$usernotice.usernotice_id}&amp;pageNo={$smarty.get.pageNo|default:"1"}">查看</my:a>|<my:a href="{$url_base}index.php?go=model.usernotice.edit&amp;id={$usernotice.usernotice_id}&amp;pageNo={$smarty.get.pageNo|default:"1"}">修改</my:a>|<my:a href="{$url_base}index.php?go=model.usernotice.delete&amp;id={$usernotice.usernotice_id}&amp;pageNo={$smarty.get.pageNo|default:"1"}">删除</my:a></td>
        </tr>
        {/foreach}
    </table>
    <div class="page-control-bar" align="center"><my:page src='{$url_base}index.php?go=model.usernotice.lists' /></div>
    <div class="footer" align="center">
        <my:a href='{$url_base}index.php?go=model.usernotice.edit&amp;pageNo={$smarty.get.pageNo|default:"1"}'>新建</my:a><my:a href='{$url_base}index.php?go=model.index.index'>返回首页</my:a>
    </div>
</div>
{/block}