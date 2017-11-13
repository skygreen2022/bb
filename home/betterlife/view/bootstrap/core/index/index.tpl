{extends file="$templateDir/layout/normal/layout.tpl"}
{block name=body}
<body class="index">
    <nav class="navbar navbar-inverse navbar-fixed-top">
      <div class="navbar-container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="#">
            <i class="glyphicon glyphicon-grain"></i> Betterlife Front UI
          </a>
        </div>
        <div id="navbar" class="collapse navbar-collapse">
          <ul class="nav navbar-nav navbar-right">
            <li><a href="#">首页</a></li>
            <li><a href="html/blog/list.html">读书</a></li>
            <li><a href="#movie">电影</a></li>
            <li><a href="#music">音乐</a></li>
            <li><a href="#dev">研发</a></li>
            <li class="dropdown">
              <a href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                其它
                <span class="caret"></span>
              </a>
              <ul class="dropdown-menu" aria-labelledby="dLabel">
                <li><a href="#ebuy">购物</a></li>
                <li><a href="#topic">话题</a></li>
                <li><a href="html/model/preview.html">幽默</a></li>
              </ul>
            </li>
            <li><a href="{$url_base}index.php?go=betterlife.auth.login"><span class="glyphicon glyphicon-log-in"></span>登录</a></li>
            <li class="search-toggle"><a href="#"><span><span class="menu-search-text">搜索</span><span class="glyphicon glyphicon-search" aria-hidden="true"></span></span></a></li>
          </ul>
        </div>
        <div id="searchform-header" class="hidden">
          <div id="searchform-header-inner">
            <form method="get" action="" class="header-searchform">
              <input type="search" class="form-control" name="s" autocomplete="off" autofocus="autofocus" placeholder="搜索">
            </form>
            <span id="searchform-header-close" class="glyphicon glyphicon-remove search-toggle"></span>
          </div>
        </div>
      </div>
    </nav>

    <div id="main-content-container" class="container-fluid">
      <div class="page" id="page1">
        <div class="container section-header-container">
          <span class="bb-icon">B</span>
          <p class="lead">Betterlife Front UI is the most popular front-end framework for developing responsive, web first projects on the web browser.</p>
          <p class="lead"><a href="https://github.com/skygreen2001/betterlife.front/archive/master.zip" target="_blank" class="btn btn-outline-inverse btn-lg">下载 Betterlife Front UI</a></p>
          <p class="version">版本 v1.0.0</p>
        </div>
        <div class="starfield"></div>
      </div>


      <div class="section page" id="page2">
          <div class="container section-container">
              <h2 class="title slogan">I'm <span>BB</span>, 每一天<font class="flag">只 · 为 · 更 · 好</font></h2>
          </div>
      </div>

      <div class="section page darker" id="page3">
        <div class="page-over-header text-center">
          <div class="slogan-top">Betterlife Front UI</div>
          <div class="title slogan-bottom">
            Action → Better
            <div>Just Do It</div>
          </div>
        </div>
        <div class="container content-head">
          <div class="container page-detail">
            <h2>最佳方案设计</h2>
            <i class="icon-quote-left"></i>
            <p style="display:block;" data-id="1">
              专用于移动APP的html5 UI界面，可发布成原生应用。<br>
              也可用于html5 web页面；可嵌入微信；手机端优先。<br>
              实现框架底层采用: <br>
                  <span> - [ jQuery + Bootstrap3 Css Only ] </span>
                  <span> - [ jQuery + PureCss ] </span>
                  <span> - [ AngularJS + jQuery WeUI ] </span>
            </p>
            <p data-id="2">
              Html5开发生成Native原生应用[iOS,Andriod]<br>
                  <span> - `AngularJS`</span>
                  <span> [ Mobile Angular UI + jQuery WeUI ]</span>
                  <span> - `Angular` </span>
                  <span> [ Angular + Ionic ]</span>
                  <span> - `React Native` </span>
            </p>
            <p data-id="3">
              专用于Web开发的html5自适应界面，可用于pc电脑端<br>
              也可嵌入原生应用；可嵌入微信；Pc Web端优先。<br>
              实现框架底层采用: <br>
                  <span> - [ Jquery + Bootstrap3 ] </span>
                  <span> - [ AngularJS + Semantic-UI ] </span>
                  <span> - [ jQuery + Layui ] </span>
            </p>
            <i class="icon-quote-right"></i>
          </div>
          <div class="row">
            <div class="col-md-4 active" data-id="1">
              <div>
                <i class="fa fa-desktop" aria-hidden="true"></i>
                <span>Web自适应界面</span>
                <span>可用于PC端</span>
              </div>
            </div>
            <div class="col-md-4" data-id="2">
              <div>
                <i class="fa fa-weixin" aria-hidden="true"></i>
                <span>可嵌入微信</span>
                <span>PC Web端优先</span>
              </div>
            </div>
            <div class="col-md-4" data-id="3">
              <div>
                <i class="glyphicon glyphicon-phone" aria-hidden="true"></i>
                <span>手机原生应用</span>
                <span>内嵌html5页面</span>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="section page" id="page4">
          <div class="container section-container">
              <h2 class="title">四大前端框架 完美快速开发<font>四大前端框架始终坚持在技术创新中前行，引领行业重新定义前端开发时尚潮流</font></h2>
              <div class="content-box" id="page-four-framework">
                  <div class="content-list">
                      <div class="content-list-bg">
                          <div class="content-num"><span class="content-num-digital" id="countE1">65</span> %</div>
                          <div class="content-list-txt-bg"></div>
                          <div class="content-list-txt">
                            jQuery是一个高效、精简、功能丰富的 JavaScript 工具库。它的语法设计使得许多操作变得容易，如操作文档对象（document）、选择文档对象模型（DOM）元素、创建动画效果、处理事件、以及开发Ajax程序。<br><br>
                            全球前10,000个访问最高的网站中有65%使用了jQuery，是目前最受欢迎的JavaScript库。它是开源软件，使用MIT许可证授权。
                          </div>
                          <img src="https://lorempixel.com/900/500?r=1">
                          <div class="content-list-pop"></div>
                      </div>
                      <p>Jquery</p>
                  </div>
                  <div class="content-list">
                      <div class="content-list-bg">
                          <div class="content-num"><span class="content-num-digital" id="countE2">105,000</span> 次</div>
                          <div class="content-list-txt-bg"></div>
                          <div class="content-list-txt">
                            Bootstrap 是最受欢迎的 HTML、CSS 和 JS 框架，用于开发响应式布局、移动设备优先的 WEB 项目。<br><br>
                            GitHub上面被标记为“Starred”次数排名第二最多的项目。Starred次数超过105,000，而分支次数超过了47,000次。
                          </div>
                          <img src="https://lorempixel.com/900/500?r=2">
                          <div class="content-list-pop"></div>
                      </div>
                      <p>Bootstrap</p>
                  </div>
                  <div class="content-list">
                      <div class="content-list-bg">
                          <div class="content-num"><span class="content-num-digital" id="countE3">56.3</span> k</div>
                          <div class="content-list-txt-bg"></div>
                          <div class="content-list-txt">
                            AngularJS是一款开源JavaScript库，由Google维护，用来协助单一页面应用程序运行的。它的目标是通过MVC模式（MVC）功能增强基于浏览器的应用，使开发和测试变得更加容易。<br><br>
                            Angular是用于构建移动应用和桌面Web应用的开发平台。一套框架，多种平台，同时适用手机与桌面。
                          </div>
                          <img src="https://lorempixel.com/900/500?r=3">
                          <div class="content-list-pop"></div>
                      </div>
                      <p>Angular</p>
                  </div>
                  <div class="content-list">
                      <div class="content-list-bg">
                          <div class="content-num"><span class="content-num-digital" id="countE4">70,319</span> <i class="glyphicon glyphicon-star"></i></div>
                          <div class="content-list-txt-bg"></div>
                          <div class="content-list-txt">
                            React是一个为数据提供渲染为HTML视图的开源JavaScript库。React视图通常采用包含以自定义HTML标记规定的其他组件的组件渲染。React为程序员提供了一种子组件不能直接影响外层组件的模型，数据改变时对HTML文档的有效更新，和现代单页应用中组件之间干净的分离。<br><br>
                            React和React Native在GitHub上的加星数量是Facebook位列第二的开源项目，也是GitHub有史以来星标第九多的项目。
                          </div>
                          <img src="https://lorempixel.com/900/500?r=4">
                          <div class="content-list-pop"></div>
                      </div>
                      <p>React</p>
                  </div>
              </div>
          </div>
      </div>

      <footer>
        <div id="footer-inner" class="container clr">
          <div id="copyright" class="clr" role="contentinfo">© 2017-2020 Betterlife - All Rights Reserved.&nbsp;<a title="License" href="https://github.com/skygreen2001/betterlife.core/blob/master/LICENSE" target="_blank">License</a>&nbsp;| <a title="Help" href="https://github.com/skygreen2001/betterlife.front" target="_blank">Help</a></div>
        </div>
      </footer>
    </div>

    <script src="{$template_url}js/common/bower/bower.min.js"></script>
    <script src="{$template_url}js/common/common.jquery.min.js"></script>
    <script src="{$template_url}js/common/bower/index.bower.min.js"></script>
    <script src="{$template_url}js/index.js"></script>
</body>
{/block}
