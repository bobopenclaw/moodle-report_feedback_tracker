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
 * Steps definitions related to editing mode.
 *
 * @package    report_feedback_tracker
 * @copyright  2024 UCL <m.opitz@ucl.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Exception\ExpectationException;

/**
 * Steps definitions related to editing mode.
 *
 * @package    report_feedback_tracker
 * @copyright  2024 UCL <m.opitz@ucl.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_report_feedback_tracker extends behat_base {

    /**
     * Select an option from a dropdown menu.
     *
     * @When I select :option from the :dropdown dropdown
     * @param string $option
     * @param string $dropdown
     */
    public function i_select_from_the_dropdown($option, $dropdown) {
        $this->getSession()->getPage()->selectFieldOption($dropdown, $option);
    }

    /**
     * Create custom field.
     *
     * @param  TableNode $table
     * @throws \dml_exception
     *
     * @Given /^the following custom field exists:$/
     */
    public function the_following_custom_field_exists(TableNode $table): void {
        global $DB;

        $data = $table->getRowsHash();

        // Create a new custom field category if it doesn't exist.
        $category = $DB->get_record(
            'customfield_category',
            ['name' => $data['category'],
                'component' => 'core_course',
                'area' => 'course']);

        if (!$category) {
            $category     = (object)[
                'name'         => $data['category'],
                'component'    => 'core_course',
                'area'         => 'course',
                'sortorder'    => 1,
                'timecreated'  => time(),
                'timemodified' => time(),
            ];
            $category->id = $DB->insert_record(
                'customfield_category',
                $category
            );
        }

        // Create the custom field.
        $field = (object)[
            'shortname' => $data['shortname'],
            'name' => $data['name'],
            'type' => $data['type'],
            'categoryid' => $category->id,
            'sortorder' => 0,
            'configdata'   => json_encode([
                "required" => 0,
                "uniquevalues" => 0,
                "maxlength" => 4,
                "defaultvalue" => "",
                "ispassword" => 0,
                "displaysize" => 4,
                "locked" => 1,
                "visibility" => 0,
            ]),
            'timecreated' => time(),
            'timemodified' => time(),
        ];
        $DB->insert_record('customfield_field', $field);
    }

}
