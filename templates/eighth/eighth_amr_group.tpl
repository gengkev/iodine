[<include file="eighth/eighth_header.tpl">]
<form name="theform" action="[<$I2_ROOT>]eighth/amr_group/add_member/gid/[<$gid>]" method="post">
	Student ID: <input type="text" name="uid"> <input type="submit" value="Add">
</form>
<script language="javascript" type="text/javascript">
	<!--
		document.theform.uid.focus();
	// -->
</script>
[<if count($members) > 0>]
<table cellspacing="0" style="border: 0px; margin: 0px; padding: 0px;">
	<tr>
		<th style="padding: 0px 5px; text-align: left;">Name</th>
		<th style="padding: 0px 5px; text-align: left;">Student ID</th>
		<th style="padding: 0px 5px; text-align: left;">Grade</th>
		<td>&nbsp;</td>
	</tr>
[<foreach from=$members item='member'>]
	<tr>
		<td style="padding: 0px 5px;">[<$member->name_comma>]</td>
		<td style="padding: 0px 5px;">[<$member->uid>]</td>
		<td style="padding: 0px 5px;">[<$member->grade>]</td>
		<td style="padding: 0px 5px;"><a href="[<$I2_ROOT>]eighth/amr_group/remove_member/gid/[<$gid>]/uid/[<$member->uid>]">Remove</a></td>
	</tr>
[</foreach>]
	<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td style="padding: 0px 5px;"><a href="[<$I2_ROOT>]eighth/amr_group/remove_all/gid/[<$gid>]">Remove all</a></td></tr>
</table>
[</if>]
