[<include file="eighth/header.tpl">]
<script language="javascript" type="text/javascript" src="[<$I2_ROOT>]www/js/eighth_sch_activity.js"></script>
<div style="width: 100%; text-align: center; font-weight: bold; font-size: 18pt; margin-top: 10px;">
<select name="activity_list" style="font-size: 18pt; text-align: left;" onchange="location.href='[<$I2_ROOT>]eighth/sch_activity/view/aid/' + this.options[this.selectedIndex].value">
[<foreach from=$activities item="activity">]
	<option value="[<$activity->aid>]" [<if $act->aid == $activity->aid>]style="font-size: 18pt; font-weight: bold;" selected="selected"[<else>]style="font-size: 10pt; "[</if>]>[<$activity->aid>]: [<$activity->name_r>]</option>
[</foreach>]
</select>
<form name="aidform" action="[<$I2_ROOT>]eighth/sch_activity/view/" method="post">
AID: <input type="text" name="aid"/>
<script language="javascript" type="text/javascript">
	document.aidform.aid.focus();
</script>
</form>
</div>

<form name="activities" action="[<$I2_ROOT>]eighth/sch_activity/modify/aid/[<$act->aid>]" method="post">
<div id="eighth_room_pane">
	<input type="text" id="eighth_room_filter" autocomplete="off" onchange="filterList(value,'eighth_room_list');" onkeyup="filterList(value,'eighth_room_list');" onsearch="filterList(value,'eighth_room_list');"/>
	<select id="eighth_room_list" name="rooms" size="10" multiple="multiple" onchange="do_action('add_room', 0, this.options[this.selectedIndex]); void(this.selectedIndex=-1);">
[<foreach from=$rooms item='room'>]
		<option value="[<$room.rid>]">[<$room.name>]</option>
[</foreach>]
	</select><br />
	<span style="text-decoration: underline; cursor: pointer;" onclick="void(this.parentNode.style.display='none');">Close</span>
</div>
<div id="eighth_sponsor_pane">
	<input type="text" id="eighth_sponsor_filter" autocomplete="off" onchange="filterList2(value,'eighth_sponsor_list');" onkeyup="filterList2(value,'eighth_sponsor_list');" onsearch="filterList2(value,'eighth_sponsor_list');"/>
	<select id="eighth_sponsor_list" name="sponsors" size="10" multiple="multiple" onchange="do_action('add_sponsor', 0, this.options[this.selectedIndex]); void(this.selectedIndex=-1);">
[<foreach from=$sponsors item='sponsor'>]
	<option value="[<$sponsor.sid>]">[<$sponsor.name_comma>]</option>
[</foreach>]
	</select><br />
	<span style="text-decoration: underline; cursor: pointer;" onclick="void(this.parentNode.style.display='none');">Close</span>
</div>
<table cellspacing="0" style="border: 0px; padding: 0px; margin: 0px; width: 100%;">
	<tr>
		<td colspan="2">
			<input type="submit" value="Save" />
		</td>
		<td colspan="4">
		</td>
	</tr>
	<script type="text/javascript">
		blocks=[];
		[<foreach from=$block_activities item="activity">]
		blocks.push([[<$activity.block.date|date_format:"%w">],"[<$activity.block.block>]","check_[<$activity.block.bid>]"]);
		[</foreach>]
		function hideshow(el,ar) {
			el=document.getElementById(el);
			ar=document.getElementById(ar);
			if(el.style.display=='none') {
				el.style.display='block';
				ar.src="[<$I2_ROOT>]www/pics/uparrow.gif";
			} else {
				el.style.display='none';
				ar.src="[<$I2_ROOT>]www/pics/downarrow.gif";
			}
		}
		function select(day,block) {
			for(var n=0;n<blocks.length;n++) {
				if(blocks[n][0]==day && blocks[n][1]==block)
					document.getElementById(blocks[n][2]).checked="checked";
				//else
				//	document.getElementById(blocks[n][2]).checked=null;
			}
		}
	</script>
	<tr>
		<td style="width:  20px; text-align: left;"><input type="checkbox" name="selectall" onclick="CA();" /></td>
		<th style="padding: 5px; text-align: left; width: 120px;">Select All 
			<a onclick="hideshow('typelist','dropmenubutton')"><img id="dropmenubutton" alt="dropdown" src="https://iodine.tjhsst.edu/www/pics/downarrow.gif" /></a>
			<div id="typelist" class="mainbox" style="display:none;background-color:#ffffff; float:right; left:auto; right:auto;top: auto;padding:1px 4px">
				<a onclick="select(1,'B')">Monday B block</a><br />
				<a onclick="select(3,'A')">Wednesday A block</a><br />
				<a onclick="select(3,'B')">Wednesday B block</a><br />
				<a onclick="select(5,'A')">Friday A block</a><br />
				<a onclick="select(5,'B')">Friday B block</a>
			</div>
		</th>
		<th style="padding: 5px; text-align: left;">Room(s)</th>
		<td>&nbsp;</td>
		<th style="padding: 5px; text-align: left;">Sponsor(s)</th>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td style="width: 150px;"></td>
	</tr>
