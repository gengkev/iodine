[<include file="eighth/header.tpl">]
[<if count($group->members) > 0>]
<table cellspacing="0" style="border: 0px; margin: 0px; padding: 0px;">
	<tr>
		<th style="padding: 0px 5px; text-align: left;">Name</th>
		<th style="padding: 0px 5px; text-align: left;">Student ID</th>
		<th style="padding: 0px 5px; text-align: left;">Grade</th>
		<td>&nbsp;</td>
	</tr>
[<foreach from=$group->members_obj item='member'>]
	<tr>
		<td style="padding: 0px 5px;">[<$member->name_comma>]</td>
		<td style="padding: 0px 5px; text-align: center;">[<$member->uid>]</td>
		<td style="padding: 0px 5px; text-align: center;">[<$member->grade>]</td>
		<td style="padding: 0px 5px;"><a href="[<$I2_ROOT>]eighth/amr_group/remove_member/gid/[<$group->gid>]/uid/[<$member->uid>]">Remove</a></td>
	</tr>
[</foreach>]
	<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td style="padding: 0px 5px;"><a href="[<$I2_ROOT>]eighth/amr_group/remove_all/gid/[<$group->gid>]">Remove all</a></td></tr>
</table>
[</if>]
<form name="theform" action="[<$I2_ROOT>]eighth/amr_group/add_member/gid/[<$group->gid>]" method="post">
	<fieldset style="width: 220px;">
		<legend>Member to add:</legend>
		First Name: <input type="text" name="fname" /><br />
		Last Name: <input type="text" name="lname" /><br />
		Student ID: <input type="text" name="uid" /><br />
		<input type="submit" value="Add" />
	</fieldset>
</form>
<script language="javascript" type="text/javascript">
	<!--
		document.theform.uid.focus();
	// -->
</script>