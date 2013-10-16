<style type="text/css">
.dayschedule {
	width: 300px;
	text-align: center;
}
.day-name {
	font-size: 20px;
}
.day-type {
	font-size: 16px;
}
.day-type.anchor { color: black; }
.day-type.red { color: red; }
.day-type.blue { color: blue; }
.schedule-tbl {
	margin-left: auto;
	margin-right: auto;
}

.day-left {
	float: left;
}

.day-right {
	float: right;
}

.day-left, .day-right {
	font-size: 40px;
	opacity: 0.3;
}

.day-left:hover, .day-right:hover {
	opacity: 1;
	cursor: pointer;
}
</style>
<script type='text/javascript'>
var currentdate = '[<$date>]';
Date.prototype.yyyymmdd = function() {
   var yyyy = this.getFullYear().toString();
   var mm = (this.getMonth()+1).toString();
   var dd = this.getDate().toString();
   return yyyy + (mm[1]?mm:"0"+mm[0]) + (dd[1]?dd:"0"+dd[0]);
};
day_jump = function(days) {
	/*var qd = location.search.indexOf('date=') != -1 ? location.search.split('date=')[1] : null;*/
	var qd = currentdate;
	var dobj = (qd != null ? new Date(qd.substring(0,4), qd.substring(4,6), qd.substring(6,8)) : new Date());
	
	var newdobj = new Date();
	newdobj.setDate(dobj.getDate() + days);

	location.href = '?date=' + newdobj.yyyymmdd();
}
</script>
<div class='dayschedule'>
	<div class='day-left' onclick='day_jump(-1)'>
	&#9668;
	</div>
	<div class='day-right' onclick='day_jump(1)'>
	&#9658;
	</div>
	<div class='day-name'>
		[<$dayname>]
	</div>
	<div class='day-type [<$summaryid>]'>
		[<$summary>]
	</div>
	<div class='day-schedule'>
		[<include file='dayschedule/schedule.tpl' schedule=$schedule>]
	</div>


</div>
