<?php
/**
* Just contains the definition for the class {@link LDAP}.
* @author The Intranet 2 Development Team <intranet2@tjhsst.edu>
* @copyright 2004-2005 The Intranet 2 Development Team
* @package core
* @subpackage Database
* @filesource
*/

/**
* A class for interfacing with the Lightweight Directory Access Protocol.
* @package core
* @subpackage Database
*/
class LDAP {

	const LDAP_SEARCH = 100;
	const LDAP_MODIFY = 200;
	const LDAP_DELETE = 300;
	const LDAP_COMPARE = 400;

	const SCOPE_SUB = 1;
	const SCOPE_BASE = 2;
	const SCOPE_ONE = 3;

	private $dnbase;
	private static $ou_bases = [];
	private $bind;
	private $conn;
	private $server;
	private $sizelimit;
	private $timelimit;
	
	private $conns = [];
	
	function __construct($dn=NULL,$pass=NULL,$server=NULL,$gssapi=FALSE) {
		if ($server !== NULL) {
			$this->server = $server;
		} else {
			$this->server = i2config_get('server','localhost','ldap');
		}
		self::cache_config();
		$this->dnbase = i2config_get('base_dn','dc=tjhsst,dc=edu','ldap');
		$this->sizelimit = i2config_get('max_rows',500,'ldap');
		$this->timelimit = i2config_get('max_time',0,'ldap');
		
		$this->rebase($dn);
		
		d("Connecting to LDAP server {$this->server}...",8);
		$this->conn = $this->connect();
		if ($gssapi) {
			/*
			** GSSAPI bind - ignores $dn and $pass!
			*/
			//$_ENV['KRB5CCNAME'] = $I2_AUTH->cache();
			//putenv("KRB5CCNAME={$_ENV['KRB5CCNAME']}");
			if(ldap_set_option($this->conn,LDAP_OPT_PROTOCOL_VERSION,3)) {
				d('LDAP protocol version set to LDAPv3.',9); //Technically, we should do this, but we don't have to...most of the time.
			} else {
				d('LDAP protocol version set failed.',7);
			}
			d('KRB5CCNAME for LDAP bind is '.$_ENV['KRB5CCNAME'],8);
			$this->bind = ldap_sasl_bind($this->conn,'','','GSSAPI');

			/*
			** This is what stuff would look like for a proxy bind (w/GSSAPI)... But PHP ldap_sasl_bind is badly broken...
			*/
			//$this->bind = ldap_sasl_bind($this->conn,'','','GSSAPI',i2config_get('sasl_realm','CSL.TJHSST.EDU','ldap'),$proxydn);
			
			d('Bound to LDAP via GSSAPI',8);
		} elseif ($dn !== NULL && $pass !== NULL) {
			/*
			** Simple bind
			*/
			$this->bind = @ldap_bind($this->conn,$dn,$pass);
			d("Bound to LDAP simply as $dn",8);
		} else {
			/*
			** Anonymous bind
			*/
			$this->bind = ldap_bind($this->conn);
			d('Bound to LDAP anonymously',8);
		}
		/*
		** These errors aren't nonfatal, so they might bring down the
		** whole application beyond any hope of a fix.
		*/
		if (!$this->conn) {
			throw new I2Exception('Unable to connect to LDAP server!');
		}
		if (!$this->bind) {
			throw new I2Exception('Unable to bind to LDAP server!');
		}
	}

	function __destruct() {
		/*
		** Close all LDAP connections made by this module instance
		*/
		foreach ($this->conns as $conn) {
			ldap_close($conn);
		}
	}

	public static function cache_config() { 
		if (count(self::$ou_bases) > 0) {
			return;
		}
		self::$ou_bases['user'] = i2config_get('user_dn','ou=people,dc=tjhsst,dc=edu','ldap');
		self::$ou_bases['group'] = i2config_get('group_dn','ou=groups,dc=iodine,dc=tjhsst,dc=edu','ldap');
		self::$ou_bases['room'] = i2config_get('room_dn','ou=rooms,dc=tjhsst,dc=edu','ldap');
		self::$ou_bases['schedule'] = i2config_get('schedule_dn','ou=schedule,dc=tjhsst,dc=edu','ldap');
	}
	private function conn_options($conn) {
		/*
		** Version 3 required for GSSAPI binds
		*/
		ldap_set_option($conn,LDAP_OPT_PROTOCOL_VERSION,3);
		/*
		** Not currently used, but a good idea
		*/
		ldap_set_option($conn,LDAP_OPT_DEREF,LDAP_DEREF_ALWAYS);
	}

