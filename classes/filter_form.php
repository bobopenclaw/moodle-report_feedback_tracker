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
 * Form to filter the feedback tracker report
 *
 * @package    report_feedback_tracker
 * @copyright  2024 UCL <m.opitz@ucl.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_feedback_tracker;

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->libdir . '/formslib.php');

/**
 * Class filter_form form to filter the results by date
 *
 * @package report_feedback_tracker
 */
class filter_form extends \moodleform {
    /**
     * Form definition
     *
     * @return void
     * @throws \HTML_QuickForm_Error
     * @throws \coding_exception
     */
    protected function definition() {
        $mform = $this->_form;

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('header', 'filterheader', get_string('filter'));
        $opts = ['optional' => true];
        $mform->addElement('date_selector', 'filterstartdate', get_string('fromdate'), $opts);
        $mform->addElement('date_selector', 'filterenddate', get_string('todate'), $opts);

        $mform->setExpanded('filterheader', false);

        // Add the filter/cancel buttons (without 'closeHeaderBefore', so they collapse with the filter).
        $buttonarray = [
            $mform->createElement('submit', 'submitbutton', get_string('filter')),
            $mform->createElement('cancel'),
        ];
        $mform->addGroup($buttonarray, 'buttonar', '', [' '], false);
    }

    /**
     * Expand the form contents if the filter is in use.
     *
     * @return void
     * @throws \HTML_QuickForm_Error
     */
    public function definition_after_data() {
        $mform = $this->_form;
        $filterstartdate = $mform->getElement('filterstartdate')->getValue();
        $filterenddate = $mform->getElement('filterenddate')->getValue();
        if (!empty($filterstartdate['enabled']) || !empty($filterenddate['enabled'])) {
            $mform->setExpanded('filterheader', true);
        }
    }
}
