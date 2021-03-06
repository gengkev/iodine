<?php
/**
* @author Derek Morris
* @package modules
* @subpackage JS
* @filesource
* Supports theme-specific javascript
*/

/**
* @package modules
* @subpackage JS
* Supports theme-specific javascript
*/
// This is to allow for theme-specific javascript
// Note that most of this is copied from css.mod.php5
// Although it's not as powerful, or as complex
// But it should be faster, and the javascript for the
// styles isn't likely to be too complex anyway.
class JS extends Module {

	private $warnings = [];

	private $date;

	private $script_path;

	private $script_cache;

	private $current_style;

	/**
	* Required by the {@link Module} interface.
	*/
	function display_pane($disp) {

		header('Content-type: text/javascript');
		header("Last-Modified: {$this->gmdate}");
		// header('Cache-Control: public');
		header('Pragma:'); // Unset Pragma header
		// header('Expires:'); // Unset Expires header
		echo "/* Server-Cache: {$this->script_cache} */\n";
		echo "/* Client-Cached: {$this->date} */\n";

		$disp->clear_buffer();
		$text = file_get_contents($this->script_cache); // Put something here!!!
		if ($this->current_style != substr($text, -(strlen($this->current_style)))) {
			$this->recache();
			$text = file_get_contents($this->script_cache); // Put something here!!!
		}
		echo $text;

		Display::stop_display();

		exit;
	}

	/**
	* Required by the {@link Module} interface.
	*/
	function get_name() {
		return 'js';
	}

	/**
	* Required by the {@link Module} interface.
	*/
	function init_pane() {
		global $I2_ARGS, $I2_USER, $I2_FS_ROOT;

		if(count($I2_ARGS)>1) {
			$current_style = $I2_ARGS[1];
		} else {
			$current_style=$I2_USER->style;
		}

		// this forces a theme (e.g. for April Fools' Day)
		/*if($I2_USER->iodineUid != 'eighthOffice') {
			$current_style = 'msoffice';
		}*/

		if (ends_with($current_style, '.js')) {
			$current_style = substr($current_style, 0, strlen($current_style) - 3);
		}

		$this->current_style = $current_style;
		$this->script_path = $I2_FS_ROOT . 'javascriptstyles/' . $current_style . '.js';
		$cache_dir = i2config_get('cache_dir', NULL, 'core') . 'javascriptstyles/';
		if (!is_dir($cache_dir)) {
			mkdir($cache_dir, 0777, TRUE);
		}

		$script_cache = $cache_dir . $I2_USER->uid;

		$this->script_cache = $script_cache;

		//Recompile the cache if it's stale
		if (!file_exists($script_cache)) {
			// || (filemtime($script_cache) < filemtime($this->script_path))
			$this->recache();
		}

		$this->gmdate = gmdate('D, d M Y H:i:s', filemtime($script_cache)) . ' GMT';

		if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
			$if_modified_since = preg_replace('/;.*$/', '', $_SERVER['HTTP_IF_MODIFIED_SINCE']);
			if ($if_modified_since == $this->gmdate) {
				Display::stop_display();
				header('HTTP/1.0 304 Not Modified');
				exit;
			}
		}

		$date = date('D M j G:i:s T Y');
		$this->date = $date;

		return 'js';
	}

	function recache() {
		$date = date("D M j G:i:s T Y");
		$contents = "/* Server cache '$this->current_style' created on $date */\n";
		foreach ($this->warnings as $message) {
			$contents .= "/* WARNING: $message */\n";
		}
		$parser = new Display();
		$contents .= $parser->fetch($this->script_path,[],FALSE);
		$contents .= "\n/* End of file */\n";
		$contents .= "//$this->current_style";
		file_put_contents($this->script_cache,$contents);
	}

	public static function flush_cache(User $user) {
		$cache_dir = i2config_get('cache_dir', NULL, 'core') . 'javascriptstyles/';
		$style_cache = $cache_dir . $user->uid;
		if(is_file($style_cache))
			unlink($style_cache);
	}
}

?>