	/**
	* Connect to the LDAP server
	*/
	private function connect() {
		$conn = ldap_connect($this->server);
		$this->conn_options($conn);
		//ldap_set_rebind_proc($conn,rebind);
		$this->conns[] = $conn;
		return $conn;
	}

	/**
	* Rebind if necessary to chase a referral
	*/ 
	/* private function rebind($conn,$url) {
		$this->conn_options($conn);
		$bind = ldap_sasl_bind($conn,'','','GSSAPI');
		$this->conns[] = $conn;
		if (!$bind) {
			$I2_ERR->nonfatal_error("Unable to bind to LDAP server chasing referral to \"$url\"!");
		}
	} */

	public static function get_anonymous_bind() {
		return new LDAP();
	}

	/**
	* Asks auth for an appropriate user bind
	*/
	public static function get_user_bind() {
		global $I2_AUTH;
		return $I2_AUTH->get_ldap_bind();
	}

	/**
	* Gets a GSSAPI bind
	*/
	public static function get_gssapi_bind($server = NULL) {
		return new LDAP('','',$server,TRUE);
	}

	/**
	* Gets a simple bind as a generic authuser.
	*/
	public static function get_generic_bind() {
		$dn = i2config_get('authuser_dn','fail','ldap');
		$pass = i2config_get('authuser_passwd','fail','ldap');
		return self::get_simple_bind($dn,$pass);
	}

	public static function get_simple_bind($userdn,$pass,$server=NULL) {
		return new LDAP($userdn,$pass,$server);	
	}

	public function has_admin_privs() {
	}

	public function search_base($dn=NULL,$attributes='*',$bind=NULL) {
		return $this->search($dn,'objectClass=*',$attributes,LDAP::SCOPE_BASE,$bind);
	}

	public function search_sub($dn=NULL,$query='objectClass=*',$attributes='*',$bind=NULL) {
		return $this->search($dn,$query,$attributes,LDAP::SCOPE_SUB,$bind);
	}

	/**
	* Sorts LDAPResult objects.  Note that no rows may have been fetched from the Result.
	*
	* @param LDAPResult $result The LDAP Resultset object to sort.
	* @param array $sortattrs An array of attributes, in order, to sort by.
	*/
	public static function sort(LDAPResult $result,$sortattrs) {
		if (!is_array($sortattrs)) {
			$sortattrs = array($sortattrs);
		}
		$result->sort($sortattrs);
	}

	/**
	* Performs a search of the LDAP tree using the given parameters.
	*
	* @todo Properly escape the query string.
	*/
	public function search($dn=NULL,$query='objectClass=*',$attributes='*',$depth=LDAP::SCOPE_SUB,$bind=NULL,$attrsonly=FALSE) {
		if (!is_array($attributes)) {
			$attributes = array($attributes);
		}
		
		$this->rebase($dn);


		if (!$bind) {
			$bind = $this->conn;
		}
			
		if (!$query) {
			$query = 'objectClass=*';
		}

		$res = null;
	
		try {
	
		//TODO: consider how searching is done
			if ($depth == LDAP::SCOPE_SUB) {
				d("LDAP Searching $dn for ".print_r($attributes,1)." where $query...",7);
				$res = ldap_search($bind,$dn,$query,$attributes,$attrsonly,$this->sizelimit,$this->timelimit);
			} elseif ($depth == LDAP::SCOPE_ONE) {
				d("LDAP Listing $dn for ".print_r($attributes,1)." where $query...",7);
				$res = ldap_list($bind,$dn,$query,$attributes,$attrsonly,$this->sizelimit,$this->timelimit);
			} elseif ($depth == LDAP::SCOPE_BASE) {
				d("LDAP Reading $dn's values for ".print_r($attributes,1)." where $query...",7);
				$res = ldap_read($bind,$dn,$query,$attributes,$attrsonly,$this->sizelimit,$this->timelimit);
			} else {
				throw new I2Exception("Unknown scope number $depth passed to ldap_search!");
			}

		} catch (Exception $e) {
			d("LDAP error: $e",5);
		}

		//d('LDAP got '.ldap_count_entries($bind,$res).' results.',7);
		
		//ldap_free_result($res);
		//return NULL;

		if (!$res) {
			return LDAPResult::get_null();
		}
		
		return new LDAPResult($bind,$res,LDAP::LDAP_SEARCH);
	}

