<script type="text/javascript" src="[<$I2_ROOT>]www/js/polls.js"></script>
<a href="[<$I2_ROOT>]polls">Polls Home</a><br /><br />

<form method="post" action="[<$I2_ROOT>]polls/add" class="boxform">
Name: <input type="text" name="name" value="" maxlength="128" /><br />
Start date/time:<input type="text" name="startdt" value="[<$smarty.now|date_format:"%Y-%m-%d %H-%M-%S">]" /><br />
End date/time:<input type="text" name="enddt" value="[<$smarty.now|date_format:"%Y-%m-%d %H-%M-%S">]" /><br />
<input type="checkbox" name="visible" /> Visible<br />
<table id="polls_groups_table" cellpadding="0">
<thead>
  <tr>
    <td></td>
    <th>Group</th>
    <th>Vote?</th>
    <th>Modify?</th>
    <th>View results?</th>
  </tr>
</thead>
<tbody><tr>
    <td>Groups:</td>
    <td>admin_polls<select id="polls_groups">
[<foreach from=$groups item=group>]
      <option value="[<$group->gid>]">[<$group->name>]</option>
[</foreach>]
    </select></td>
    <td><input type="checkbox" disabled="disabled" checked="checked" /></td>
    <td><input type="checkbox" disabled="disabled" checked="checked" /></td>
    <td><input type="checkbox" disabled="disabled" checked="checked" /></td>
  </tr><tr>
    <td><input type="hidden" name="groups[]" value="0" /></td>
    <td><select class="groups_list" name="group_gids[0]">
 [<foreach from=$groups item=group>]
      <option value="[<$group->gid>]" [<if $group->name == 'all'>]SELECTED[</if>]>[<$group->name>]</option>
 [</foreach>]
    </select></td>
    <td><input type="checkbox" name="vote[0]" /></td>
    <td><input type="checkbox" name="modify[0]" /></td>
    <td><input type="checkbox" name="results[0]" /></td>
    <td><a onclick="polls_deleteGroup(event)" href="">remove</a></td>
  </tr><tr>
    <td></td>
    <!-- We should have a non-JS interface, but this is difficult to do at this point in time. -->
    <td><a href="" onclick="polls_addGroup(event)">Add another group</a></td>
    <td></td>
  </tr></tbody>
</table>
Introduction:<br />
<textarea rows="2" cols="50" name="intro"></textarea><br />
<input type="submit" value="Create and start adding questions" name="submit" />
</form>
