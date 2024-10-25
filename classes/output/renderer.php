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
 * The renderer.
 *
 * @package    report_feedback_tracker
 * @copyright  2024 UCL <m.opitz@ucl.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_feedback_tracker\output;

use context_course;
use grade_item;
use local_assess_type\assess_type;
use plugin_renderer_base;
use report_feedback_tracker\local\admin;
use report_feedback_tracker\local\helper;
use report_feedback_tracker\local\user;
use stdClass;

/**
 * Renderer class for feedback tracker report table.
 */
class renderer extends plugin_renderer_base {

    /**
     * Render the user table.
     *
     * @param int $userid
     * @param int $courseid optional course id to limit output.
     * @return string
     * @throws \moodle_exception
     */
    public function render_feedback_tracker_user_data($userid, $courseid = 0): string {
        global $USER;

        // Course ID 1 is not a standard Moodle course and is excluded.
        if ($courseid < 2) {
            $courseid = 0;
        }
        // Get the table data.
        $feedbacktrackerdata = user::get_feedback_tracker_user_data($userid, $courseid);

        if ($courseid) { // Render a student view of single course as an editor.
            $context = context_course::instance($courseid);
            $feedbacktrackerdata->courseid = $courseid;
            $feedbacktrackerdata->canedit = has_capability('moodle/grade:edit', $context);
            $feedbacktrackerdata->viewasstudent = true;
            $feedbacktrackerdata->dropdownstudents = helper::get_students_for_dropdown($courseid, $userid);

            return $this->output->render_from_template('report_feedback_tracker/course/course',
                $feedbacktrackerdata);
        } else { // Render all courses for a student.
            $feedbacktrackerdata->canedit = false;
            // While there are more than one courses, remove the ones without assessments.
            // If there is only one course without assessments show it nevertheless.
            if ($feedbacktrackerdata->courses) {
                $coursesremoved = false;
                foreach ($feedbacktrackerdata->courses as $key => $course) {
                    // If there is only one course (left) do not remove it.
                    if (count($feedbacktrackerdata->courses) < 2) {
                        break;
                    }
                    if (empty($course->records)) { // If a course has no grade records, remove it from the report.
                        unset($feedbacktrackerdata->courses[$key]);
                        $coursesremoved = true;
                    }
                }
                // If any courses have been removed, re-index the array.
                if ($coursesremoved) {
                    $feedbacktrackerdata->courses = array_values($feedbacktrackerdata->courses);
                }
            }
            return $this->output->render_from_template('report_feedback_tracker/user/courses', $feedbacktrackerdata);
        }
    }

    /**
     * Render the user table.
     *
     * @param int $userid
     * @param int $courseid optional course id to limit output.
     * @return string
     * @throws \moodle_exception
     */
    public function render_feedback_tracker_userview_data($userid, $courseid = 0): string {
        // Get the table data.
        $feedbacktrackerdata = user::get_feedback_tracker_user_data($userid, $courseid);

        // If no course ID is provided, show assessments from all courses.
        // While there are more than one courses, remove the ones without assessments.
        // If there is only one course without assessments show it nevertheless.
        if (count($feedbacktrackerdata->courses) !== 1 && $courseid === 0) {
            $coursesremoved = false;
            foreach ($feedbacktrackerdata->courses as $key => $course) {
                if (empty($course->records)) {
                    unset($feedbacktrackerdata->courses[$key]);
                    $coursesremoved = true;
                }
            }
            // If we removed any courses, reindex the array.
            if ($coursesremoved) {
                $feedbacktrackerdata->courses = array_values($feedbacktrackerdata->courses);
            }
        }

        // Render the table data.
        $feedbacktrackerdata->viewasstudent = true;
        return $this->output->render_from_template('report_feedback_tracker/course/course',
            $feedbacktrackerdata);
    }

    /**
     * Render the wrapper containing the table for a course admin.
     *
     * @param int $courseid
     * @return string
     * @throws \moodle_exception
     */
    public function render_feedback_tracker_admin_wrapper($courseid): string {
        // Get the table data.
        $feedbacktrackerdata = admin::get_feedback_tracker_admin_data_old($courseid);
        $feedbacktrackerdata->courseid = $courseid;
        // Render the table data.
        if ($feedbacktrackerdata->editmode) {
            return $this->output->render_from_template('report_feedback_tracker/adminedittable', $feedbacktrackerdata);
        }
        return $this->output->render_from_template('report_feedback_tracker/adminwrapper', $feedbacktrackerdata);
    }

    public function render_feedback_tracker_admin(int $courseid): string {
        global $DB, $OUTPUT;

        $context = context_course::instance($courseid);
        $modinfo = get_fast_modinfo($courseid);

        $dateformat = get_config('report_feedback_tracker', 'dateformat');
        $assessmenttypes = helper::get_assessment_types($courseid);
        $users = get_enrolled_users($context);

        // Get all grade items for the course.
        $gradeitems = grade_item::fetch_all(['courseid' => $courseid]);

        $data = new stdClass();
        $data->courseid = $courseid;
        $data->staffdata = true;
        $data->canedit = true;
        $data->outputedit = true;
        $data->records = [];

        $data->dropdownstudents = helper::get_students_for_dropdown($courseid);

        // Create records for manual grade items and supported course modules.
        foreach ($gradeitems as $gradeitem) {

            // If it is a 'manual' grade item there is no course module.
            if ($gradeitem->itemtype === 'manual') {
                $record = new stdClass();
                $record->name = $gradeitem->itemname;
                $record->manual = true;
                $record->feedbackduedateraw = 9999999999; // Needed for sorting. Make sure they are listed last.

                $data->records[] = $record;
                continue;
            }

            // Skip any gradeitem without a module or a with module that is not suported.
            if (!$gradeitem->itemmodule || !helper::module_is_supported_new($gradeitem->itemmodule)) {
                continue;
            }

            if (!$record = admin::get_module_record($gradeitem, $modinfo, $assessmenttypes)) {
                continue;
            }

            // If it is a Turnitin module create a record for each part of it.
            if ($gradeitem->itemmodule === 'turnitintooltwo') {
                $tttparts = helper::get_tttparts_new($gradeitem);

                foreach ($tttparts as $tttpart) {
                    $record->name = $gradeitem->itemname . " - " . $tttpart->partname;
                    $record->partid = $tttpart->id;

                    $duedate = $tttpart->dtdue;
                    // The raw date is needed for sorting.
                    $record->feedbackduedateraw = $duedate ? helper::get_feedbackduedate_new($courseid, $duedate) : 9999999999;
                    $record->feedbackduedate = $duedate ? date($dateformat, $record->feedbackduedateraw) : false;

                    $data->records[] = clone $record;
                }
            } else {
                $data->records[] = $record;
            }
        }

        // Sort the data records by feedback due date.
        usort($data->records, function($a, $b) {
            return strcmp($a->feedbackduedateraw, $b->feedbackduedateraw);
        });

        return $this->output->render_from_template('report_feedback_tracker/course/course', $data);
    }

    /**
     * Render the feedback tracker admin table.
     *
     * @param int $courseid
     * @return string
     * @throws \moodle_exception
     */
    public function render_feedback_tracker_admin_table($courseid): string {
        // Get the table data.
        $feedbacktrackerdata = admin::get_feedback_tracker_admin_data_old($courseid);
        // Render the table data.
        return $this->output->render_from_template('report_feedback_tracker/admintable', $feedbacktrackerdata);
    }

}
