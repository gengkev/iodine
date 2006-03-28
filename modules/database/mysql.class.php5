<?php
/**
* Contains the definition for the class {@link MySQL}.
* @author The Intranet 2 Development Team <intranet2@tjhsst.edu>
* @copyright 2004 The Intranet 2 Development Team
* @package core
* @subpackage Database
* @filesource
*/

/**
* The MySQL module for Iodine.
* @package core
* @subpackage Database
* @see Result
*/
class MySQL {

	/**
	* The mysql_pconnect() link
	*/
	private $link;
	
	/**
	* Represents a SELECT query.
	*/
	const SELECT = 1;
	/**
	* Represents an INSERT query.
	*/
	const INSERT = 2;
	/**
	* Represents an UPDATE query.
	*/
	const UPDATE = 3;
	/**
	* Represents a DELETE query.
	*/
	const DELETE = 4;
	/**
	* Represents a REPLACE query.
	*/
	const REPLACE = 5;
	
	/**
	* Represents input that is a string
	*/
	const STRING	= 10;
	/**
	* Represents input that is an integer
	*/
	const INT	= 11;
	/**
	* Represents input that is a float
	*/
	const FLOAT	= 12;
	/**
	* Represents input that represents a date
	*/
	const DATE	= 13;

	/**
	* A string representing all custom printf tags for mysql queries which require an argument. Each character represents a different tag.
	*/
	const TAGS_ARG = 'acdistDIS';

	/**
	* A string representing all custom printf tags for mysql queries which do not require an argument. Each character represents a different tag.
	*/
	const TAGS_NOARG = 'V%';
		
	
	/**
	* The MySQL class constructor.
	* 
	* @access public
	*/
	function __construct() {
		$this->connect(i2config_get('server','','mysql'), i2config_get('user','','mysql'), i2config_get('pass','','mysql'));
		$this->select_db('iodine');
	}

	/**
	* Connects to a MySQL database server.
	*
	* @access protected
	* @param string $server The MySQL server location/name.
	* @param string $user The MySQL username.
	* @param string $password The MySQL password.
	*/
	protected function connect($server, $user, $password) {
		d("Connecting to mysql server $server as $user",8);
		$this->link = @mysql_pconnect($server, $user, $password);
		if( $this->link === FALSE ) {
			throw new I2Exception('Could not connect to MySQL server');
		}
		return $this->link;
	}
	
	/**
	* Select a MySQL database.
	*
	* @access protected
	* @param string $database The name of the database to select.
	*/
	protected function select_db($database) {
		mysql_select_db($database, $this->link);
	}

	/**
	* Perform a preformatted MySQL query.
	*
	* @param string $query The query string.
	* @return resource A raw mysql result resourse.
	*/
	function raw_query($query) {
		global $I2_ERR;
		d('Running MySQL query: '.$query,7);
		$r = mysql_query($query, $this->link);
		if ($err = mysql_error($this->link)) {
			throw new I2Exception('MySQL error: '.$err);
			return false;
		}
		return $r;
	}

	/**
	* Printf-style MySQL query function.
	*
	* This takes a string and args. The string is the actual MySQL query
	* with optional printf-style markers to indicate values that should be
	* checked (or formatted in a certain way). Any other arguments after
	* that are the printf-style arguments. For example:
	*
	* <code>
	* query('SELECT * FROM mytable WHERE id=%d', $the_id);
	* </code>
	*
	* Will essentially execute the query
	* 'SELECT * FROM mytable WHERE id=`$the_id`' except it will check that
	* $the_id is a valid integer.
	*
	* The printf-style tags implemented are:
	* <ul>
	* <li>%a - A string which only contains alphanumeric characters</li>
	* <li>%c - A table or column name, or an array of column or table names</li>
	* <li>%d or %i - An integer, or an integer in a string</li>
	* <li>%D or %I - An array of integers, to be separated by ','</li>
	* <li>%s - A string, which will be quoted, and escapes all necessary
	* characters for use in a mysql statement</li>
	* <li>%S - An array of strings, quoted and escaped</li>
	* <li>%t - A date of the form YYYY-MM-DD</li>
	* <li>%V - Outputs the current Iodine version</li>
	* <li>%% - Outputs a literal '%'</li>
	* </ul>
	*
	* @access public
	* @param string $query The printf-ifyed query you want to run.
	* @param mixed $args,... Arguments for printf tags.
	* @return Result The results of the query run.
	*/
	public function query($query) {
		$args = NULL;
		
		if( func_num_args() > 1 ) {
			$args = func_get_args();
			array_shift($args);
		}

		return $this->query_arr($query, $args);
	}

