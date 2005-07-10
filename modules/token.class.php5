<?php
/**
* Just contains the class definition of Token.
* @author The Intranet 2 Development Team <intranet2@tjhsst.edu>
* @copyright 2005 The Intranet 2 Development Team
* @version 1.0
* @since 1.0
* @package core
* @filesource
*/

/**
* The class for determining permissions to data for modules.
*
* NOTE: The constructor for this class is <em>private</em>, so you cannot just
* try to do <pre>$mytoken = new Token(/*...*<!-- -->/);</pre> Use the factory
* method, {@link token} to generate a new one.
*
* @package core
* @see Display
*/
class Token {

	/**
	* An associative array of the rights that this token as. The key is the
	* 'infotype', and the value is a string, where each character represents
	* a right to that infotype.
	*/
	private $rights = array();

	/**
	* The master token. Has access to everything, and the ability to
	* generate new tokens.
	*/
	private static $mastertoken = NULL;
	
	/**
	* Generates the master token, which has all rights.
	*
	* This method can only be run once. Subsequent calls with cause an
	* exception to be thrown.
	*
	* @return Token The master token.
	*/
	final public static function master_token() {
		if (self::$mastertoken !== NULL) {
			throw new I2Exception('Something attempted to re-generate the master token!');
		}

		self::$mastertoken = new Token(array('*' => 'a'));
		return self::$mastertoken;
	}
	
	/**
	* Constructor. This is private so tokens can be created without having
	* to pass $mastertoken for authorization (say, when the master token
	* needs to be created).
	*/
	final private function __construct($rights) {

		if( !is_array($rights) ) {
			throw new I2Exception('A non-array was passed to Token as a rights array. The \'$rights\' parameter to Token\'s constructor must be an assoicative array');
		}

		$this->rights = $rights;
	}

	/**
	* The factory-like method outside classes use to instantiate Token
	* objects.
	*
	* This method just generates a token. It basically just calls the
	* constructor after a few checks. Note that the master token must be
	* passed as the first argument, as authorization to issue tokens.
	*
	* @param Token $mastertoken The master token.
	* @param array $rights An associative array representing which rights to
	*                      which data this token will have.
	*/
	final public static function token(Token &$mastertoken, $rights) {
		if( self::$mastertoken == NULL ) {
			throw new I2Exception('Something tried to create a new token before the mastertoken has been instantiated');
		}
		if( $mastertoken != self::$mastertoken ) {
			throw new I2Exception('Something tried to create a token with an invalid mastertoken');
		}

		return new Token($rights);
	}

	/**
	* Check if this token has certain rights.
	*
	* This function checks if this access token has certain rights to
	* certain data. The $infotype is the type of data that you want to
	* check, and $access is the permissions you want to check against.
	* Confused? Good, because that wasn't a very good explanation. Here's an
	* example showing a standard check to see if this token has access to
	* read data from the 'news_stories' mysql table:
	*
	* <pre>$token->check_rights('mysql/news_stories', 'r')</pre>
	*
	* That will return true if it has access, false if it does not. To check
	* multiple permissions at once, say insert and write, just pass a longer
	* string as the second argument, like so:
	*
	* <pre>$token->check_rights('mysql/news_stories', 'iw')</pre>
	*
	* (The order of the characters in the second argument does not matter.)
	* That will check if the token has insert <em>and</em> write access to
	* the news_stories mysql table.
	*
	* Note that the characters in the 'access' string can represent anything
	* you want them to, they are not standardized, nor do they need to be.
	* But keeping the normal ones like read and write standard will help in
	* the long run.
	*
	* @param String $infotype The type of data to check if the token has access.
	* @param String $access A string containing all of the permissions this token must match to return true.
	*/
	final public function check_rights($infotype, $access) {

		/* disable token checking for now, don't know if we're actually
		going to use tokens */
		return true;
		if( $this === self::$mastertoken ) {
			return true;
		}

		if( count($this->rights) < 1 ) {
			return false;
		}

		$this->infotype = $infotype;

		/* Standard case, an exact match to the $infotype is in the
		$rights array */
		if( isset($this->rights[$infotype]) ) {
			$myrights = $this->rights[$infotype];
		}
		/* wildcards */
		else {
			/* match the wilcard with the longest strlen, as it
			will be the most specific match */
			foreach(array_keys($this->rights) as $pattern) {
				if( fnmatch($pattern, $infotype) &&
				  (!isset($max) || strlen($pattern) > $max) ) {
					$max = strlen($pattern);
					$myrights = $this->rights[$pattern];
				}
			}

			if( !isset($max) ) {
			/* no wildcards matched. Catch-all '*' would have been
			caught by fnmatch, so no rules matched this query.
			Return false as default. */
				return FALSE;
			}
			
		}

		/* Checks if each character in $access is also in the
		appropriate $this->rights string */
		foreach (str_split($access) as $char) {
			if( strpos($myrights, $char) === FALSE) {
				return FALSE;
			}
		}
		return TRUE;
	}
}

?>
