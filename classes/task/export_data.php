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
 * Batch create adhoc tasks to export data.
 *
 * @package    report_feedback_tracker
 * @copyright  2024 UCL <m.opitz@ucl.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_feedback_tracker\task;

use core\exception\moodle_exception;
use core\task\manager;
use core\task\scheduled_task;
use local_assess_type\assess_type;
use report_feedback_tracker\local\admin;
use report_feedback_tracker\local\helper;
use stdClass;

/**
 * Task to write data to a file.
 */
class export_data extends scheduled_task {
    /**
     * Get the task name.
     *
     * @return string
     */
    public function get_name(): string {
        return get_string('data:export', 'report_feedback_tracker');
    }

    /**
     * Get the courses for the given academic year.
     * @param int $year
     * @return array
     */
    protected function get_courses(int $year): array {
        global $DB;
        $sql = "SELECT c.id, c.category, c.fullname
                  FROM {customfield_data} cfd
                  JOIN {context} ctx ON cfd.contextid = ctx.id AND ctx.contextlevel = :contextcourse
                  JOIN {course} c ON c.id = ctx.instanceid
                  JOIN {customfield_field} cff ON cfd.fieldid = cff.id
                  WHERE cff.shortname = 'course_year' AND cfd.value = :acyear";
        $params = ['contextcourse' => CONTEXT_COURSE, 'acyear' => $year];
        return $DB->get_records_sql($sql, $params);
    }

    /**
     * Execute the task.
     */
    public function execute() {

        $academicyear = (int)get_config('report_feedback_tracker', 'export_academicyear') ?: helper::get_current_academic_year();
        $previousyear = $academicyear - 1;
        $academicyears = [$previousyear, $academicyear];

        foreach ($academicyears as $acyear) {
            $courses = $this->get_courses($acyear);
            mtrace('Found ' . count($courses) . ' courses for academic year ' . $acyear);

            foreach ($courses as $course) {
                mtrace('Spawning adhoc task for course ' . $course->id);
                $task = new process_export();
                $task->set_custom_data(['courseid' => $course->id, 'academicyear' => $acyear]);
                manager::queue_adhoc_task($task, true);
            }
        }
    }
}
