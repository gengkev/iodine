[<assign var=pid value=$podcast->pid>]
<script type="text/javascript" src="[<$I2_ROOT>]www/js/podcasts.js"></script>

<a href="[<$I2_ROOT>]podcasts">Podcasts Home</a><br /><br />

<form method="post" action="[<$I2_ROOT>]podcasts/edit/[<$podcast->pid>]" class="boxform">
<input type="hidden" name="podcast_edit_form" value="podcast" />
Name: <input type="text" name="name" value="[<$podcast->name>]" /><br />
Start date/time:<input type="text" name="startdt" value="[<$podcast->startdt>]" /><br />
End date/time:<input type="text" name="enddt" value="[<$podcast->enddt>]" /><br />
<input type="checkbox" name="visible" [<if $podcast->visible>]checked="checked" [</if>]/> Visible<br />
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
    </select>
    </td>
    <td><input type="checkbox" disabled="disabled" checked="checked" /></td>
    <td><input type="checkbox" disabled="disabled" checked="checked" /></td>
    <td><input type="checkbox" disabled="disabled" checked="checked" /></td>
  </tr><tr>
[<if count($podcast->groups) == 0>]
    <td><input type="hidden" name="groups[]" value="0" /></td>
    <td><select class="groups_list" name="group_gids[0]">
 [<foreach from=$groups item=group>]
      <option value="[<$group->gid>]">[<$group->name>]</option>
 [</foreach>]
    </select></td>
    <td><input type="checkbox" name="vote[0]" /></td>
    <td><input type="checkbox" name="modify[0]" /></td>
    <td><input type="checkbox" name="results[0]" /></td>
    <td><a onclick="podcasts_deleteGroup(event)" href="[<$I2_ROOT>]podcasts/edit/[<$pid>]/delg/-1">remove</a></td>
  </tr><tr>
[<else>]
 [<assign var='index' value=0 >]
 [<foreach from=$podcast->groups key=gid item=perms>]
    <td><input type="hidden" name="groups[]" value="[<$index>]" /></td>
    <td><select class="groups_list" name="group_gids[[<$index>]]">
  [<foreach from=$groups item=g>]
      <option value="[<$g->gid>]"[<if $g->gid == $gid>] selected="selected"[</if>]>[<$g->name>]</option>
  [</foreach>]
    </select></td>
    <td><input type="checkbox" name="vote[[<$index>]]" [<if $perms[0] == 1>]checked="checked"[</if>] /></td>
    <td><input type="checkbox" name="modify[[<$index>]]" [<if $perms[1] == 1>]checked="checked"[</if>] /></td>
    <td><input type="checkbox" name="results[[<$index>]]" [<if $perms[2] == 1>]checked="checked"[</if>] /></td>
    <td><a onclick="podcasts_deleteGroup(event)" href="[<$I2_ROOT>]podcasts/edit/[<$pid>]/delg/[<$gid>]">remove</a></td>
  </tr><tr>
  [<assign var='index' value='$index+1'>]

 [</foreach>]
[</if>]
  <td></td>
  <td><a href="[<$I2_ROOT>]podcasts/edit/[<$pid>]/addg" onclick="podcasts_addGroup(event)">Add another group</a></td>
  <td></td>
 </tr></tbody>
</table>
Introduction:<br />
<textarea rows="5" cols="50" name="intro">[<$podcast->introduction>]</textarea><br /><br />

Questions:<br />
<a onclick="addQuestion(event)" href="[<$I2_ROOT>]podcasts/edit/[<$pid>]/addq">Add a question</a><br />
<table id="podcast_question_list">
<thead>
  <tr><th>Id</th><th>Question</th><th>Type</th><th>Vote limit</th></tr>
</thead><tbody>
[<foreach from=$podcast->questions item=q>]
  <tr><th rowspan="2">[<$q->qid>]<input name="question[]" value="[<$q->qid>]" type="hidden" /></th>
  <td><input name="q_[<$q->qid>]_name" value="[<$q->question>]" /></td>
  <td><select name="q_[<$q->qid>]_type">
[<foreach from=$types item=val key=k>]
    <option value="[<$k>]" [<if $q->answertype == $k>]selected="selected"[</if>]>[<$val>]</option>
[</foreach>]
  </select></td>
  <td><input name="q_[<$q->qid>]_lim" maxlength="3" size="3" value="[<$q->maxvotes>]" /></td>
  <td><a href="[<$I2_ROOT>]podcasts/edit/[<$pid>]/delq/[<$q->qid>]"><img src="[<$I2_ROOT>]www/pics/podcastx.gif" onclick="deleteRow(event)" alt="" /></a></td>
  </tr><tr><td colspan="4">
    <ul><li>
      <a href="[<$I2_ROOT>]podcasts/edit/[<$pid>]/adda/[<$q->qid>]" onclick="addAnswer(event)">Add an answer choice</a>
    </li>[<foreach from=$q->answers item=ans key=aid>]<li>
      <input type="hidden" name="a_[<$q->qid>][]" value="[<$aid>]" />
      <a href="[<$I2_ROOT>]podcasts/edit/[<$pid>]/dela/[<$q->qid>]/[<$aid>]" onclick="deleteAnswer(event)">Delete</a>&nbsp;&nbsp;&nbsp;<input name="a_[<$q->qid>]_[<$aid>]" value="[<$ans|escape:'html'>]" /></textarea>
    </li>[</foreach>]</ul>
  </td>
  </tr>
[</foreach>]
</tbody>
</table>
<a onclick="addQuestion(event)" href="[<$I2_ROOT>]podcasts/edit/[<$pid>]/addq">Add a question</a><br /><br />
<input type="submit" value="Update" name="submit" />
</form>
