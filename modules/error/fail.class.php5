<?php
/**
* Just contains the definition for the {@link Module} {@link Fail}.
* @author The Intranet 2 Development Team <intranet2@tjhsst.edu>
* @copyright 2005-2006 The Intranet 2 Development Team
* @package modules
* @subpackage Utility
* @filesource
*/

/**
* A module that just always fails. Used for testing module failure.
* @package modules
* @subpackage Utility
*/
class Fail implements Module {
		/**
		* Just always returns the string 'Failure'
		*
		* @return string The word "Failure"
		*/
		public function init_pane() {
			return 'Failure';
		}

		/**
		* Unused.
		*/
		public function init_box() {
			return FALSE;
		}

		/**
		* Always throws an {@link I2Exception} with the text "Failed!"
		*/
		public function display_pane($disp) {
			throw new I2Exception('Failed!');
		}

		/**
		* Unused.
		*/
		public function display_box($disp) {
		}

		/**
		* Returns the name of the module.
		*
		* @return string The name of the module; in this case, 'Fail'
		*/
		public function get_name() {
			return 'Fail';
		}
}
?>