	public function search_one($dn='',$query='objectClass=*',$attributes='*',$bind=NULL,$attrsonly=FALSE) {
		return $this->search($dn,$query,$attributes,LDAP::SCOPE_ONE,$bind,$attrsonly);
	}

	/**
	* Adds the base dn if necessary, and escapes special characters
	*
	* @param string $dn The DN to fix
	*/
	private function rebase(&$dn) {
		if (!$dn || $dn === '') {
			$dn = $this->dnbase;
		}
		//FIXME: consider better escaping - this won't always work correctly.
		if (substr($dn,-strlen($this->dnbase)) != $this->dnbase) {
			$dn = addslashes($dn.','.$this->dnbase);
		} else {
			$dn = addslashes($dn);
		}
	}

	public function delete($dn,$bind=NULL) {
		$this->rebase($dn);
		if (!$bind) {
			$bind = $this->conn;
		}
		d("LDAP deleting $dn...",7);
		$res = ldap_delete($bind,$dn);
		return new LDAPResult($bind,$res,LDAP::LDAP_DELETE);
	}

	/**
	* Recursively delete a node and all its children
	*
	*/
	public function delete_recursive($dn,$filter,$bind=NULL,$delete_entry=TRUE) {
		$this->rebase($dn);
		if (!$bind) {
			$bind = $this->conn;
		}
		/*
		** Find all objects below the given DN and delete each one
		*/
		$res = $this->search_one($dn,$filter,array('dn'),$bind,TRUE)->fetch_all_arrays(RESULT::ASSOC);
		foreach (array_keys($res) as $itemdn) {
			/*
			** Avoid weird results of recursing into self
			*/
			if ($itemdn == $dn) {
				continue;
			}
			//d("Deleting dn $itemdn with filter $filter from LDAP recursive delete",6);
			$this->delete_recursive($itemdn,$filter,$bind,TRUE);
		}
		if ($delete_entry) {
			$this->delete($dn,$bind);
		}
	}

	public function modify_val($dn,$attribute_name,$value,$bind=NULL) {
		return $this->modify_object($dn,array($attribute_name=>$value),$bind);
	}

	public function modify_object($dn,$vals,$bind=NULL) {
		if (!$vals) {
			d("Null LDAP modification made to dn $dn",5);
			return TRUE;
		}
		if (!is_array($vals)) {
			throw new I2Exception("Non-array \"$vals\" passed to LDAP modify_object method!");
		}
		$this->rebase($dn);
		if (!$bind) {
			$bind = $this->conn;
		}
		
		d("LDAP modifying $dn: ".print_r($vals,TRUE),7);
		$a=ldap_modify($bind,$dn,$vals);
		if(!$a) {
			d("LDAP modify fail on $dn: ".print_r($vals,TRUE),5);
		}
		return $a;
	}

	public function compare($dn,$attribute,$value,$bind=NULL) {
		$this->rebase($dn);
		//FIXME: better escaping
		$attribute = addslashes($attribute);
		$value = addslashes($value);
		if (!$bind) {
			$bind = $this->conn;
		}
		//TODO: return LDAPResult
		$res = ldap_compare($bind,$dn,$attribute,$value);
		if ($res === -1) {
			throw new I2Exception(ldap_error($bind));
		}
		return $res;
	}

	public function add($dn,$values,$bind=NULL) {
		$this->rebase($dn);
		if (!$bind) {
			$bind = $this->conn;
		}
		if (!$values) {
			throw new I2Exception("Attempted to create null LDAP object with dn $dn");
		}
		if (!is_array($values)) {
			throw new I2Exception("Cannot create LDAP object $dn with non-array \"$values\"");
		}
		/*
		** Filter out empty-string and null values
		*/
		$newvalues = array_filter($values);
		d("LDAP adding dn $dn: ".print_r($newvalues,TRUE),7);
		return ldap_add($bind,$dn,$newvalues);
	}

