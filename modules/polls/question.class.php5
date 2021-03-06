<?php
/**
* Just contains the definition for the class {@link Question}.
* @author The Intranet 2 Development Team <intranet2@tjhsst.edu>
* @copyright 2005 The Intranet 2 Development Team
* @package modules
* @subpackage Polls
* @filesource
*/

/**
* The class that keeps track of one question
* @package modules
* @subpackage Polls
*/
abstract class Question {

	protected static $questiontype;

	private $mypid;
	private $myqid;
	private $mymaxvotes;
	private $myquestion;
	private $myanswers;
	private $myanswertype;

	/**
	 * The magic get function.
	 * Returns possible pid, qid, question, answers, and answertype.
	 */
	public function __get($var) {
		global $I2_SQL;
		switch($var) {
		case 'pid':
			return $this->mypid;
		case 'qid':
			return $this->myqid;
		case 'maxvotes':
			return $this->mymaxvotes;
		case 'question':
			return $this->myquestion;
		case 'answers':
			return $this->myanswers;
		case 'answers_randomsort':
			$keys=array_keys($this->myanswers);
			shuffle($keys);
			$ret=[];
			foreach($keys as $key) {
				$ret[$key]=$this->myanswers[$key];
			}
			return $ret;
		case 'answertype':
			return $this->myanswertype;
		default:
			throw new I2Exception("Invalid variable $var attempted to be accessed in ".get_class());
		}
	}

	/**
	 * Constructs a new Question with the given pid and qid.
	 *
	 * Note that this does not modify the database.
	 */
	public function __construct($pid, $qid) {
		global $I2_SQL, $I2_LOG;

		//if (! self::question_exists($qid)) {
		//	throw new I2Exception("Invalid Question attempted to be created with qid $qid");
		//}

		$info = $I2_SQL->query('SELECT maxvotes, question, answertype FROM '.static::$questiontype.'_questions WHERE pid=%d AND qid=%d', $pid, $qid)->fetch_array(Result::ASSOC);

		$this->myqid = $qid;
		$this->mypid = $pid;
		$this->myanswertype = $info['answertype'];
		$this->mymaxvotes = $info['maxvotes'];
		$this->myquestion = $info['question'];

		$as = $I2_SQL->query('SELECT aid, answer FROM '.static::$questiontype.'_responses WHERE pid=%d AND qid=%d', $pid,$qid)->fetch_all_arrays();
		$this->myanswers = [];
		foreach ($as as $answer) {
			$this->myanswers[$answer['aid']] = $answer['answer'];
		}
	}

	/**
	* Create a new question in the database
	*
	* This inserts a question into the database; it does NOT add it to any pre-existing {@link Poll}
	* objects. Usually the {@link Poll} add_question method, which calls this, should be used instead.
	*
	* @param int $pid The ID number of the Question
	* @param string $question The question text
	* @param string $answertype The type of the question
	* @param int $maxvotes The maximum number of options one can vote for
	* @param int $qid The qid to specifically request (NULL => don't care)
	*/
	public static function new_question($pid, $question, $answertype, $maxvotes, $qid=NULL) {
		global $I2_SQL;

		if ($qid == NULL) {
			$qid = $I2_SQL->query('SELECT MAX(qid) FROM '.static::$questiontype.'_questions WHERE pid=%d',$pid)->fetch_single_value();
			if ($qid === NULL)
				$qid = 0;
			else
				$qid += 1;
		}

		$I2_SQL->query('INSERT INTO '.static::$questiontype.'_questions SET pid=%d, qid=%d, question=%s, answertype=%s, maxvotes=%d', $pid, $qid, $question, $answertype, $maxvotes);
		$class=get_called_class();
		return new $class($pid,$qid);
	}

	public function edit_question($text, $answertype, $maxvotes) {
		global $I2_SQL;

		$I2_SQL->query('UPDATE '.static::$questiontype.'_questions SET question=%s, answertype=%s, maxvotes=%d WHERE pid=%d AND qid=%d', $text, $answertype, $maxvotes, $this->mypid, $this->myqid);
	}

	public function add_answer($text, $aid=NULL) {
		global $I2_SQL;
		if ($aid === NULL) {
			$aid = $I2_SQL->query('SELECT MAX(aid) FROM '.static::$questiontype.'_responses WHERE pid=%d AND qid=%d',$this->mypid,$this->myqid)->fetch_single_value();
			if ($aid === NULL)
				$aid = 0;
			else
				$aid += 1;
		}

		$I2_SQL->query('INSERT INTO '.static::$questiontype.'_responses SET pid=%d, qid=%d, aid=%d, answer=%s',$this->mypid,$this->myqid,$aid,$text);
		$this->myanswers[$aid] = $text;
	}

