<?php
/**
* The API for all Intranet2 modules to extend.z
*/
interface Module {

	/**
	* Displays all of a module's ibox content.
	*
	* @param object $disp The Display object to use for output.
	* @abstract
	*/
	function display_box($disp);
	
	/**
	* Displays all of a module's main content.
	*
	* @param object $disp The Display object to use for output.
	* @abstract
	*/
	function display_pane($disp);
	
	/**
	* Gets the module's name.
	*
	* @returns string The name of the module.
	* @abstract
	*/
	function get_name();

	/**
	* Performs all initialization necessary for this module to be 
	* displayed in an ibox.
	*
	* @abstract
	*/
	function init_box();

	/**
	* Performs all initialization necessary for this module to be
	* displayed as the main page.
	*
	* @abstract
	*/
	function init_pane();
	
}
?>
