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

namespace report_feedback_tracker\external;

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_value;
use stdClass;

/**
 * External API for updating additional information for a module.
 *
 * @package    report_feedback_tracker
 * @copyright  2024 onwards University College London {@link https://www.ucl.ac.uk/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Matthias Opitz <m.opitz@ucl.ac.uk>
 */
class update_module extends external_api {
    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            'gradeitemid' => new external_value(PARAM_INT, 'The ID of the grade item'),
            'partid' => new external_value(PARAM_INT, 'The part ID used by turnitintooltwo only, 0 otherwise'),
            'contact' => new external_value(PARAM_TEXT, 'An optional contact'),
            'method' => new external_value(PARAM_TEXT, 'An optional method'),
            'hidden' => new external_value(PARAM_BOOL, 'Hidden from report'),
            'assessmenttype' => new external_value(PARAM_INT, 'The assessment type'),
            'feedbackduedate' => new external_value(PARAM_RAW, 'The feedback due date'),
            'generalfeedback' => new external_value(PARAM_TEXT, 'An optional feedback for all participants'),
        ]);
    }

    /**
     * Returns description of method result value.
     *
     * @return \external_value
     */
    public static function execute_returns() {
        return new external_value(PARAM_TEXT, 'Status message');
    }

    /**
     * Saving the feedback due date and the reason for it for a grade item.
     *
     * @param int $gradeitemid
     * @param int $partid
     * @param string $contact
     * @param string $method
     * @param bool $hidden
     * @param int $assessmenttype
     * @param int|null $feedbackduedate
     * @param string $generalfeedback
     * @return string
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public static function execute(int $gradeitemid, int $partid, string $contact, string $method, bool $hidden,
                                   int $assessmenttype, int|null $feedbackduedate, string $generalfeedback): string {
        global $DB;

        if ($record = $DB->get_record('report_feedback_tracker', ['gradeitem' => $gradeitemid, 'partid' => $partid])) {
            $record->responsibility = $contact;
            $record->method = $method;
            $record->hidden = $hidden;
            $record->assessmenttype = $assessmenttype;
            $record->feedbackduedate = $feedbackduedate > -1 ? $feedbackduedate : null;
            $record->generalfeedback = $generalfeedback;
            $DB->update_record('report_feedback_tracker', $record);
        } else {
            $record = new stdClass();
            $record->gradeitem = clean_param($gradeitemid, PARAM_INT);
            $record->partid = clean_param($partid, PARAM_INT);
            $record->responsibility = clean_param($contact, PARAM_TEXT);
            $record->method = clean_param($method, PARAM_TEXT);
            $record->hidden = clean_param($hidden, PARAM_BOOL);
            $record->assessmenttype = clean_param($assessmenttype, PARAM_INT);
            $record->feedbackduedate = $feedbackduedate > -1 ? clean_param($feedbackduedate, PARAM_INT) : null;
            $record->generalfeedback = clean_param($generalfeedback, PARAM_TEXT);
            $DB->insert_record('report_feedback_tracker', $record);
        }
        return true;
    }
}
