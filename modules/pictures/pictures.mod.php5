<?php

class Pictures implements Module {

	function display_box($disp) {
	}
	
	function display_pane($disp) {
		global $I2_ARGS, $I2_LDAP;
		Display::stop_display();
		$user = new User($I2_ARGS[1]);
		if($photo = $user->preferredPhoto) {
			header("Content-type: image/jpeg");
			echo $photo;
		} else {
			header("Content-type: image/png");
			readfile(i2config_get('root_path', '/var/www/iodine/', 'core') . 'www/pics/bomb.png');
		}
	}
	
	function get_name() {
		return "Pictures";
	}

	function init_box() {
		return FALSE;
	}

	function init_pane() {
		return "";
	}
}
?>
