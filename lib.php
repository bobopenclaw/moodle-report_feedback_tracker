<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This file contains public API of feedback_tracker report
 *
 * @package    report_feedback_tracker
 * @copyright  2024 UCL <m.opitz@ucl.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * This function extends the course navigation with the report items
 *
 * @param navigation_node $navigation The navigation node to extend
 * @param stdClass $course The course to object for the report
 * @param stdClass $context The context of the course
 */
function report_feedback_tracker_extend_navigation_course($navigation, $course, $context) {
    if (has_capability('report/feedback_tracker:view', $context)) {
        $url = new moodle_url('/report/feedback_tracker/index.php', array('id'=>$course->id));
        $navigation->add(get_string('pluginname', 'report_feedback_tracker'), $url, navigation_node::TYPE_SETTING, null, null, new pix_icon('i/report', ''));
    }
}

/**
 * Is current user allowed to access this report
 *
 * @private defined in lib.php for performance reasons
 *
 * @param stdClass $user
 * @param stdClass $course
 * @return bool
 */
function report_feedback_tracker_can_access_user_report($user, $course) {
    global $USER;

    $coursecontext = context_course::instance($course->id);
    $personalcontext = context_user::instance($user->id);

    if ($user->id == $USER->id) {
        if ($course->showreports and (is_viewing($coursecontext, $USER) or is_enrolled($coursecontext, $USER))) {
            return true;
        }
    } else if (has_capability('moodle/user:viewuseractivitiesreport', $personalcontext)) {
        if ($course->showreports and (is_viewing($coursecontext, $user) or is_enrolled($coursecontext, $user))) {
            return true;
        }

    }

    // Check if $USER shares group with $user (in case separated groups are enabled and 'moodle/site:accessallgroups' is disabled).
    if (!groups_user_groups_visible($course, $user->id)) {
        return false;
    }

    if (has_capability('report/feedback_tracker:viewuserreport', $coursecontext)) {
        return true;
    }

    return false;
}

/**
 * Callback to verify if the given instance of store is supported by this report or not.
 *
 * @param string $instance store instance.
 *
 * @return bool returns true if the store is supported by the report, false otherwise.
 */
function report_feedback_tracker_supports_logstore($instance) {
    if ($instance instanceof \core\log\sql_internal_table_reader) {
        return true;
    }
    return false;
}

function get_data($user) {

    $data = new stdClass();
    $data->records = [];

    $one_day = 24 * 60 * 60; // Number of seconds in a day.
    $one_week = 7 * $one_day; // Number of seconds in a week.
    $two_weeks = 2 * $one_week; // Number of seconds in two weeks.

    // Retrieve enrolled courses for the user
    $enrolled_courses = enrol_get_users_courses($user->id);

    foreach ($enrolled_courses as $enrolled_course) {
        $course_modules = get_course_mods($enrolled_course->id);

        foreach ($course_modules as $cm) {
            $module = get_module($cm);
            $record = new stdClass();
            $record->course = $enrolled_course->shortname;
            $record->assessment = $module->name;
            $record->type = $cm->modname;
            $record->duedate = isset($module->duedate) &&  date("Y-m-d", $module->duedate) != '1970-01-01' ?
                date("Y-m-d", $module->duedate) : '--';

            $data->records[] = $record;
        }
    }
    // Retrieve all modules (activities/resources) in the course


//    $data = get_dummy_data();

    return $data;
}

function get_module($cm) {
    global $DB;

    // Handle special cases of module types here where needed.
    switch ($cm->modname) {
        case 'special':
            // Do something specific here.
            break;
        default:
            $tablename = $cm->modname;
            break;
    }

    $module = $DB->get_record($tablename, ['id' => $cm->instance]);
    return $module;
}

function get_dummy_data() {
    // Get 10 lines of dummy data.
    $data = new stdClass();
    $data->records = [];

    $one_day = 24 * 60 * 60; // Number of seconds in a day.
    $one_week = 7 * $one_day; // Number of seconds in a week.
    $two_weeks = 2 * $one_week; // Number of seconds in two weeks.


    for ($i=1; $i <= 10; $i++) {
        $record = new stdClass();
        $record->course = "Dummy Course";
        $record->assessment = "Dummy Assessment";
        $record->type = "dummy";
        $record->summative = "yes";
        $duedate = strtotime("-14 days");
        $record->duedate = date("Y-m-d", $duedate);
        $record->submissiondate = date("Y-m-d", $duedate - $one_day);
        $feedbackduedate = $duedate + $two_weeks;
        $record->feedbackduedate = date("Y-m-d", $feedbackduedate);
        $record->fullfeedback = "Full feedback here";
        $record->grade = "80/100";

        $data->records[] = $record;
    }

    return $data;
}