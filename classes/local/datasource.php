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

namespace report_feedback_tracker\local;
use stdClass;

/**
 * This file contains functions to get the date for the feedback tracker report.
 *
 * @package    report_feedback_tracker
 * @copyright  2024 UCL <m.opitz@ucl.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class datasource {
    /**
     * Get the Feedback Tracker data for all enrolled users of a given course.
     *
     * @param int $courseid
     * @return stdClass
     */
    public static function get_feedback_tracker_admin_data($courseid) {
        global $OUTPUT, $PAGE;

        $data = new stdClass();
        $data->records = [];

        // Get the students of the course.
        $sdata = new stdClass();
        $context = \context_course::instance($courseid);
        $users = get_enrolled_users($context);
        $sdata->students = [];
        foreach ($users as $user) {
            // Check if the user has no managerial or supervising capabilities (e.g. is a student).
            if (!has_capability('gradereport/grader:view', $context, $user) &&
                !has_capability('moodle/course:manageactivities', $context, $user) &&
                !has_capability('enrol/category:synchronised', $context, $user) &&
                !has_capability('moodle/course:view', $context, $user)
            ) {
                $sdata->students[] = $user;
            } else { // If a user has a managerial or supervising role check if there is (also) a student role.
                $roles = get_user_roles($context, $user->id, true);
                foreach ($roles as $role) {
                    if (strstr($role->shortname, 'student')) {
                        $sdata->students[] = $user;
                        break;
                    }
                }
            }
        }

        // Render the drop down menu for switching into student view.
        $data->studentdd = $OUTPUT->render_from_template('report_feedback_tracker/studentdropdown', $sdata);

        // Check if the user is in edit mode.
        $data->editmode = $PAGE->user_is_editing();

        $course = get_course($courseid);
        // Get the gradings and append them to the data.
        helper::get_admin_course_gradings($course, $data);

        return $data;
    }

    /**
     * Get the Feedback tracker data for one or all courses of a given user.
     *
     * @param int $userid
     * @param int $courseid
     * @return stdClass
     */
    public static function get_feedback_tracker_user_data($userid, $courseid = 0) {
        $data = new stdClass();
        $data->records = [];
        $data->courses = [];

        // Check if we want to show a module header.
        $data->modheader = get_config('report_feedback_tracker', 'modheader');

        // If a course ID is given return data for that course only
        // otherwise return data for all courses a user is enrolled in.
        if ($courseid) {
            $course = get_course($courseid);
            helper::get_user_course_gradings($course, $userid, $data);
        } else {
            $enrolledcourses = enrol_get_users_courses($userid);
            foreach ($enrolledcourses as $course) {
                helper::get_user_course_gradings($course, $userid, $data);
            }
        }

        // Sort the courses by name.
        if (is_array($data->courses)) {
            usort($data->courses, function($a, $b) {
                return strcmp($a->fullname, $b->fullname);
            });
        }

        return $data;
    }

}
