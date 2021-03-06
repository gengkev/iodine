<script type="text/javascript">
<!--

function hideAll(select) {
	var options = select.options;
	var values = new Array();
	for (var i=0;i < options.length;i++) {
		if (!options.item(i).selected)
			values[values.length] = options.item(i).value;
	}
	hide(values);
}

function hide(value) {
	var divs = document.getElementsByTagName("div");
	for (var i=0;i<divs.length;i++) {
		if (divs.item(i).className.indexOf("podcast_freeresponse") == -1)
			continue;
		// podcast_freeresponse 9
		// 0123456789012345678
		var name = divs.item(i).className;
		name = name.substring(18);
		if (name.indexOf(" ") != -1) {
			name = name.substring(0, name.indexOf(" "));
		}
		if (value.indexOf(name) != -1) {
			divs.item(i).className = "podcast_freeresponse "+name+" hidden";
		} else {
			divs.item(i).className = "podcast_freeresponse "+name;
		}
	}
}
// -->
</script>
<div id="podcasts_header">
<a href="[<$I2_ROOT>]podcasts/home">Podcasts Home</a><br /><br />

Show responses by: (use Ctrl-click to select multiple responses) <br />
<select onchange="hideAll(this);" size="5" multiple="multiple">
	<option value="9" selected="selected">9th graders</option>
	<option value="10" selected="selected">10th graders</option>
	<option value="11" selected="selected">11th graders</option>
	<option value="12" selected="selected">12th graders</option>
	<option value="staff" selected="selected">Teachers</option>
</select>
</div>
<h4>[<$podcast->name>]</h4><br />

[<$question>]<br /><br />

[<foreach from=$votes item=vote>]
<div class="podcast_freeresponse [<$vote.numgrade>]"><span>[<$vote.grade>]</span>[<$vote.vote>]</div>
[</foreach>]
