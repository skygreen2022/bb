{extends file="$template_dir/layout/normal/layout.tpl"}
{block name=body}
<div class="block">
    <div><h1>查看通知</h1></div>
    <table class="viewdoblock">
        <tr class="entry"><td colspan="2" class="v_g_t"><h3>¶ <span>基本信息</span></h3></td></tr>
        <tr class="entry"><th class="head">编号</th><td class="content">{$notice.notice_id}</td></tr>
        <tr class="entry"><th class="head">通知分类</th><td class="content">{$notice.noticeType}</td></tr>
        <tr class="entry"><th class="head">标题</th><td class="content">{$notice.title}</td></tr>
        <tr class="entry"><th class="head">通知内容</th><td class="content">{$notice.notice_content}</td></tr>
        <tr class="entry v_g_b"><td colspan="2" class="v_g_t"><h3>¶ <span>其他信息</span></h3></td></tr>
        <tr class="entry"><th class="head">编号</th><td class="content">{$notice.notice_id}</td></tr>
        <tr class="entry"><th class="head">提交时间</th><td class="content">{$notice.commitTime|date_format:"%Y-%m-%d %H:%M"}</td></tr>
        <tr class="entry"><th class="head">更新时间</th><td class="content">{$notice.updateTime|date_format:"%Y-%m-%d %H:%M"}</td></tr>
    </table>
    <div class="footer" align="center"><my:a href='{$url_base}index.php?go=model.notice.lists&amp;pageNo={$smarty.get.pageNo|default:"1"}'>返回列表</my:a><my:a href='{$url_base}index.php?go=model.notice.edit&amp;id={$notice.notice_id}&amp;pageNo={$smarty.get.pageNo|default:"1"}'>修改通知</my:a></div>
</div>
{/block}