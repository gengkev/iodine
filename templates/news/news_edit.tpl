<script type="text/javascript" src="[<$I2_ROOT>]www/js/news_groups.js"></script>
[<if isset($edited)>]
Your news post has been changed.<br />
<a href="[<$I2_ROOT>]news">Back to news</a><br />
[</if>]
<form action="[<$I2_SELF>]" method="POST">
 <input type="hidden" name="edit_form" value="1" />
 <table cellpadding="0" width="100%">
 <tr><td width="20%">Title:</td><td><input type="text" name="edit_title" value="[<$newsitem->title>]" size="30" /></td></tr>
 <tr><td>Expiration date:</td><td><input type="text" name="edit_expire" size="30" value="[<$newsitem->expire>]"/></td></tr>
 <tr><td width="20%">Visible:</td><td><input type="checkbox" name="edit_visible"[<if $newsitem->visible>]checked="checked"[</if>] /></td></tr>
 </table>
 <table id="groups_table" cellpadding="0" width="100%">
  <tr>
   <td width="20%">Groups:</td>
   <td width="1%">
    [<if count($newsitem->groups) == 0>]
    <select id="groups" class="groups_list" name="add_groups[]">
     [<foreach from=$groups item=group>]
      <option value="[<$group->gid>]">[<$group->name>]</option>
     [</foreach>]
    </select>
    [<else>]
    <select id="groups" class="groups_list" name="add_groups[]">
     [<foreach from=$groups item=group>]
      <option value="[<$group->gid>]"[<if $group->gid == $newsitem->groups[0]->gid>] selected[</if>]>[<$group->name>]</option>
     [</foreach>]
    </select>
    [</if>]
   </td>
   <td>&nbsp;</td>
  </tr>
  <tr>
   <td>&nbsp;</td>
   <td><a href="#" onclick="news_addGroup(); return false">Add another group</a></td>
   <td>&nbsp;</td>
  </tr>
 </table>
 <script type="text/javascript">
  [<section name=i loop=$newsitem->groups start=1>]
   news_addGroup([<$newsitem->groups[i]->gid>]);
  [</section>]
 </script>
 Text: <br />
 <textarea name="edit_text" cols="80" rows="15">[<$newsitem->text|escape:"html">]</textarea><br />
 <input type="submit" value="Submit" name="submit" />
</form>
