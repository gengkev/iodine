preload();
function preload() {
}
[<include file="../javascriptstyles/includes/ibtoggle.js">]

[<include file="../javascriptstyles/includes/mobile.js">]
function page_init() {
	//shrinkBlurb();
	intrabox_upd();
	mobiletweak();
}
function intrabox_onmouseover(div_id) {
}
function intrabox_onmouseout(div_id) {
}
function menu_onmouseover() {
}
function menu_onmouseout() {
}
function menuitem_onmouseover(div_id,not_1,not_2,not_3,not_4,not_5) {
}
function menuitem_onmouseout(div_id) {
}
/*function shrinkBlurb() { // remove the date from the header blurb
	if(window.innerWidth < 575) {
		var blurb = document.getElementsByClassName("blurb")[0];
		blurb.innerHTML = blurb.innerHTML.replace(/Today is .*?\. /, "");
		window.removeEventListener("resize", shrinkBlurb);
	}
}
window.addEventListener("resize", shrinkBlurb, false);*/
