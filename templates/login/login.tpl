<!doctype html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="Description" content="The TJ Intranet allows students at the Thomas Jefferson High School for Science and Technology to sign up for activities, access files, and perform other tasks." />
	<meta name="keywords" content="TJHSST, TJ Intranet, Intranet2" />
	<meta name="robots" content="index, follow" />
	<meta name="author" content="The Intranet2 Development Team" />
	<link rel="canonical" href="[<$I2_ROOT>]" />
	<!-- zoom in mobile browsers -->
	<meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=1">
	<title>TJHSST Intranet2: Login</title>
	
	<link href="https://fonts.googleapis.com/css?family=Open+Sans:400italic,700italic,400,700&amp;subset=latin,latin-ext,cyrillic-ext,greek-ext,cyrillic,vietnamese,greek" rel="stylesheet" type="text/css" />
	<link rel="stylesheet" type="text/css" href="[<$I2_ROOT>]www/extra-css/i3-ui-light.css" />
	<link rel="stylesheet" type="text/css" href="[<$I2_ROOT>]www/extra-css/i3-login-default.css" />
	<link rel="stylesheet" type="text/css" href="[<$I2_ROOT>]www/extra-css/i3-login-light.css" />
	<link rel="stylesheet" type="text/css" href="[<$I2_ROOT>]www/extra-css/login-schedule.css" />
	<link rel="shortcut icon" href="[<$I2_ROOT>]www/favicon.ico" />
	<link rel="icon" href="[<$I2_ROOT>]www/favicon.ico" />
	<script type="text/javascript" src="[<$I2_ROOT>]www/js/login.js"></script>
	<script type="text/javascript">
	//Set some variables so that any script can use them.
	var i2root="[<$I2_ROOT>]";
	prep_init = function() {
		day_parsehash();
	}
	if(!!window.addEventListener) {
		window.addEventListener("load", prep_init, false);
	} else {
		window.onload = prep_init;
	}
	</script>
	[<if isset($bgjs)>]
	<script type="text/javascript" src="[<$I2_ROOT>][<$bgjs>]"></script>
	[</if>]
	<!--[if lt IE 7]>
	<script type="text/javascript">
	IE7_PNG_SUFFIX = ".png";
	</script>
	<script src="[<$I2_ROOT>]www/js/ie7/ie7-standard-p.js" type="text/javascript"></script>
	<![endif]-->
</head>
<body style="background-image: url('[<$I2_ROOT>][<$bg|escape>]')" onLoad="document.getElementById('login_username').focus()" class="login">
	<div class="pane" id="mainPane">
		<a id="logo" href="">Intranet</a>

		[<if isset($err)>]
		<div class="login_msg" id="login_failed">
			[<$err>]<br />
		</div>
		[<elseif $failed eq 1>]
		<div class="login_msg" id="login_failed">
			Your login[<if $uname>] as [<$uname|escape>][</if>] failed.  Maybe your password is incorrect?<br />
		</div>
		[<elseif $failed eq 2>]
		<div class="login_msg" id="login_failed">
			Your password and username were correct, but you don't appear to exist in our database.  If this is a mistake, please contact the intranetmaster about it.
		</div>
		[<elseif $failed>]
		<div class="login_msg" id="login_failed">
			An unidentified error has occurred.  Please contact the Intranetmaster and tell him you received this error message.
		</div>
		[<else>]
		<div class="login_msg" id="login_msg">
			Please type your username and password to log in.
		</div>
		[</if>]

		<br />
		<br />

		<form name="login_form" action="[<$I2_SELF|escape>]" method="post">
			[<$posts>]
			<label for="login_username">Username</label>
			<br />
			<input name="login_username" id="login_username" type="text" size="25" value="[<$uname|escape>]"/>
			<br />
			<br />
			<label for="login_password">Password</label>
			<input name="login_password" id="login_password" type="password" size="25" /><!--onkeydown="checkcl()" />-->
			<br />
			<br />
			<button type="submit">Login</button>
		</form>

		<br />
		<br />

		<div style="text-align:center;">
			<div id="verisign_box" class="box" title="Click to Verify - This site chose VeriSign SSL for secure confidential communications.">
				<script type="text/javascript" src="https://seal.verisign.com/getseal?host_name=iodine.tjhsst.edu&amp;size=S&amp;use_flash=NO&amp;use_transparent=YES&amp;lang=en"></script><br/>
			</div>
		</div>
	</div>
	<div class="pane" id="subPane">
		[<* the schedule *>]
		[<include file='bellschedule/common.tpl'>]
		<br /><br />
		<ul id="links">
			<li><a href="http://www.tjhsst.edu" target="_blank">TJHSST</a></li>
			<li><a href="https://webmail.tjhsst.edu" target="_blank">Mail</a></li>
			<li><a href="http://www.calendarwiz.com/calendars/calendar.php?crd=tjhsstcalendar" target="_blank">Calendar</a></li>
			<!--<li><a href="http://www.tjhsst.edu/studentlife/publications/tjtoday" target="_blank">tjTODAY</a></li>-->
		</ul>
		<br />
		[<*
		<form action="[<$I2_SELF|escape>]" method="get">
			<select name="background" onchange="this.form.bid.value=this.options[this.selectedIndex].id.split('bg')[1];this.form.submit()">
				<optgroup label="Change your background image:">
					<option value="random" id="bg0">Surprise me!</option>
				</optgroup>
				<optgroup>
					[<assign var=num value=0>]
					[<foreach from=$backgrounds item=b>]
						[<assign var=num value=$num+1>]
						[<assign var=name value="."|explode:$b>]
						<option value="[<$b|escape>]" id="bg[<$num>]">[<$name[0]|escape>]</option>
					[</foreach>]

				</optgroup>
			</select>
			<input type="hidden" name="bid" value="0">
			<noscript><input type="submit" value="Change Background" /></noscript>
		</form>
		<script type="text/javascript">
		parse_bgs();
		</script>
		*>]
	</div>

	[<* This doesn't work on recent versions of chrome because
	     it is not in the web store; so why advertise it? *>]
	<!--script type="text/javascript">
		chrome_app();

	</script-->

