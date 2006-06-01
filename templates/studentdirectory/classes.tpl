Sections of <b>[<$classes.0.class->name>]</b>: <br /><br />

<table>
	<thead>
		<th>Period</th>
		<th>Teacher</th>
	</thead>
	<tbody>
[<foreach from=$classes item="class">]
	<tr class="[<cycle values="c1,c2">]">
		<td class="directory-table"><a href="[<$I2_ROOT>]studentdirectory/class/[<$class.class->sectionid>]">[<$class.class->period>]</a></td>
		<td class="directory-table"><a href="[<$I2_ROOT>]studentdirectory/info/[<$class.class->teacher->uid>]">[<$class.class->teacher->name_comma>]</a></td>
	</tr>
[</foreach>]
	</tbody>
</table>
