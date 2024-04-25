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

/**
 * This function extends the course navigation with the report items
 *
 * @param navigation_node $navigation The navigation node to extend
 * @param stdClass $course The course to object for the report
 * @param stdClass $context The context of the course
 */
function report_feedback_tracker_extend_navigation_course($navigation, $course, $context) {
    if (has_capability('report/feedback_tracker:view', $context)) {
        $url = new moodle_url('/report/feedback_tracker/index.php', ['id' => $course->id]);
        $navigation->add(get_string('pluginname', 'report_feedback_tracker'),
            $url, navigation_node::TYPE_SETTING, null, null, new pix_icon('i/report', ''));
    }
}

/**
 * This function extends the course navigation with the report items
 *
 * @param navigation_node $navigation The navigation node to extend
 * @param stdClass $user
 * @param stdClass $course The course to object for the report
 */
function report_feedback_tracker_extend_navigation_user($navigation, $user, $course) {
    global $USER;

    if (isguestuser() || !isloggedin()) {
        return;
    }

    if (\core\session\manager::is_loggedinas() || $USER->id != $user->id) {
        // No peeking at somebody else's sessions!
        return;
    }

    $context = context_course::instance($course->id);
    if (has_capability('report/feedback_tracker:view', $context) || true) {
        $navigation->add(get_string('navigationlink', 'report_feedback_tracker'),
            new moodle_url('/report/feedback_tracker/user.php'), $navigation::TYPE_SETTING);
    }
}

/**
 * Add nodes to myprofile page.
 *
 * @param \core_user\output\myprofile\tree $tree Tree object
 * @param stdClass $user user object
 * @param bool $iscurrentuser
 * @param stdClass $course Course object
 *
 * @return bool
 */
function report_feedback_tracker_myprofile_navigation(core_user\output\myprofile\tree $tree, $user, $iscurrentuser, $course) {
    global $COURSE, $USER;

    if (isguestuser() || !isloggedin()) {
        return;
    }

    if (\core\session\manager::is_loggedinas() || $USER->id != $user->id) {
        // No peeking at somebody else's sessions!
        return;
    }

    $context = context_course::instance($COURSE->id);
    if (has_capability('report/feedback_tracker:view', $context) || true) {
        $node = new core_user\output\myprofile\node('reports', 'feedback_tracker',
            get_string('navigationlink', 'report_feedback_tracker'), null, new moodle_url('/report/feedback_tracker/user.php'));
        $tree->add_node($node);
    }
    return true;
}

/**
 * Is current user allowed to access this report
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
        if ($course->showreports && (is_viewing($coursecontext, $USER) || is_enrolled($coursecontext, $USER))) {
            return true;
        }
    } else if (has_capability('moodle/user:viewuseractivitiesreport', $personalcontext)) {
        if ($course->showreports && (is_viewing($coursecontext, $user) || is_enrolled($coursecontext, $user))) {
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

/**
 * Get the Feedback tracker data for a given user.
 *
 * @param stdClass $user
 * @return stdClass
 */
function get_feedback_tracker_data($user) {

    $data = new stdClass();
    $data->records = [];

    $oneday = 24 * 60 * 60; // Number of seconds in a day.
    $oneweek = 7 * $oneday; // Number of seconds in a week.
    $twoweeks = 2 * $oneweek; // Number of seconds in two weeks.

    // Retrieve enrolled courses for the user.
    $enrolledcourses = enrol_get_users_courses($user->id);

    foreach ($enrolledcourses as $enrolledcourse) {
        // Retrieve all modules (activities/resources) in the course.
        $coursemodules = get_course_mods($enrolledcourse->id);

        // Prepare the report data for each module.
        foreach ($coursemodules as $cm) {
            $module = get_module($cm);

            $duedate = isset($module->duedate) && date("Y-m-d", $module->duedate) != '1970-01-01' ?
                date("Y-m-d", $module->duedate) : '--';
            $duedate = $module->duedate;
            $feedbackduedate = $duedate ? $duedate + $twoweeks : $duedate;

            $record = new stdClass();
            $record->course = $enrolledcourse->shortname;
            $record->assessment = $module->name;
            $record->type = $cm->modname;
            $record->duedate = $duedate == 0 ? '--' : date("Y-m-d", $duedate);
            $record->feedbackduedate = $feedbackduedate == 0 ? '--' : date("Y-m-d", $feedbackduedate);
            $data->records[] = $record;
        }
    }

    return $data;
}

/**
 * Get information about the module instance.
 *
 * @param stdClass $cm
 * @return false|mixed|stdClass
 * @throws dml_exception
 */
function get_module($cm) {
    global $DB;

    // Handle special cases of module types here where needed.
    switch ($cm->modname) {
        case 'assign':
            $tablename = $cm->modname;
            break;
        case 'quiz':
            $tablename = $cm->modname;
            $replacements = ['timeclose' => 'duedate'];
            break;
        case 'special':
            // Do something specific here.
            break;
        default:
            $tablename = $cm->modname;
            break;
    }

    $module = $DB->get_record($tablename, ['id' => $cm->instance]);

    if (isset($replacements)) {
        foreach ($replacements as $from => $to) {
            $module->$to = $module->$from;
        }
        unset($replacement);
    }

    return $module;
}

/**
 * Return some dummy data.
 *
 * @return stdClass
 */
function get_dummy_data() {
    // Get 10 lines of dummy data.
    $data = new stdClass();
    $data->records = [];

    $oneday = 24 * 60 * 60; // Number of seconds in a day.
    $oneweek = 7 * $oneday; // Number of seconds in a week.
    $twoweeks = 2 * $oneweek; // Number of seconds in two weeks.

    for ($i = 1; $i <= 10; $i++) {
        $record = new stdClass();
        $record->course = "Dummy Course";
        $record->assessment = "Dummy Assessment";
        $record->type = "dummy";
        $record->summative = "yes";
        $duedate = strtotime("-14 days");
        $record->duedate = date("Y-m-d", $duedate);
        $record->submissiondate = date("Y-m-d", $duedate - $oneday);
        $feedbackduedate = $duedate + $twoweeks;
        $record->feedbackduedate = date("Y-m-d", $feedbackduedate);
        $record->fullfeedback = "Full feedback here";
        $record->grade = "80/100";

        $data->records[] = $record;
    }

    return $data;
}
