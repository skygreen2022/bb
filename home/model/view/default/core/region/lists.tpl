{extends file="$template_dir/layout/normal/layout.tpl"}
{block name=body}
<div class="block">
    <div><h1>地区列表(共计{$countRegions}个)</h1></div>
    <table class="viewdoblock">
        <tr class="entry">
            <th class="header">标识</th>
            <th class="header">目录层级[全]</th>
            <th class="header">父地区标识</th>
            <th class="header">地区名称</th>
            <th class="header">地区类型</th>
            <th class="header">目录层级</th>
            <th class="header">操作</th>
        </tr>
        {foreach item=region from=$regions}
        <tr class="entry">
            <td class="content">{$region.region_id}</td>
            <td class="content">{$region.regionShowAll}</td>
            <td class="content">{$region.parent}</td>
            <td class="content">{$region.region_name}</td>
            <td class="content">{$region.region_typeShow}</td>
            <td class="content">{$region.level}</td>
            <td class="btnCol"><my:a href="{$url_base}index.php?go=model.region.view&amp;id={$region.region_id}&amp;pageNo={$smarty.get.pageNo|default:"1"}">查看</my:a>|<my:a href="{$url_base}index.php?go=model.region.edit&amp;id={$region.region_id}&amp;pageNo={$smarty.get.pageNo|default:"1"}">修改</my:a>|<my:a href="{$url_base}index.php?go=model.region.delete&amp;id={$region.region_id}&amp;pageNo={$smarty.get.pageNo|default:"1"}">删除</my:a></td>
        </tr>
        {/foreach}
    </table>
    <div class="page-control-bar" align="center"><my:page src='{$url_base}index.php?go=model.region.lists' /></div>
    <div class="footer" align="center">
        <my:a href='{$url_base}index.php?go=model.region.edit&amp;pageNo={$smarty.get.pageNo|default:"1"}'>新建</my:a><my:a href='{$url_base}index.php?go=model.index.index'>返回首页</my:a>
    </div>
</div>
{/block}