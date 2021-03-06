<?php
/**
* Class for uploaded documents.
* @package modules
* @subpackage Docs
* @filesource
*/

/**
* The class that represents a doc.
* @package modules
* @subpackage Docs
*/
class Doc {
        private $doc_id;
        private $name;

        private $path;
        private $visibility;

        private $gs = [];
	private $type;

        /**
         * Vars this can get:
         * docid, name, visible, groups, path, type
         */
        public function __get($var) {
                switch($var) {
                        case 'docid':
                                return $this->doc_id;
                        case 'name':
                                return $this->name;
                        case 'path':
                                return $this->path;
                        case 'visible':
                                return $this->visibility;
                        case 'groups':
                                return $this->gs;
			case 'type':
				return $this->type;
                }
        }

        /**
        * Creates a Doc object
        *
        * @param int $docid The id of the doc
        */
        public function __construct($docid) {
                global $I2_SQL;
                $docinfo = $I2_SQL->query('SELECT name,path,visible,type FROM docs WHERE docid=%d', $docid)->fetch_array(Result::ASSOC);
                $this->doc_id = $docid;
                $this->name = $docinfo['name'];
                $this->path = $docinfo['path'];
		$this->type = $docinfo['type'];
                $this->visibility = $docinfo['visible'] == 1 ? true : false;

                $gs = $I2_SQL->query('SELECT * FROM doc_permissions WHERE docid=%d',$docid)->fetch_all_arrays();
                foreach($gs as $g) {
                        $this->gs[$g['gid']] = array($g['view'], $g['edit']);
                }
        }

        /**
        * Create a new document.
        *
        * @param string $name The name of the document
        * @param string $path The path to the document
        * @param boolean $visible Determines if the document is visible
	* @param string $type The mime type of the document
        *
        * @return Doc The newly-created document
        */
        public static function add_doc($name, $path, $visible, $type) {
                global $I2_SQL;
                $docid = $I2_SQL->query('INSERT INTO docs SET name=%s, path=%s, visible=%d, type=%s',$name,$path,$visible,$type)->get_insert_id();
                return new Doc($docid);
        }

        /**
        * Updates the document
        *
        * @param string $name The name of the document
        * @param string $path The path to the document
        * @param boolean $visible Determines if the document is visible
	* @param string $type The mime type of the document
        */
        public function edit_doc($name, $path, $visible, $type) {
                global $I2_SQL;
                $I2_SQL->query('UPDATE docs SET name=%s, path=%s, visible=%d, type=%s WHERE docid=%d',$name,$path,$visible,$type,$this->doc_id);
                $this->name = $name;
                $this->path = $path;
                $this->visibility = $visible;
		$this->type = $type;
        }

        /**
        * Deletes the document with given id
        *
        * @param int $docid The document's id
        */
        public static function delete_doc($docid) {
                global $I2_SQL;
                $I2_SQL->query('DELETE FROM docs WHERE docid=%d', $docid);
                $I2_SQL->query('DELETE FROM doc_permissions WHERE docid=%d', $docid);
        }

        /**
        * Returns all documents
        *
        * @return array The array of documents
        */
        public static function all_docs() {
                global $I2_SQL;
                $docids = $I2_SQL->query('SELECT docid FROM docs ORDER BY docid DESC')->fetch_all_single_values();
                $docs = [];
                foreach($docids as $docid) {
                        $docs[] = new Doc($docid);
                }
                return $docs;
        }

        /**
        * Return all accessible docs
        *
        * @return array All documents the user can see
        */
        public static function accessible_docs() {
                global $I2_USER;
                $docs = Doc::all_docs();
                if($I2_USER->is_group_member('admin_all'))
                        return $docs;
                $ugroups = Group::get_user_groups($I2_USER);
                foreach($docs as $doc) {
                        foreach($ugroups as $grp) {
                                if(isset($doc->gs[$grp->gid])) {
                                        $out[] = $doc;
                                        break;
                                }
                        }
                }
                return $out;
        }

        /**
        * Determines whether a user can see a document
        *
        * @return boolean Whether or not the document can be viewed
        */
        public function can_see() {
                global $I2_USER;
                if($I2_USER->is_group_member('admin_all'))
                        return TRUE;
                if(!$this->visibility)
                        return FALSE;
                $ugroups = Group::get_user_groups($I2_USER);
                foreach($ugroups as $grp) {
                        if(isset($this->gs[$grp->gid]))
                                return TRUE;
                }
                return FALSE;
        }

        /**
        * Adds a group with specified permissions
        *
        * @param integer gid The group id
        * @param array perms An array of permissions
        */
        public function add_group_id($gid,$perms=array(TRUE,FALSE)) {
                global $I2_SQL;
                if($gid != -1)
                        $I2_SQL->query('INSERT INTO doc_permissions SET docid=%d, gid=%d, view=%d, edit=%d',$this->doc_id,$gid,$perms[0],$perms[1]);
                $this->gs[$gid] = $perms;
        }

        /**
        * Edits a group's permissions
        *
        * @param integer gid The group id
        * @param array perms An array of permissions
        */
        public function edit_group_id($gid,$perms) {
                global $I2_SQL;
                $I2_SQL->query('UPDATE doc_permissions SET view=%d, edit=%d WHERE docid=%d AND gid=%d', $perms[0],$perms[1],$this->doc_id,$gid);
                $this->gs[$gid] = $perms;
        }

        /**
        * Deletes a group's permissions
        *
        * @param integer gid The group id
        */
        public function remove_group_id($gid) {
                global $I2_SQL;
                $I2_SQL->query('DELETE FROM doc_permissions WHERE docid=%d AND gid=%d',$this->doc_id,$gid);
                unset($this->gs[$gid]);
        }

        /**
        * Checks to see if the user can perform the specified action
        *
        * @param int docid The document's id
        * @param string action The action being performed
        *
        * @return boolean Whether the operation is valid
        */
        public static function can_do($docid,$action) {
                global $I2_USER, $I2_SQL;
                if($I2_USER->is_group_member('admin_all')) {
                        return TRUE;
                }
                switch($action) {
                        case 'home':
                                return TRUE;
                        case 'add':
                                return FALSE;
                        case 'edit':
                        case 'delete':
                                $action = 'edit';
                                break;
                        case 'view':
                                break;
                        default:
                                throw new I2_Exception('Illegal action '.$action.' for document');
                }
                $groups = $I2_SQL->query('SELECT * FROM doc_permissions WHERE docid=%d',$docid)->fetch_all_arrays();
                $ugroups = Group::get_user_groups($I2_USER);
                foreach($groups as $g) {
                        if($g[$action]) {
                                if(in_array(new Group($g['gid']),$ugroups))
                                        return TRUE;
                        }
                }
                return FALSE;
        }
}
?>
