<script type="text/javascript" src="[<$I2_ROOT>]www/js/podcasts.js"></script>
<a href="[<$I2_ROOT>]podcasts">Podcasts Home</a><br /><br />

<form method="post" action="[<$I2_ROOT>]podcasts/add" class="boxform">
Name: <input type="text" name="name" value="" maxlength="128" /><br />
Start date/time:<input type="text" name="startdt" value="YYYY-MM-DD HH:MM:SS" /><br />
End date/time:<input type="text" name="enddt" value="YYYY-MM-DD HH:MM:SS" /><br />
<input type="checkbox" name="visible" /> Visible<br />
<table id="podcasts_groups_table" cellpadding="0">
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
    <td>admin_podcasts<select id="podcasts_groups">
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
      <option value="[<$group->gid>]">[<$group->name>]</option>
 [</foreach>]
    </select></td>
    <td><input type="checkbox" name="vote[0]" /></td>
    <td><input type="checkbox" name="modify[0]" /></td>
    <td><input type="checkbox" name="results[0]" /></td>
    <td><a onclick="podcasts_deleteGroup(event)" href="">remove</a></td>
  </tr><tr>
    <td></td>
    <!-- We should have a non-JS interface, but this is difficult to do at this point in time. -->
    <td><a href="" onclick="podcasts_addGroup(event)">Add another group</a></td>
    <td></td>
  </tr></tbody>
</table>
Introduction:<br />
<textarea rows="2" cols="50" name="intro"></textarea><br />
<input type="submit" value="Create and start adding questions" name="submit" />
</form>