[<foreach from=$block_activities item="activity">]
	<tr class="[<cycle values="c1,c2">]">
		<td class="eighth_sch_activity_checkcell"><a name="[<$activity.block.bid>]"></a><input type="checkbox" name="modify[]" value="[<$activity.block.bid>]" id="check_[<$activity.block.bid>]" onclick="CCA(this);" [<if $activity.scheduled>]checked="checked"[</if>]/></td>
		<td class="eighth_sch_activity_datecell[<if !$activity.scheduled>]_unscheduled[</if>]">
			[<$activity.block.date|date_format:"%a">] [<$activity.block.block>], [<$activity.block.date|date_format:"%m/%d/%y">]
[<if $activity.scheduled>]
			<br /><a id="unschedule_[<$activity.block.bid>]" onclick="return do_action('unschedule', '[<$activity.block.bid>]');" href="#[<$activity.block.bid>]" class="eighth_sch_activity_unschedule">Unschedule</a>&nbsp;&nbsp;<a id="cancel_[<$activity.block.bid>]" onclick="return do_action('cancel', '[<$activity.block.bid>]');" href="#[<$activity.block.bid>]" class="eighth_sch_activity_cancel">[<if $activity.cancelled>]Uncancel[<else>]Cancel[</if>]</a>
[</if>]
		</td>
		<td class="eighth_sch_activity_listcell">
			<div id="div_room_list_[<$activity.block.bid>]" class="eighth_room_list">
[<if $activity.scheduled>]
	[<foreach from=$activity.rooms_obj item=room>]
				[<$room->name>] <a href="#[<$activity.block.bid>]" onclick="return do_action('remove_room', '[<$activity.block.bid>]', '[<$room->rid>]', event)">Remove</a><br />
	[</foreach>]
[</if>]
			</div>
			<input type="hidden" name="room_list[[<$activity.block.bid>]]" value="[<if $activity.scheduled>][<$activity.rooms>][</if>]" id="room_list_[<$activity.block.bid>]" />
		</td>
		<td style="text-align: left;">
			<a href="#[<$activity.block.bid>]" onclick="return do_action('view_rooms', '[<$activity.block.bid>]', new Array([<$activity.rooms_array>]), event);">Add Room</a><br />
		</td>
		<td class="eighth_sch_activity_listcell">
			<div id="div_sponsor_list_[<$activity.block.bid>]" class="eighth_sponsor_list">
[<if $activity.scheduled>]
	[<foreach from=$activity.sponsors_obj item=sponsor>]
				[<$sponsor->name_comma>] <a href="#[<$activity.block.bid>]" onclick="return do_action('remove_sponsor', '[<$activity.block.bid>]', '[<$sponsor->sid>]', event)">Remove</a><br />
	[</foreach>]
[</if>]
			</div>
			<input type="hidden" name="sponsor_list[[<$activity.block.bid>]]" value="[<if $activity.scheduled>][<$activity.sponsors>][</if>]" id="sponsor_list_[<$activity.block.bid>]" />
			<input type="hidden" id="activity_status_[<$activity.block.bid>]" name="activity_status[[<$activity.block.bid>]]" value="[<if $activity.scheduled && $activity.cancelled>]CANCELLED[<else>]SCHEDULED[</if>]" />
		</td>
		<td style="text-align: left;">
			<a href="#[<$activity.block.bid>]" onclick="return do_action('view_sponsors', '[<$activity.block.bid>]', new Array([<$activity.sponsors_array>]), event);">Add Sponsor</a><br />
		</td>
		<td style="padding: 5px;">
			<textarea name="comments[[<$activity.block.bid>]]" id="comment_[<$activity.block.bid>]" readonly="readonly" class="eighth_sch_activity_commentcell" rows="1">[<if isset($activity.comment) >][<$activity.comment|escape:"html">][</if>]</textarea>
		</td>
		<td style="text-align: center;"><img src="[<$I2_ROOT>]www/pics/eighth/notepad.gif" alt="Add Comment" title="Add Comment" onmousedown="show_comment_dialog(event, [<$activity.block.bid>])" class="eighth_sch_activity_comment"><a href="#[<$activity.block.bid>]" class="eighth_sch_activity_propagate" onclick="return do_action('propagate', [<$activity.block.bid>]);">&uarr;&nbsp;Propagate&nbsp;&darr;</a>
		</td>
		<td>
			<input type="submit" value="Save" />
		</td>
	</tr>
[</foreach>]
	<tr>
		<td colspan="2">
			<input type="submit" value="Save" />
		</td>
		<td colspan="4">
		</td>
	</tr>
</table><br />
</form>
<script language="javascript" type="text/javascript">
	var frm = document.activities;
	var unscheduled_blocks = new Array([<$unscheduled_blocks>]);
</script>
<script language="javascript" type="text/javascript">
	var select_activity = document.getElementById("eighth_room_list");
	var savedRoomList = select_activity.cloneNode(true);
	var select_activity = document.getElementById("eighth_sponsor_list");
	var savedSponsorList = select_activity.cloneNode(true);
</script>