	/**
	* Rename an entry in LDAP -- MAY BE DANGEROUS?
	*
	* This may(?) have the ability to overwrite entries. Be careful.
	*
	* This line of code demonstrates moving
	* "iodineUid=jrandom,ou=people,dc=tjhsst,dc=edu" to
	* "iodineUid=jhacker,ou=people,dc=tjhsst,dc=edu":
	*
	* <code>
	*  $I2_LDAP->rename("iodineUid=jrandom,ou=people,dc=tjhsst,dc=edu", "jhacker");
	* </code>
	*/
	public function rename($olddn, $newrdnvalue, $bind = NULL) {
		$this->rebase($olddn);
		if (!$bind) {
			$bind = $this->conn;
		}
		if (!$newrdnvalue) {
			throw new I2Exception("Attempted to rename LDAP object to nothing");
		}

		$dnsections = explode(',', $olddn);
		array_shift($dnsections);
		$parent = implode(',', $dnsections);

		$rdnsections = explode('=', $olddn);
		$newrdn = $rdnsections[0].'='.$newrdnvalue;

		return ldap_rename($bind, $olddn, $newrdn, $parent, TRUE);
	}

	public function attribute_add($dn, $values, $bind = NULL) {
		$this->rebase($dn);
		if(!$bind) {
			$bind = $this->conn;
		}
		if (!$values) {
			throw new I2Exception("Attempted to create null LDAP object with dn $dn");
		}
		if (!is_array($values)) {
			throw new I2Exception("Cannot add LDAP attributes to object $dn with non-array \"$values\"");
		}
		/*
		** Filter out empty-string and null values
		*/
		$newvalues = array_filter($values);
		d("LDAP modifying dn $dn adding values: ".print_r($newvalues,TRUE),7);
		return ldap_mod_add($bind, $dn, $newvalues);
	}

	public function attribute_delete($dn, $values, $bind = NULL) {
		$this->rebase($dn);
		if(!$bind) {
			$bind = $this->conn;
		}
		if (!$values) {
			throw new I2Exception("Attempted to create null LDAP object with dn $dn");
		}
		if (!is_array($values)) {
			throw new I2Exception("Cannot delete LDAP attributes from $dn with non-array \"$values\"");
		}
		/*
		** Filter out empty-string and null values
		*/
		$newvalues = array_filter($values);
		//$newvalues = $values;
		/*$fin = [];
		foreach ($newvalues as $value) {
			$fin[$value] = 1;
		}*/
		d("LDAP modifying dn $dn deleting values: ".print_r($newvalues,TRUE),7);
		//return ldap_mod_del($bind, $dn, $newvalues);
		return ldap_modify($bind, $dn, $newvalues);
	}

	public static function get_user_dn($uid = NULL) {
		self::cache_config();
		$oubase = self::$ou_bases['user'];
		if (!$uid) {
			return $oubase;
		}
		$user = new User($uid);
		$uid = $user->username;
		if($uid) {
			return "iodineUid={$uid},{$oubase}";
		} else {
			return $oubase;
		}
	}

	public static function get_user_dn_username($uid = NULL) {
		self::cache_config();
		$oubase = self::$ou_bases['user'];
		if($uid) {
			return "iodineUid={$uid},{$oubase}";
		} else {
			return $oubase;
		}
	}

	public static function get_group_dn($name = NULL) {
		$oubase = self::$ou_bases['group'];
		if (!$name) {
				  return $oubase;
		}
		$group = new Group($name);
		$name = $group->name;
		if($name) {
			return "cn={$name},{$oubase}";
		} else {
			return $oubase;
		}
	}

	public static function get_room_dn($name = NULL) {
		$oubase = self::$ou_bases['room'];
		if($name) {
			return "cn={$name},{$oubase}";
		} else {
			return $oubase;
		}
	}

	public static function get_schedule_dn($sectionid = NULL) {
		$oubase = self::$ou_bases['schedule'];
		if($sectionid) {
			return "tjhsstSectionId={$sectionid},{$oubase}";
		} else {
			return $oubase;
		}
	}

	public static function get_pic_dn($picname, $user = NULL) {
		return 'cn='.$picname.','.self::get_user_dn($user);
	}
	
}

?>
