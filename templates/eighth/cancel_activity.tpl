[<include file="eighth/header.tpl">]
<br />Editing comments for <a href="[<$I2_ROOT>]eighth/cancel_activity/activity/bid/[<$activity->bid>]">[<$activity->name>]</a><br /><br/>
<form action="[<$I2_ROOT>]eighth/cancel_activity/update/bid/[<$activity->bid>]/aid/[<$activity->aid>]" method="post">
	Enter a comment: <input type="text" name="comment" value="[<$activity->comment>]" /><br />
	<span style="vertical-align: 200%;">Enter special info:</span> <textarea name="advertisement" rows="3" cols="30">[<$activity->advertisement>]</textarea><br />
	Cancelled: <input type="checkbox" name="cancelled"[<if $activity->cancelled >] checked="checked"[</if>] /><br /><br />
	<input type="submit" value="Update" />
</form>
