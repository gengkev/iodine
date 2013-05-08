week_click = function() {
	if(typeof weekd == 'undefined' || (typeof weekd != 'undefiend' && !weekd)) {
		weekd = true;
		$("#week_click").html(week_text);
		return week_show();
	} else {
		weekd = false;
		$("#week_click").html("View Week");
		return week_hide();
	}
}
week_show = function() {
	$('#subPane').css({'overflow-x': 'hidden'}).addClass('exp');
	$('#schedule').css({'float': 'left'});

	$("#schedule h2, #schedule p, #schedule div").hide();
	$("#schedule_t").show();
	$('#schedule_week').show();
	$('#week_b, #week_f').hide();
	u = week_base_url+'?week';
	/*
	if (window.location.search.indexOf('day=')!==-1) {
		u+= '&day=' + window.location.search.split('day=')[1];
	}*/
	function getMonday(d) {
		d = new Date(d);
		var day = d.getDay(),
		diff = d.getDate() - day + (day == 0 ? -6:1); // adjust when day is sunday
		return new Date(d.setDate(diff));
	}
	m = getMonday(new Date());
	dy = m.getFullYear();
	dm = m.getMonth()+1;
	if(dm <= 9) dm = '0'+dm;
	dd = m.getDate()+1;
	if(dd <= 9) dd = '0'+dd;
	d = parseInt(dy+''+dm+''+dd);
	u+= '&start='+(d-1);
	u+= '&end='+(d+4);
	$.get(u, {}, function(d) {
		//try {
			window.getd = d;
			weekdata = d.split('::START::');
			weekdata = weekdata[1].split('::END::')[0];
			$('#schedule_week').html(weekdata);
		//} catch(e) {
		//	$('#schedule_week').append('<p>An error occurred fetching schedules.</p>');
		//}
	}, 'text');
}
week_hide = function() {
	$("#subPane").css({'overflow-x': ''}).removeClass('exp');
	$("#schedule_week").hide();
	$("#schedule h2, #schedule p, #schedule div").show();
	$("#schedule_t, #week_b, #week_f").show();
}
