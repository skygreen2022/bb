<?php
require_once "jssdk.php";
// 微信公众号APPID和APPSECRET
$jssdk = new JSSDK("微信公众号APPID", "微信公众号APPSECRET");
$signPackage = $jssdk->GetSignPackage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title></title>
</head>
<body>
  
</body>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script>
  /*
   * 注意: 
   * 1. 所有的JS接口只能在公众号绑定的域名下调用，公众号开发者需要先登录微信公众平台进入“公众号设置”的“功能设置”里填写“JS接口安全域名”。
   * 2. 如果发现在 Android 不能分享自定义内容，请到官网下载最新的包覆盖安装，Android 自定义分享接口需升级至 6.0.2.58 版本及以上。
   * 3. 常见问题及完整 JS-SDK 文档地址: http://mp.weixin.qq.com/wiki/7/aaa137b55fb2e0456bf8dd9148dd613f.html
   *
   * 开发中遇到问题详见文档“附录5-常见错误及解决办法”解决，如仍未能解决可通过以下渠道反馈: 
   * 邮箱地址: weixin-open@qq.com
   * 邮件主题: 【微信JS-SDK反馈】具体问题
   * 邮件内容说明: 用简明的语言描述问题所在，并交代清楚遇到该问题的场景，可附上截屏图片，微信团队会尽快处理你的反馈。
   */
  wx.config({
    debug: true,
    appId: '<?php echo $signPackage["appId"];?>',
    timestamp: <?php echo $signPackage["timestamp"];?>,
    nonceStr: '<?php echo $signPackage["nonceStr"];?>',
    signature: '<?php echo $signPackage["signature"];?>',
    jsApiList: [
      // 所有要调用的 API 都要加到这个列表中
        'chooseImage', 'scanQRCode'
    ]
  });
  wx.ready(function () {
    // 在这里调用 API
    wx.scanQRCode({
        needResult: 1, // 默认为0，扫描结果由微信处理，1则直接返回扫描结果，
        scanType: ["qrCode"], // 可以指定扫二维码还是一维码，默认二者都有
        success: function(res) {
            // console.log("======");
            var result = res.resultStr;
            // onfire.fire('uploadQrCodeAnswer', result);
            window.open('https://www.baidu.com/');
             // 当needResult 为 1 时，扫码返回的结果

        },
        fail: function (res) {
            alert(" l am  fail!"+JSON.stringify(res));
        },
        complete: function (res) {
            // alert("l am complete!"+res);
        }
    });

  });
</script>
</html>