	/**
	* Array counterpart to query().
	*
	* This is the same as the query() method, except that it takes its
	* arguments as an array, instead of as a varied-length list of
	* arguments. Use this if you're building your own string that involves
	* printf tags and you need to dynamically create the argument list, as
	* well.
	*
	* @param string $query The printf-ifyed query you want to run.
	* @param array $args Arguments for printf tags.
	* @return Result The results of the query run.
	*/
	public function query_arr($query, $args = NULL) {
		global $I2_ERR,$I2_LOG;

		$argc = $args == NULL ? 0 : count($args);
		$argv = $args;

		$query = trim($query);
		
		/* 
		** matches Iodine custom printf-style tags
		*/
		if( preg_match_all(
			'/(?<!%)%['.self::TAGS_ARG.self::TAGS_NOARG.']/',
			$query,
			$tags,
			PREG_PATTERN_ORDER | PREG_OFFSET_CAPTURE )
		) {
			foreach (array_reverse($tags[0]) as $tag) {
				/*$tag[0] is the string, $tag[1] is the offset*/
				
				/* tags that require an argument */
				if ( strpos(self::TAGS_ARG, $tag[0][1]) !== FALSE) {
					if($argc < 1) {
						throw new I2Exception('Insufficient arguments to mysql query string');
					}
					$arg = array_pop($argv);
					$argc--;
				}

				/* Now substitute the tag depending on which tag
				was matched. $arg is the argument, if the tag
				needs one, and $replacement is the string to
				replace the tag with*/

				if ($arg === NULL) {
					$replacement = 'NULL';
				} else {
					$replacement = $this->replace_tag($arg, $tag[0][1]);
				}

				$query = substr_replace($query,$replacement,$tag[1],2);
			}
		}

		/* Get query type by examining the query string up to the first
		space */
		switch( strtoupper(substr($query, 0, strpos($query, ' '))) ) {
			case 'DESCRIBE':
			case 'SHOW':
			case 'SELECT':
				$query_t = MYSQL::SELECT;
				break;
			case 'UPDATE':
				$query_t = MYSQL::UPDATE;
				break;
			case 'DELETE':
				$query_t = MYSQL::DELETE;
				break;
			case 'INSERT':
				$query_t = MYSQL::INSERT;
				break;
			case 'REPLACE':
				$query_t = MYSQL::REPLACE;
				break;
			default:
				throw new I2Exception('Attempted MySQL query of unauthorized command `'.substr($query, 0, strpos($query, ' ')).'`');
		}
		return new MySQLResult($this->raw_query($query),$query_t);
	}

	private function replace_tag($arg, $tag) {
		switch($tag) {
			/* 'argument' tags first */
			
			/*alphanumeric string*/
			case 'a':
				if ( !ctype_alnum($arg) ) {
					throw new I2Exception('String `'.$arg.'` contains non-alphanumeric characters, and was passed as an %a string in a mysql query');
				}
			case 's':
				return '\''.mysql_real_escape_string($arg).'\'';

			/* array of strings */
			case 'S':
				if( !is_array($arg) ) {
					throw new I2Exception('Non-array passed as %S in a mysql query');
				}
				foreach($arg as $i=>$str) {
					$arg[$i] = mysql_real_escape_string($str);
				}
				return '\''.implode('\',\'', $arg).'\'';

			/* table or column name or array of table or column names*/
			case 'c':
				if( is_array($arg) ) {
					foreach($arg as $i=>$col) {
						$arg[$i] = mysql_real_escape_string($col);
					}
					return '`'.implode('`,`', $arg).'`';
				}
				else {
					return '`'.mysql_real_escape_string($arg).'`';
				}

			/* array of integers */
			case 'I':
			case 'D':
				if( !is_array($arg) ) {
					throw new I2Exception('Non-array passed to %D in a mysql query');
				}
				foreach($arg as $num) {
					if(!(	is_int($num) ||
						ctype_digit($num) ||
						(ctype_digit(substr($num,1)) && $num[0]=='-') //negatives
					)) {
						throw new I2Exception('Non-integer `'.$num.'` passed in the array passed to %D in a mysql query');
					}
				}
				return implode($arg, ',');

			/* integer*/
			case 'd':
			case 'i':
				if (	is_int($arg) ||
					ctype_digit($arg) ||
					(ctype_digit(substr($arg,1)) && $arg[0]=='-') //negatives
				) {
					return ''.$arg;
				}
				else {
					throw new I2Exception('The string `'.$arg.'` is not an integer, but was passed as %d or %i in a mysql query');
				}

			/* date */
			case 't':
				if(is_string($arg)) {
					$parts = explode("-", $arg);
					if(count($parts) == 3 && !empty($parts[1]) && !empty($parts[2]) && strlen($parts[0]) == 4 && strlen($parts[1]) <= 2 && strlen($parts[2]) <= 2 && ctype_digit($parts[0]) && ctype_digit($parts[1]) && ctype_digit($parts[2]) && checkdate($parts[1], $parts[2], $parts[0])) {
						return '\''.$arg.'\'';
					}
				}
				throw new I2Exception('The string `'.$arg.'` is not a properly formatted date, but was passed as %t in a mysql query');
			
			/* Non-argument tags below here */
			
			/*Iodine version string*/
			case 'V':
				return 'TJHSST Intranet2 Iodine version '.I2_VERSION;
			case '%':
				return '%';
			
			/* sanity check */
			default:
				$I2_ERR->fatal_error('Internal error, undefined mysql printf tag `%'.$tag[0][1].'`', TRUE);
		}
	}
	
	/**
	* Determines whether a certain column is in a certain table.
	*
	* @param string $table The mysql table where the column might be.
	* @param string $col The name of the column you are searching for.
	* @return bool TRUE if $col is in table $table, FALSE otherwise.
	*/
	public function column_exists($table, $col) {
		foreach($this->query('DESCRIBE %c;', $table)->fetch_all_arrays(Result::ASSOC) as $field) {
			if( $field['Field'] == $col ) {
				return TRUE;
			}
		}
		return FALSE;
	}
}

?>