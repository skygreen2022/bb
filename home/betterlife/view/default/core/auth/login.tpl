{extends file="$template_dir/layout/normal/layout.tpl"}
{block name=body}
    <form method="POST">
    <div class="login-container">
        <h2></h2>
        <div>
            <div class="modal">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3 class="modal-title"><span style="font-family: Arial">{$site_name}</span> 框架前台</h3>
                        </div>
                        <div class="modal-body">
                           <label class="login-label">用户名</label><input class="inputNormal inputLogin" type="text" name="username" /><br/><br/>
                           <label class="login-label">密&nbsp;码</label><input class="inputNormal inputLogin" type="password" name="password" />
                           <p class="message">{$message}</p>
                        </div>
                        <div class="modal-footer">
                            <input type="submit" name="Submit" value="登录" class="btnSubmit" />
                            <button type="button" class="btn btn-register inputNormal" onclick="javascript:window.location.href='{$url_base}index.php?go={$app_name}.auth.register'">注册</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div><div class="login-info">[测试帐户]用户名:admin,密码:admin<br/>[测试帐户]用户名:china,密码:iloveu</div></div>
    </div>
    </form>
{/block}
