[<if $usermail>]
<form method="post" class="boxform" action="[<$I2_ROOT>]news/request">
[</if>]
[<if isSet($mailed)>]
	[<if $mailed>]
		Your request has been submitted for approval.
	[<else>]
		There was a problem submitting your request. Please contact the Intranet Developers for assistance.	
	[</if>]
[<else>]
	Do you want to post an informational news article or announcement on Intranet? This page allows you to easily submit news for approval.<br />
	[<if $usermail>]
		We will follow up with you at <strong>[<$usermail>]</strong> if needed.  If this e-mail address is incorrect, please update your preferences, or feel free to send an e-mail directly to [<mailto address=$iodinemail encode=hex >].  If you send us an e-mail directly, don't forget to tell us who you are!<br />
		<br />
		<strong>Specifications for News Posts:</strong><br />
		To increase the chances that your post comes up quickly, please mind the following:<br />
		<ol>
		<li>Use correct English grammar, punctuation, and spelling. Posts get sent out to a few hundred people via email and are viewed several thousand times. Therefore, please be at least a little professional when requesting them.</li>
		<li>If you'd like us to attach a file to the post or put in an image, please leave a note to us in the note field. If you put a publicly-accessible URL there, we'll copy the file over to intranet's servers for hosting.</li>
		<li>If you have a link to an external website in your post, make sure that it can be accessed without having to register for that site. Facebook links that require you to have registered cannot be used for this reason, and may be omitted. This is due to improve compliance with the FCPS Network User Guidelines.</li>
		<li>If you are talking about an event in your post, please put the location and time of the event in your post body. Otherwise people will have no idea where or when it is.</li>
		<li>If there's a well-defined group, such as "The class of 2011", "All students students with ___ gender" that you'd like to limit your post to, add that as a note in the notes field. If we have this group in the system, then we'll put it up for that; otherwise, we'll email you to try to get it worked out.</li>
		<li>DO NOT make requests that we put up lost+found-type notices. Due to volume, we won't post these. Instead, go to Lost+Found in the school.</li>
		<li>Keep it SFW.</li>
		</ol>
		We reserve the power to edit requests at our discretion.<br />
		<br />
		Title:<br /><input type="text" name="submit_title" style="width:98%" /><br />
		Expiration Date (if left blank, we will attempt to choose one for you):<br /><input type="text" name="submit_expdate" style="width:98%" /><br />
		Contents:<br />
		<textarea name="submit_box" style="width:98%;height:150px"></textarea><br />
		Notes:<br />
		<textarea name="notes_box" style="width:98%;height:35px"></textarea><br />
		<input type="submit" value="Submit" name="submit_form" />
		</form>
	[<else>]
		<br />
		Oops...it looks like you didn't enter an e-mail address in your preferences.<br />
		To use the request box, it is required that you specify an e-mail address in your preferences so we can contact you about your request if needed.  However, if you would still like to send us a request without sharing your e-mail with all of TJ, feel free to send an e-mail directly to <a id="mailtolink" href="mailto:[<$iodinemail>]">[<$iodinemail>]</a>.  Don't forget to tell us who you are!
[</if>][</if>]
