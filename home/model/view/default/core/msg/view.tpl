{extends file="$template_dir/layout/normal/layout.tpl"}
{block name=body}
<div class="block">
    <div><h1>查看消息</h1></div>
    <table class="viewdoblock">
        <tr class="entry"><td colspan="2" class="v_g_t"><h3>¶ <span>基本信息</span></h3></td></tr>
        <tr class="entry"><th class="head">标识</th><td class="content">{$msg.msg_id}</td></tr>
        <tr class="entry"><th class="head">发送者</th><td class="content">{$msg.senderId}</td></tr>
        <tr class="entry"><th class="head">接收者</th><td class="content">{$msg.receiverId}</td></tr>
        <tr class="entry"><th class="head">发送者名称</th><td class="content">{$msg.senderName}</td></tr>
        <tr class="entry"><th class="head">接收者名称</th><td class="content">{$msg.receiverName}</td></tr>
        <tr class="entry"><th class="head">发送内容</th><td class="content">{$msg.content}</td></tr>
        <tr class="entry"><th class="head">消息状态</th><td class="content">{$msg.statusShow}</td></tr>
        <tr class="entry v_g_b"><td colspan="2" class="v_g_t"><h3>¶ <span>其他信息</span></h3></td></tr>
        <tr class="entry"><th class="head">标识</th><td class="content">{$msg.msg_id}</td></tr>
        <tr class="entry"><th class="head">提交时间</th><td class="content">{$msg.commitTime|date_format:"%Y-%m-%d %H:%M"}</td></tr>
        <tr class="entry"><th class="head">更新时间</th><td class="content">{$msg.updateTime|date_format:"%Y-%m-%d %H:%M"}</td></tr>
    </table>
    <div class="footer" align="center"><my:a href='{$url_base}index.php?go=model.msg.lists&amp;pageNo={$smarty.get.pageNo|default:"1"}'>返回列表</my:a><my:a href='{$url_base}index.php?go=model.msg.edit&amp;id={$msg.msg_id}&amp;pageNo={$smarty.get.pageNo|default:"1"}'>修改消息</my:a></div>
</div>
{/block}