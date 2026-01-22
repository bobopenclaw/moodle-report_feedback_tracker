<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Hook callbacks for report_feedback_tracker
 *
 * @package    report_feedback_tracker
 * @copyright  2025 onwards UCL <m.opitz@ucl.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Andrew Hancox <andrewdchancox@googlemail.com>
 */

namespace report_feedback_tracker\local;

use ltiservice_gradebookservices\hook\lineitemreceived;
use ltiservice_gradebookservices\hook\scorereceived;
use ltiservice_gradebookservices\local\service\gradebookservices;

/**
 * Class to handle hook callbacks
 */
class hook_callbacks {
    /**
     * Handle score coming in from LTI source.
     *
     * @param scorereceived $hook
     * @return void
     * @throws \dml_exception
     */
    public static function scorereceived(scorereceived $hook): void {
        global $DB;

        if (empty(get_config('report_feedback_tracker', 'supportlti'))) {
            return;
        }

        if ($hook->grade_item->itemmodule !== 'lti') {
            return;
        }

        $reportfeedbacktrackerlti = $DB->get_record(
            "report_feedback_tracker_lti_usr",
            [
                "instanceid" => $hook->grade_item->iteminstance,
                'userid' => $hook->userid,
            ]
        );

        if (empty($reportfeedbacktrackerlti)) {
            $reportfeedbacktrackerlti = new \stdClass();
            $reportfeedbacktrackerlti->instanceid = $hook->grade_item->iteminstance;
            $reportfeedbacktrackerlti->userid = $hook->userid;
        }

        if (
            isset($hook->score->submission->submittedAt)
            &&
            gradebookservices::validate_iso8601_date($hook->score->submission->submittedAt)
        ) {
            $reportfeedbacktrackerlti->submittedat = strtotime($hook->score->submission->submittedAt);
        }

        if (isset($reportfeedbacktrackerlti->id)) {
            $DB->update_record('report_feedback_tracker_lti_usr', $reportfeedbacktrackerlti);
        } else {
            $DB->insert_record('report_feedback_tracker_lti_usr', $reportfeedbacktrackerlti);
        }
    }

    /**
     * Handle line item coming in from LTI source.
     *
     * @param lineitemreceived $hook
     * @return void
     * @throws \dml_exception
     */
    public static function lineitemreceived(lineitemreceived $hook): void {
        global $DB;

        if (empty(get_config('report_feedback_tracker', 'supportlti'))) {
            return;
        }

        if ($hook->grade_item->itemmodule !== 'lti') {
            return;
        }

        $reportfeedbacktrackerlti = $DB->get_record(
            "report_feedback_tracker_lti",
            [
                "instanceid" => $hook->grade_item->iteminstance,
            ]
        );

        if (empty($reportfeedbacktrackerlti)) {
            $reportfeedbacktrackerlti = new \stdClass();
            $reportfeedbacktrackerlti->instanceid = $hook->grade_item->iteminstance;
        }

        if (
            isset($hook->lineitem->endDateTime)
            &&
            gradebookservices::validate_iso8601_date($hook->lineitem->endDateTime)
        ) {
            $reportfeedbacktrackerlti->enddatetime = strtotime($hook->lineitem->endDateTime);
        }

        if (!empty($hook->lineitem->gradesReleased) && empty($reportfeedbacktrackerlti->gradesreleased)) {
            $reportfeedbacktrackerlti->gradesreleased = time();
        }

        if (isset($reportfeedbacktrackerlti->id)) {
            $DB->update_record('report_feedback_tracker_lti', $reportfeedbacktrackerlti);
        } else {
            $DB->insert_record('report_feedback_tracker_lti', $reportfeedbacktrackerlti);
        }
    }
}
