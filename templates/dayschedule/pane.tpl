<link rel="stylesheet" type="text/css" href="[<$I2_ROOT>]www/extra-css/dayschedule.css" />
<script type="text/javascript" src="[<$I2_ROOT>]www/js/dayschedule.js"></script>
<script type='text/javascript'>
var currentdate = '[<$date>]';
</script>
<div class='dayschedule[<if isset($type)>] [<$type>][</if>]'>
	<div class='day-left' onclick='day_jump(-1)' title='Go back one day'>
	&#9668;
	</div>
	<div class='day-right' onclick='day_jump(1)' title='Go forward one day'>
	&#9658;
	</div>
	<div class='day-name'>
		[<$dayname>]
	</div>
	<div class='day-type [<$summaryid>]' title='[<$summaryid>]'>
		[<$summary>]
	</div>
	<div class='day-schedule'>
		[<include file='dayschedule/schedule.tpl' schedule=$schedule>]
	</div>


</div>
