<?php
/**
* Just contains the definition for the class {@link EighthSchedule}.
* @author The Intranet 2 Development Team <intranet2@tjhsst.edu>
* @copyright 2005 The Intranet 2 Development Team
* @package modules
* @subpackage Eighth
* @filesource
*/

/**
* The module that holds the utilities for an eighth period schedule.
* @package modules
* @subpackage Eighth
*/

class EighthSchedule {
	/**
	* Schedule an eighth period activity.
	*
	* @access public
	* @param int $blockid The block ID.
	* @param int $activityid The activity ID.
	* @param array $sponsors The sponsors for that activity for that block.
	* @param array $rooms The rooms for that activity for that block.
	*/
	public static function schedule_activity($blockid, $activityid, $sponsors = array(), $rooms = array()) {
		global $I2_SQL;
		if(!is_array($sponsors)) {
			$sponsors = array($sponsors);
		}
		if(!is_array($rooms)) {
			$rooms = array($rooms);
		}
		$result = $I2_SQL->query("REPLACE INTO eighth_block_map (bid,activityid,sponsors,rooms) VALUES (%d,%d,'%D','%D')", $blockid, $activityid, $sponsors, $rooms);
	}

	/**
	* Unschedule an eighth period activity.
	*
	* @access public
	* @param int $blockid The block ID.
	* @param int $activityid The activity ID.
	*/
	public static function unschedule_activity($blockid, $activityid) {
		global $I2_SQL;
		$result = $I2_SQL->query("DELETE FROM eighth_block_map WHERE bid=%d AND activityid=%d", $blockid, $activityid);
	}

	/**
	* Adds an absentee for a block.
	*
	* @access public
	* @param int $blockid The block ID.
	* @param int $userid The student's user ID.
	*/
	public static function add_absentee($blockid, $userid) {
		global $I2_SQL;
		$result = $I2_SQL->query("REPLACE INTO eighth_absentees (bid,userid) VALUES (%d,%d)", $blockid, $userid);
	}

	/**
	* Removes an absentee for a block.
	*
	* @access public
	* @param int $blockid The block ID.
	* @param int $userid The student's user ID.
	*/
	public static function remove_absentee($blockid, $userid) {
		global $I2_SQL;
		$result = $I2_SQL->query("DELETE FROM eighth_absentees WHERE bid=%d AND userid=%d", $blockid, $userid);
	}

	/**
	* Get the absentees for a block and activity.
	*
	* @access public
	* @param int $blockid The block ID.
	* @param int $activityid The activity ID.
	*/
	public static function get_absentees($blockid, $activityid) {
		global $I2_SQL;
		return flatten($I2_SQL->query("SELECT eighth_absentees.userid FROM eighth_absentees LEFT JOIN eighth_activity_map USING (userid,bid) WHERE eighth_absentees.bid=%d AND aid=%d", $blockid, $activityid)->fetch_all_arrays(Result::NUM));
	}

	/**
	* Get the absences for a student.
	*
	* @access public
	* @param int $userid The student's user ID.
	*/
	public static function get_absences($userid) {
		global $I2_SQL;
		return $I2_SQL->query("SELECT aid,eighth_activity_map.bid FROM eighth_absentees LEFT JOIN eighth_activity_map USING (userid,bid) WHERE eighth_absentees.userid=%d", $userid)->fetch_all_arrays(Result::NUM);
	}

	/**
	* Get the next eighth period date.
	*
	* @access public
	*/
	public static function get_next_date() {
		global $I2_SQL;
		$date = $I2_SQL->query("SELECT date FROM eighth_blocks WHERE date >= %t ORDER BY date,block LIMIT 1", date("Y-m-d"))->fetch_array(Result::NUM);
		return $date[0];
	}

	/**
	* Gets the activities a student is signed up for on a specific date.
	*
	* @access public
	* @param $userid The student's user ID.
	* @param $date The date to get activities for.
	*/
	public static function get_activities($userid, $starting_date = NULL, $number_of_days = 14) {
		global $I2_SQL;
		if($starting_date == NULL) {
			$starting_date = date("Y-m-d");
		}
		return $I2_SQL->query("SELECT aid,eighth_blocks.bid FROM eighth_activity_map LEFT JOIN eighth_blocks USING (bid) WHERE userid=%d AND date >= %t AND date <= ADDDATE(%t, INTERVAL %d DAY) ORDER BY date,block", $userid, $starting_date, $starting_date, $number_of_days)->fetch_all_arrays(Result::NUM);
	}

	/**
	* Counts the number of students in an activity and block.
	*
	* @access public
	* @param int $blockid The block ID.
	* @param int $activityid The activity ID.
	*/
	public static function count_members($blockid, $activityid) {
		global $I2_SQL;
		return $I2_SQL->query("SELECT userid FROM eighth_activity_map WHERE bid=%d AND aid=%d", $blockid, $activityid)->num_rows();
	}

	/**
	* Print the activity rosters for the given date and block(s).
	*
	* @access public
	* @param array bids
	*/
	public static function printActivityRosters($bids) {
		global $I2_SQL;
		$activities = EighthActivity::id_to_activity($I2_SQL->query("SELECT activityid,bid FROM eighth_block_map WHERE bid IN (%D) ORDER BY activityid,bid", $bids)->fetch_all_arrays(MYSQL_NUM));
		foreach($activities as $activity) {
			$members = User::id_to_user($activity->members);
			echo "<div style=\"display: none;\">
<pre>
\\documentclass[letterpaper,12pt]{article}
\\usepackage{fullpage}
\\pagestyle{empty}
\\textheight 11in
		
\\begin{document}
\\begin{tabular*}{6 in}{l@{\extracolsep{\fill}}cr@{\extracolsep{\fill}}}
& Class Roster & \\\\
Course No. {$activity->aid} & & {$activity->block_sponsors_comma} \\\\
Room {$activity->block_rooms_comma} & {$activity->name} - {$activity->comment} & {$activity->block->date} \\\\
Class Size " . count($activity->members) . " & & {$activity->block->block} Block \\\\*[1cm]
\\end{tabular*}

\\begin{tabular*}{6 in}{cl@{\extracolsep{\fill}}c}
Check if Present & Student Name (ID) & Grade \\\\
\\hline
\\hline
";
			foreach($members as $member) {
				echo "\hrulefill & {$member->name_comma} ({$member->uid}) & {$member->grade} \\\\\n";
			}
			echo "
\\end{tabular*}
\n\nFor additions to this roster, please call extension 5078.

Please underline or highlight the names and ID numbers of the students who are NO-SHOWS.

Thank you!

\\end{document}
</pre>
</div>\n";
		}
	}
}
