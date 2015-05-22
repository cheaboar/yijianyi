<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <title>一家依-职位列表</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="format-detection" content="telephone=no">
    <meta name="renderer" content="webkit">
    <meta http-equiv="Cache-Control" content="no-siteapp"/>
    <script src="http://ttsstatic.qiniudn.com/jquery/jquery-2.1.1.min.js"></script>

    <script type="text/javascript" src="/Public/assets/js/jquery.masonry.min.js"></script>
    <link rel="alternate icon" type="image/png" href="/Public/assets/img/logo.png">
    <link rel="stylesheet" href="/Public/assets/css/amazeui.min.css"/>
    <link rel="stylesheet" href="/Public/assets/css/style.css"/>

</head>
<body>
<div class="job-list-container">
    <div class="job-list-header">职位</div>
    <div class="job-list-content">
        <?php if(is_array($data)): $i = 0; $__LIST__ = $data;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><div class="job-list-line">
                <div class="job-list-title"><?php echo ($vo["job"]); ?></div>
                <div class="job-list-info">
                    <div class="job-list-need">工资：<?php echo ($vo["salary"]); ?></div>
                </div>
                <div class="job-list-info">
                    <div class="job-list-need">招聘人数：<?php echo ($vo["number"]); ?>人</div>
                </div>
                <div class="job-list-info">
                    <div class="job-list-need">发布时间:<?php echo ($vo["time"]); ?></div>
                </div>
                <div class="job-list-info">
                    <div class="job-list-need">职位描述:<?php echo ($vo["describe"]); ?></div>
                </div>

                <div class="job-list-detail-btn">
                    <button class="am-btn am-btn-success am-round"><a href="/admin/job/introduction/pid/<?php echo ($vo["id"]); ?>/id/<?php echo session('authed');?>">查看详细信息</a></button>
                </div>
            </div><?php endforeach; endif; else: echo "" ;endif; ?>



    </div>
    <div class="job-list-footer">

    </div>
</div>
<div>
  <div style="margin:0 0 20px 0"></div>
  <p style="text-align:center">© 2015 版权所有 一家依</p>
</div>
<div data-am-widget="navbar" class="am-navbar am-cf am-navbar-default "
     id="">
    <ul class="am-navbar-nav am-cf am-avg-sm-4">

        <li>
            <a href="/admin/index/yijiayi/id/<?php echo session('id');?>">
                <span class="am-icon-home"></span>
                <span class="am-navbar-label">首页</span>
            </a>
        </li>

        <li>
            <a href="/admin/job/index/id/<?php echo session('id');?>">
                <span class="am-icon-briefcase"></span>
                <span class="am-navbar-label">职位</span>
            </a>
        </li>

        <li>
            <a href="/admin/usercenter/user_center" class="">
                <span class="am-icon-user"></span>
                <span class="am-navbar-label">我</span>
            </a>
        </li>
    </ul>
</div>
</body>
<script>
    $current_url = window.location.pathname;
</script>
</html >
</body>
</html>