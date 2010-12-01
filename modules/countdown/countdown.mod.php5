<?php
Class Countdown implements Module
{
	//you know what? I'm not even going to bother to phpdoc all the blank methods. sue me, derek.

	function display_pane($i){ return false;}
	function init_pane() { return false; }
	function init_cli() {return false;}
	function init_mobile() {return false;}
	function display_mobile($i) {return false;}
	function display_cli($disp) {return false;}//I'm actually gonna do this one later
	
	function get_name() {return "countdown";}

	function init_box()
	{
		return "COUNTDOWN!";
	}
	function display_box($disp)
	{
		$grad=1308094200;//unix timestamp for graduation date. will eventually put this in the config.ini file
		$diff=$grad-time();
		$years=floor($diff/31556926);
		$diff=$diff%31556926;
		$days=floor($diff/86400);
		$diff=$diff%86400;
		$hours=floor($diff/3600);
		$diff=$diff%3600;
		$mins=floor($diff/60);
		$secs=$diff%60;

		$underclass=0;
		$user = new User();
		$class=$user->grade;
		if($class!=12) $underclass=1;

		$template_args['years']=$years;
		$template_args['days']=$days;
		$template_args['hours']=$hours;
		$template_args['mins']=$mins;
		$template_args['secs']=$secs;
		$template_args['underclass']=$underclass;
		$disp->disp('countdown.tpl',$template_args);
	}
}
?>