	public function edit_answer($text, $aid) {
		global $I2_SQL;

		$I2_SQL->query('UPDATE '.static::$questiontype.'_responses SET answer=%s WHERE pid=%d AND qid=%d AND aid=%d',$text,$this->mypid,$this->myqid,$aid);
		$this->myanswers[$aid] = $text;
	}

	public function delete_answer($aid) {
		global $I2_SQL;
		$I2_SQL->query('DELETE FROM '.static::$questiontype.'_responses WHERE pid=%d AND qid=%d AND aid=%d',$this->mypid,$this->myqid,$aid);
		unset($this->myanswers[$aid]);
	}

	/**
	 * Returns a list of all possible answer types.
	 *
	 * The format of this list is an array where the keys are the MySQL entries and the values are the
	 * beautified versions for output.
	 */
	public static function get_answer_types() {
		global $I2_SQL;
		$en = $I2_SQL->query('SHOW COLUMNS FROM '.static::$questiontype.'_questions LIKE \'answertype\'')->fetch_array();
		$en = substr($en['Type'],6,-2);
		$en = explode("','", $en);
		$res = [];
		foreach ($en as $type) {
			$res[$type] = ucfirst(str_replace('_',' ',$type));
		}
		return $res;
	}


	public function user_voted_for($aid) {
		global $I2_SQL, $I2_USER;
		return $I2_SQL->query('SELECT COUNT(*) FROM '.static::$questiontype.'_votes WHERE pid=%d AND qid=%d AND aid=%d AND uid=%d',$this->mypid,$this->myqid,$aid,$I2_USER->uid)->fetch_single_value() > 0;
	}

	public function get_response($uid = NULL) {
		global $I2_SQL, $I2_USER;
		if ($uid === NULL)
			$uid = $I2_USER->uid;
		if ($this->myanswertype == 'free_response' || $this->myanswertype == 'short_response' || $this->myanswertype =='standard_other' || $this->myanswertype == 'identity')
			return $I2_SQL->query('SELECT written FROM '.static::$questiontype.'_votes WHERE pid=%d AND qid=%d AND uid=%d',$this->mypid,$this->myqid,$uid)->fetch_single_value();
		else if ($this->myanswertype == 'standard' || $this->myanswertype == 'standard_election')
			return $I2_SQL->query('SELECT aid FROM '.static::$questiontype.'_votes WHERE pid=%d AND qid=%d AND uid=%d',$this->mypid,$this->myqid,$uid)->fetch_single_value();
		else if ($this->myanswertype == 'approval')
			return $I2_SQL->query('SELECT aid FROM '.static::$questiontype.'_votes WHERE pid=%d AND qid=%d AND uid=%d ORDER BY aid DESC',$this->mypid,$this->myqid,$uid)->fetch_all_single_values();
		else if ($this->myanswertype == 'split_approval')
			return $I2_SQL->query('SELECT aid FROM '.static::$questiontype.'_votes WHERE pid=%d AND qid=%d AND uid=%d ORDER BY aid DESC',$this->mypid,$this->myqid,$uid)->fetch_all_single_values();
	}

	public function delete_vote($uid = NULL) {
		global $I2_SQL, $I2_USER;

		if ($uid === NULL)
			$uid = $I2_USER->uid;
		$exists = $I2_SQL->query('SELECT COUNT(*) FROM '.static::$questiontype.'_votes WHERE pid=%d AND qid=%d AND uid=%d',
			$this->mypid,$this->myqid,$uid)->fetch_single_value() > 0;
		if ($exists)
			$I2_SQL->query('DELETE FROM '.static::$questiontype.'_votes WHERE pid=%d AND qid=%d AND uid=%d',
				$this->mypid,$this->myqid,$uid);
	}

