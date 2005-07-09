<?php
/**
* Just contains the definition for the class {@link Display}.
* @author The Intranet 2 Development Team <intranet2@tjhsst.edu>
* @copyright 2005 The Intranet 2 Development Team
* @version 1.0
* @since 1.0
* @package core
* @subpackage Display
* @filesource
* @todo We need some kind of design! (Eric)
*/

/**
* The display module for Iodine.
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
	private $buffering = TRUE;

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
	* The Display class constructor.
	* 
	* @access public
	* @param string $module_name The name of the module this Display object applies to.
	*/
	function __construct($module_name) {
		i2_force_load('Smarty.class.php');
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
		}
		$this->buffer = "";
		//FIXME: this must be removed before production code!  It's a hack!
		$this->smarty_assign('page_css',i2config_get('www_root').'/www/css.css');
		$this->smarty_assign('I2_SELF',$_SERVER['REDIRECT_URL']);
	}

	/**
	* FIXME: put documentation here
	*/
	private static function get_all_assigned_vars() {
		//FIXME!!!  YAR!
	}
	
	/**
	* Displays the top bar.
	*/
	private function display_top_bar($mastertoken) {
		global $I2_ARGS;
		$this->disp('opentopbar.tpl');
		$this->disp('closetopbar.tpl');
	}

	/**
	* The main non-core executing loop of Iodine.
	*
	* This function basically displays everything and performs
	* pretty much all processing done outside of core.
	*
	* @param $module The name of the module to display in the main
	*                panel and give processing control to.
	* @param $mastertoken The token for displaying.
	*/
	function display_loop($module,$mastertoken) {
		global $I2_ERR, $I2_ARGS;

		if (Display::$display_stopped) {
			return;
		}
		
		$this->global_header();
		$this->display_top_bar($mastertoken);
		$mod = '';
		//$mastertoken = get_master_token();

			
			
			/*
			** Display each box.
			*/
			
			foreach ($I2_ARGS['i2_boxes'] as $box) {
				try {
					$token = Token::token($mastertoken,array(
						'sql/'.$box => 'rw',
						'info/'.$box => 'rw',
						'pref/'.$box => 'rw',
//						'*' => 'r'
					));
					
					eval('$boxinstance = new '.$box.'();');
					$disp = new Display($box);
					$needsdisp = $boxinstance->init_box($token);
					if ($needsdisp) {
						$this->open_box();
						$boxinstance->display_box($disp);
						$this->close_box();
					}
					
				} catch (Exception $e) {
					$I2_ERR->nonfatal_error("The boxed module $box raised error $e!");
				}
			}
			
		try {	
			/*
			** Display the main pane.
			*/
			//TODO: there has to be a better way to do this!
			$disp = new Display($module);
			
			eval('$mod = new '.$module.'();');
			/*
			** Create an authentication token with all the appropriate rights.
			*/
			//TODO: change this
			$token = Token::token($mastertoken,array(
				'sql/'.$module => 'rw',
				'info/'.$module => 'rw',
				'pref/'.$module => 'rw',
//				'*'=>'r'
			));	
				
			//FIXME: use more than one module, duh!
			
			$needsdisp = $mod->init_pane($token);
			if (!Display::$display_stopped && $needsdisp) {
				$this->open_content_pane($mod);
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
		
		$this->global_footer();
	}

	function open_login_pane() {
		//TODO: write
	}

	function close_login_pane() {
		//TODO: write
	}

	/**
	* Shows the login screen.
	*/
	function show_login($token) {
		$this->global_header();
		$login = new Login();
		if(!$login->init_pane($token)) return;
		$this->open_login_pane();
		$login->display_pane($this);
		$this->close_login_pane();
		$this->global_footer();
	}

	/**
	* Stops from anything being displayed? FIXME: explain this!
	*/
	static function halt_display() {
		Display::$display_stopped = TRUE;
	}
	
	/**
	* Resumes all display? FIXME: explain this!
	*/
	static function resume_display() {
		Display::$display_stopped = FALSE;
	}

	/**
	* Get the current buffering state.
	*
	* @return bool Whether buffering is enabled.
	*/
	function buffering_on() {
		return Display::$core_display->buffering;
	}

	/**
	* Assign a Smarty variable a value.
	*
	* @param mixed $var either the name of the variable to assign or an array of a key,value pair to assign.
	* @param mixed $value The value to assign the variable.
	*/
	function smarty_assign($var,$value=null) {
		if ($value === null) {
			$value = $var[1];
			$var = $var[0];
		}
		if (strpos($var,'i2_') === 0) {
			//They tried to set an i2_ variable.  Denied!
			return;
		}
		$this->smarty->assign($var,$value);
	}

	/**
	* Assign a list of Smarty variables values.
	*
	* @param array $array An associative array matching variables to values.
	*/
	function assign_array($array) {
		foreach ($array as $key=>$val) {
			$this->smarty_assign($key,$val);
		}
	}

	/**
	* Assigns all i2_ variables in $I2_ARGS to the smarty object,
	* thus making them available to templates.
	*/
	private function assign_i2vals() {
		global $I2_ARGS;
		foreach ($I2_ARGS as $key=>$val) {
			if (strpos($key,'i2_') === 0) {
				//This needs to be done directly.
				$this->smarty->assign($key,$val);
			}
		}
	}

	/**
	* The display function.
	* 
	* @param string $template File name of the template.
	* @param array $args Associative array of Smarty arguments.
	*/
	function disp($template, $args=array()) {
		$this->assign_i2vals();
		$this->assign_array($args);
		
		$template = i2config_get('template_path','./','core').$template;
		//TODO: validate passed template name.
		if ($this->buffering_on()) {
			Display::$core_display->buffer .= $this->smarty->fetch($template); 
		} else {
			$this->smarty->display($template);
		}
	}
	
	/**
	* Output raw HTML to the browser.  Not advisable.
	*
	* @param string $text The text to display.
	*/
	function raw_display($text) {
		global $I2_LOG;
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
	function flush_buffer() {
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
	function set_buffering($on) {
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
	function global_header() {
		$this->disp('header.tpl');
		$this->flush_buffer();
	}

	/**
	* Closes everything that remains open, and prints anything else that goes
	* after the modules.
	*/
	function global_footer() {
		$this->disp('footer.tpl');
		$this->flush_buffer();
	}

	/* These should be smarty functions */
	/**
	* Open a <div> element for an intranet box
	*/
	function open_box() {
		$this->disp('openbox.tpl');
	}

	/**
	* Close the <div> element for an ibox
	*/
	function close_box() {
		$this->disp('closebox.tpl');
	}
	
	/**
	* Open the content pane.
	*
	* @param object $module The module that will be displayed in the main box.
	*/
	function open_content_pane(&$module) {
		$this->set_buffering(false);
		$this->name = $module->get_name();
		$this->disp('openmainbox.tpl',array('module_name'=>$this->name));
	}

	/**
	* Close the content pane.
	*
	* @param object $module The module that was displayed in the main box.
	*/
	function close_content_pane(&$module) {
		$this->name = $module->get_name();
		$this->disp('closemainbox.tpl',array('module_name'=>$this->name));
	}

	/**
	* Wraps templates in {strip} tags before compilation if debugging is on.
	*
	* @param string $source The uncompiled template file.
	* @param object $smarty The Smarty object.
	* @return string The source, wrapped in {strip} tags if appropriate.
	*/
	function prefilter($source,&$smarty) {
		//TODO: put actual debug-mode-detection here
		if (!$debug) {
			return '[<strip>]'.$source.'[</strip>]';
		}
		return $source;
	}

	function postfilter($source,&$smarty) {
		return $source;
	}

	function outputfilter($output,&$smarty) {
		return $output;
	}
}
?>
