<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>

	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="description" content="Story Tracker is an application for fiction writers to manage character and plot development effectively." />
	<meta name="keywords" content="character development, story development, plot development, tools for writers, tools for authors" />
	<meta name="owner" content="Nicholas Pickering" />
	<meta name="author" content="Nicholas Pickering" />
	<meta http-equiv="charset" content="ISO-8859-1" />
	<meta http-equiv="content-language" content="english" />
	<meta name="rating" content="general" />
	
	<title><?=SITE_NAME?><? TemplateSet::begin ('title')?><? TemplateSet::end ()?></title>

	<link href="/css/tabmin.css" rel="stylesheet" type="text/css" />
	<link href="/css/global.css" rel="stylesheet" type="text/css" />
	<link href="/css/AlertSet.css" rel="stylesheet" type="text/css" /> 

	<!-- jQuery && jQuery UI libraries -->
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
    <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>
    <link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/themes/smoothness/jquery-ui.min.css" />

    <!-- Fancybox -->
    <script type="text/javascript" src="/js/fancybox/lib/jquery.mousewheel-3.0.6.pack.js"></script>
    <link rel="stylesheet" href="/js/fancybox/source/jquery.fancybox.css?v=2.1.5" type="text/css" media="screen" />
    <script type="text/javascript" src="/js/fancybox/source/jquery.fancybox.pack.js?v=2.1.5"></script>


    <!-- Google Analytics -->
    <script src="/js/google-analytics.js" type="text/javascript"></script>

	<!-- StoryTracker libraries -->
	<script src="/js/util.js" type="text/javascript"></script>
	<script src="/js/ajax.js" type="text/javascript"></script>
	<script src="/js/json2.js" type="text/javascript"></script>
	<script src="/js/AlertSet.js" type="text/javascript"></script>
	<script src="/js/storytracker.js" type="text/javascript"></script>
    <script src="/js/sendmail.js" type="text/javascript"></script>


    <!-- Page-Specific libraries -->
	<?php TemplateSet::begin('scripts'); ?><?php TemplateSet::end() ?>
    <script type="text/javascript">
        AlertSet.handleRedirectWithAlert();
    </script>

    <script>
        (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
            (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
            m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
        })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

        ga('create', 'UA-49764204-1', 'storytracker.net');
        ga('send', 'pageview');

    </script>
	
</head>

<body>

    <!-- Facebook Like Button Code -->
    <div id="fb-root"></div>
    <script>(function(d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) return;
        js = d.createElement(s); js.id = id;
        js.src = "//connect.facebook.net/en_US/all.js#xfbml=1";
        fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));</script>

	<div class="header">
		<?php TemplateSet::begin('header');?>
			<?php include('template/header.php'); ?>
		<?php TemplateSet::end(); ?>
	</div><!-- header -->
	
	<div class="foot">
        <div class="help">
            <?php
                if( isset($currentUser) )
                {
                    ?><a style="color: black; "href="javascript:;" onclick="showQuickForm('#bug-report')">Problems or Questions?</a><?php
                }
                else
                {
                    ?><a style="color: black;" href="/contact">Got a Question? Contact Us!</a><?php
                }
            ?>
        </div>
		<span class="copyright">&copy; <?=date('Y');?> StoryTracker</span>
		<?php TemplateSet::begin('foot');?><?php TemplateSet::end(); ?>
		
	</div><!-- foot -->

    <?php TemplateSet::begin('banner')?><?php TemplateSet::end() ?>
	
    <div class="wrapper">


        <div class="container">
            <div class="body">
                <?php TemplateSet::begin('body');?><?php TemplateSet::end(); ?>
            </div><!-- body -->
        </div><!-- container -->
    </div><!-- wrapper -->

    <?php
        if( !empty($currentUser) )
        {
            ?>
            <div id="bug-report" title="Bug Report / Feature Request" class="hidden-dialog">
                <?php include($_SERVER['DOCUMENT_ROOT'].'/tabmin/modules/users/bug-report.php'); ?>
            </div>
            <?php
        }
    ?>

    <script src="/js/storytracker_footer.js"></script>
    <?php TemplateSet::begin('scripts_footer'); ?><?php TemplateSet::end() ?>
</body>
</html>