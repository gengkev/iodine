<?php
/**
* Just contains the definition for the class {@link Display}.
* @author The Intranet 2 Development Team <intranet2@tjhsst.edu>
* @copyright 2005 The Intranet 2 Development Team
* @version $Id: display.class.php5,v 1.29 2005/07/11 07:06:32 adeason Exp $
* @since 1.0
* @package core
* @subpackage Display
* @filesource
* @todo We need some kind of design! (Eric)
*/

/**
* The display module for Iodine.
* @todo Implement a way for the main module to influence the page title, and
* title for the main content pane.
* @package core
* @subpackage Display
*/
class Display {

	/**
	* The Smarty object used to convert templates to HTML.
	*
	* @access private
	*/
	private $smarty;

	/**
	* The name of the module associated with this Display object.
	*
	* @access private
	*/
	private $my_module_name;

	/**
	* The output buffer.
	*
	* @access private
	*/
	private $buffer;
	
	/**
	* Whether to buffer output.
	*
	* @access private
	*/
	private $buffering = FALSE;

	/**
	* The core display object to get buffering data from.
	* @access private
	*/
	private static $core_display;
	
	/**
	* @access private
	*/
	private static $display_stopped = FALSE;

	/**
	* The template root directory, as specified in the configuration file.
	* This variable is just here to cache the info in the config file,
	* since it's referenced so much in Display.
	*/
	private static $tpl_root = NULL;
	
	/**
	* The Display class constructor.
	* 
	* @access public
	* @param string $module_name The name of the module this Display object applies to.
	*/
	public function __construct($module_name='core') {
		require_once('Smarty.class.php');
		$this->smarty = new Smarty;
		//$this->smarty->register_prefilter(array(&$this,'prefilter'));
		//$this->smarty->load_filter('pre','strip');
		//$this->smarty->register_postfilter(array(&$this,'postfilter'));
		//$this->smarty->register_outputfilter(array(&$this,'outputfilter'));
		$this->smarty->left_delimiter = '[<';
		$this->smarty->right_delimiter = '>]';
		$this->smarty->compile_dir = i2config_get('smarty_path','./','core');
		$this->my_module_name = $module_name;
		if ($module_name == 'core') {
			Display::$core_display = $this;
			self::$tpl_root = i2config_get('template_path','./','core');
		}
		$this->buffer = "";
	}
	
	/**
	* Displays the top bar.
	*
	* @todo Put a call to the User class in here instead of a mysql query.
	*/
	private function display_top_bar() {
		global $I2_SQL;

		$res = $I2_SQL->query('SELECT fname FROM user WHERE uid=%d;', $_SESSION['i2_uid'])->fetch_all_arrays(MYSQL_ASSOC);
		$this->disp('topbar.tpl', array('first_name' => $res[0]['fname']) );
	}

	/**
	* The main non-core executing loop of Iodine.
	*
	* This function basically displays everything and performs
	* pretty much all processing done outside of core.
	*
	* @param $module The name of the module to display in the main
	*                panel and give processing control to.
	*/
	public function display_loop($module) {
		global $I2_ERR;

		if (Display::$display_stopped) {
			return;
		}
		
		$this->global_header();
		$this->display_top_bar();
		$mod = '';

			
		try {	
			/*
			** Display the main pane.
			*/
			$disp = new Display($module);
			
			eval('$mod = new '.$module.'();');
			
			$title = $mod->init_pane();
			if (!Display::$display_stopped && $title) {
				$this->open_content_pane($mod, array('title' => $title));
				try {
					$mod->display_pane($disp);
				} catch (Exception $e) {
					/* Make sure to close the content pane*/
					$this->close_content_pane($mod);
					throw $e;
				}
				$this->close_content_pane($mod);
			}
						
		} catch (Exception $e) {
			$I2_ERR->nonfatal_error('Exception raised in module '.$module.', while processing main pane. Exception: '.$e->__toString());
		}
			
		// Let the Intrabox class handle intraboxes
		Intrabox::display_boxes($mod);
		
		$this->global_footer();
	}

	/**
	* Stops from anything being displayed? FIXME: explain this!
	*/
	public static function halt_display() {
		Display::$display_stopped = TRUE;
	}
	
	/**
	* Resumes all display? FIXME: explain this!
	*/
	public static function resume_display() {
		Display::$display_stopped = FALSE;
	}

	/**
	* Get the current buffering state.
	*
	* @return bool Whether buffering is enabled.
	*/
	public function buffering_on() {
		return Display::$core_display->buffering;
	}