	public function vote($post, $uid = NULL) {
		global $I2_SQL, $I2_USER;

		if ($uid === NULL)
			$uid = $I2_USER->uid;
		switch ($this->myanswertype) {
		case 'free_response':
			$post = substr($post,0,min(strlen($post),10000)); // In case someone tries to put too much data in.
			$I2_SQL->query('INSERT INTO '.static::$questiontype.'_votes SET pid=%d,qid=%d,uid=%d,written=%s',
				$this->mypid, $this->myqid, $uid, $post);
			break;
		case 'short_response':
			$post = substr($post,0,min(strlen($post),1000)); // In case someone tries to put too much data in.
			$I2_SQL->query('INSERT INTO '.static::$questiontype.'_votes SET pid=%d,qid=%d,uid=%d,written=%s',
				$this->mypid, $this->myqid, $uid, $post);
			break;
		case 'standard':
		case 'standard_election':
			if($post >= 0) {
				$I2_SQL->query('INSERT INTO '.static::$questiontype.'_votes SET pid=%d,qid=%d,uid=%d,aid=%d',
					$this->mypid, $this->myqid, $uid, $post);
			}
			break;
		case 'approval':
		case 'split_approval':
			foreach ($post as $aid)
				$I2_SQL->query('INSERT INTO '.static::$questiontype.'_votes SET pid=%d,qid=%d,uid=%d,aid=%d',
					$this->mypid, $this->myqid, $uid, $aid);
			break;
		case 'standard_other':
			if(is_int($post) && $post>=0){
				$I2_SQL->query('INSERT INTO '.static::$questiontype.'_votes SET pid=%d,qid=%d,uid=%d,written=%s',
					$this->mypid,$this->myqid,$uid,$post);
			} else {
				$post = substr($post,0,min(strlen($post),100)); // In case someone tries to put too much data in.
				$I2_SQL->query('INSERT INTO '.static::$questiontype.'_votes SET pid=%d,qid=%d,uid=%d,written=%s',
					$this->mypid, $this->myqid, $uid, $post);
			}
			break;
		case 'identity':
			$idstr = $I2_USER->username . " (" . $I2_USER->fullname .") " . is_array($I2_USER->mail) ? $I2_USER->mail[0] : $I2_USER->mail;
			$I2_SQL->query('INSERT INTO '.static::$questiontype.'_votes SET pid=%d,qid=%d,uid=%d,written=%s',
				$this->mypid,$this->myqid,$uid,$idstr);
			break;
		default:
			throw new I2Exception("Voted in a question of invalid type (".$this->myanswertype.")");
		}
	}

	private $num_cache = NULL;
	/**
	 * This function returns the number of votes for a particular answer
	 * based on the grade and gender.
	 */
	public function num_who_vote($aid, $grade, $gender) {
		global $I2_SQL;

		$gender = ($gender===NULL?'':" AND gender='$gender'");
		switch ($this->myanswertype) {
		case 'standard':
		case 'approval':
		case 'standard_election':
		case 'identity':
			return $I2_SQL->query('SELECT COUNT(*) FROM '.static::$questiontype.'_votes '
				.'WHERE pid=%d AND qid=%d AND aid=%d AND '.
				'grade=%s'.$gender, $this->mypid, $this->myqid,
				$aid, $grade)->fetch_single_value();
		case 'split_approval':
			if ($this->num_cache === NULL) {
				$temp = $I2_SQL->query('SELECT uid, 1/COUNT(aid)'.
					'FROM '.static::$questiontype.'_votes WHERE pid=%d AND qid=%d'.
					' GROUP BY uid',$this->mypid,
					$this->myqid)->fetch_all_arrays();
				$this->num_cache = [];
				foreach ($temp as $row)
					$this->num_cache[$row[0]] = $row[1];
			}
			$res = $I2_SQL->query('SELECT uid FROM '.static::$questiontype.'_votes WHERE pid=%d AND qid=%d AND aid=%d '.
				'AND grade=%s'.$gender ,$this->mypid, $this->myqid, $aid, $grade)->fetch_all_single_values();
			$sum = 0;
			foreach ($res as $p)
				$sum += $this->num_cache[$p];
			if ($sum == 0)
				return 0;
			return sprintf("%.3f",$sum);
		}
	}

	public function num_who_voted() {
		global $I2_SQL;

		return $I2_SQL->query('SELECT COUNT(DISTINCT uid) FROM '.static::$questiontype.'_votes WHERE pid=%d AND qid=%d',$this->mypid,$this->myqid)->fetch_single_value();
	}

	public function get_all_votes() {
		global $I2_SQL;

		$votes = [];
		$res = $I2_SQL->query('SELECT written AS answer, LOWER(grade) AS grade FROM '.static::$questiontype.'_votes WHERE pid=%d AND qid=%d AND written IS NOT NULL',$this->mypid,$this->myqid)->fetch_all_arrays();
		foreach ($res as $r) {
			$vote = [];
			$vote['vote'] = $r['answer'];
			if ($r['grade'] == 'staff') {
				$vote['grade'] = 'Teacher';
			} else {
				$vote['grade'] = $r['grade'] . 'th grader';
			}
			$vote['numgrade'] = $r['grade'];
			$votes[] = $vote;
		}
		return $votes;
	}
}
?>
