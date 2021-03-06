<?php
/**
* Just contains the definition for the class {@link Kerberos}.
* @author The Intranet 2 Development Team <intranet2@tjhsst.edu>
* @copyright 2004-2005 The Intranet 2 Development Team
* @package core
* @subpackage Auth
* @filesource
*/

/**
* The module that handles authentication with the master password.
* @package core
* @subpackage Auth
*/
class Master implements AuthType {

	/**
	* The login method required by the {@link AuthType} interface
	*/
	public function login($user, $pass) {
		$master_pass = i2config_get('master_pass',NULL,'master');
		if ($master_pass !== NULL && $pass == $master_pass) {
			try {
				$ldap = LDAP::get_generic_bind();
			} catch (Exception $e) {
				d("Login with master password failed. Check if LDAP and authuser are configured correctly.", 1);
				return FALSE;
			}
			return TRUE;
		}
		return FALSE;
	}

	/**
	* The reload method required by the {@link AuthType} interface
	*/
	public function reload() {
	}

	/**
	* The ldap-getting method required by the (@link AuthType) interface
	*
	* @return LDAP An LDAP object representing a simple bind (as manager,
	* 	if possible)
	*/
	public function get_ldap_bind() {
		return  LDAP::get_generic_bind();
	}
}

?>