	/**
	* Assign a Smarty variable a value.
	*
	* @param mixed $var either the name of the variable to assign or an array of key,value pairs to assign.
	* @param mixed $value The value to assign the variable.
	*/
	public function smarty_assign($var,$value=null) {
		if ($value === null) {
			//Assign key,value pairs in the array
			$this->smarty->assign($var);
		}
		else {
			$this->smarty->assign($var,$value);
		}
	}

	/**
	* Assigns all I2 variables which we want available in all tempaltes.
	*/
	private function assign_i2vals() {
		$root = i2config_get('www_root', 'https://iodine.tjhsst.edu/','core');
		$this->smarty->assign('I2_ROOT', $root);
		$this->smarty->assign('I2_SELF', $_SERVER['REDIRECT_URL']);
		$this->smarty->assign('I2_CSS', $root . i2config_get('css_url', 'www/css.css', 'display'));
	}

	/**
	* The display function.
	* 
	* @param string $template File name of the template.
	* @param array $args Associative array of Smarty arguments.
	*/
	public function disp($template, $args=array()) {
		$this->assign_i2vals();
		$this->smarty_assign($args);
		
		// Validate template given
		if( ($tpl = self::get_template($template)) === NULL ) {
			throw new I2Exception('Invalid template `'.$template.'` passed to Display');
		}
		
		if ($this->buffering_on()) {
			Display::$core_display->buffer .= $this->smarty->fetch($tpl); 
		} else {
			$this->smarty->display($tpl);
		}
	}
	
	/**
	* Output raw HTML to the browser.  Not advisable.
	*
	* @param string $text The text to display.
	*/
	public function raw_display($text) {
		$text = 'Raw display from module '.$this->my_module_name.': '.$text;
		if ($this->buffering_on()) {
			Display::$core_display->buffer .= "$text";
		} else {
			echo($text);
		}
	}
	
	/**
	* Clear any output buffers, ensuring that all data is written to the browser.
	*/
	public function flush_buffer() {
		if ($this == Display::$core_display) {
			echo(Display::$core_display->buffer);
			Display::$core_display->buffer = "";
		}
	}
	
	/**
	* Set whether or not to buffer output.
	*
	* @param bool $on Whether to buffer output.
	*/
	public function set_buffering($on) {
		if ($this == Display::$core_display) {
			Display::$core_display->buffering = $on;
			if (!$this->buffering_on()) {
				$this->flush_buffer();
			}
		}
	}
	
	/**
	* Outputs everything that should go to the user before iboxes, regardless
	* of whether it will appear at the top or bottom of the finished layout.
	* Also sends all necessary header information, links CSS, etc.  Please note
	* that this is not global:  it is called only on the core's Display instance.
	*/
	public function global_header() {
		$this->disp('header.tpl');
		$this->flush_buffer();
	}

	/**
	* Closes everything that remains open, and prints anything else that goes
	* after the modules.
	*/
	public function global_footer() {
		$this->disp('footer.tpl');
		$this->flush_buffer();
	}

	/**
	* Open the content pane.
	*
	* @param object $module The module that will be displayed in the main box.
	*/
	public function open_content_pane(&$module) {
		$this->set_buffering(false);
		$this->name = $module->get_name();
		$this->disp('openmainbox.tpl',array('module_name'=>$this->name));
	}

	/**
	* Close the content pane.
	*
	* @param object $module The module that was displayed in the main box.
	*/
	public function close_content_pane(&$module) {
		$this->name = $module->get_name();
		$this->disp('closemainbox.tpl',array('module_name'=>$this->name));
	}

	/**
	* Wraps templates in {strip} tags before compilation if debugging is on.
	*
	* @todo Detect for 'debug mode', and actually enable the function.
	* @param string $source The uncompiled template file.
	* @param object $smarty The Smarty object.
	* @return string The source, wrapped in {strip} tags if appropriate.
	*/
	public function prefilter($source,&$smarty) {
		if (!$debug) {
			return '[<strip>]'.$source.'[</strip>]';
		}
		return $source;
	}

	/**
	* The postfilter smarty function. Not currently in use.
	*/
	public function postfilter($source,&$smarty) {
		return $source;
	}

	/**
	* The outputfilter smarty function. Not currently in use.
	*/
	public function outputfilter($output,&$smarty) {
		return $output;
	}

	/**
	* Gets the absolute filename for a template file.
	*
	* Given a template file, this method will determine if such a template
	* file exists, and if so, will return the absolute path name of the
	* template file. Otherwise will return NULL if the template is not
	* found.
	*
	* @returns mixed The absolute filename as a string if the template was
	*                found, NULL otherwise.
	*/
	public static function get_template($tpl) {
		if( is_readable(self::$tpl_root . $tpl) ) {
			return self::$tpl_root . $tpl;
		}
		return NULL;
	}
}
?>
