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
 * PHPUnit process export test
 *
 * @package    report_feedback_tracker
 * @category   test
 * @copyright  2026 UCL <m.opitz@ucl.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers \report_feedback_tracker
 */

namespace report_feedback_tracker;

use advanced_testcase;
use report_feedback_tracker\task\process_export;

/**
 * Unit test for the process to export data
 */
final class process_export_test extends advanced_testcase {
    /**
     * Setup
     *
     * @return void
     */
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest(true);
        $this->setAdminUser();
    }

    /**
     * Test that execute initialises the course properly
     *
     * @covers \process_export
     * @return void
     */
    public function test_execute_initialises_course_property(): void {
        // Minimal course setup.
        $testcourse = $this->getDataGenerator()->create_course();

        // Configure export path.
        $exportpath = make_temp_directory('rft_init_test');
        set_config('export_path', $exportpath, 'report_feedback_tracker');

        // Create task with valid custom data.
        $year = 2024;
        $task = new process_export();
        $task->set_custom_data((object)[
            'courseid' => $testcourse->id,
            'academicyear' => $year,
        ]);

        // If $this->exportcourse is not initialised, execute() will fail.
        $task->execute();

        // If we get here, $this->exportcourse was initialised correctly.
        $summative = $exportpath . "/feedback_tracker_report_{$year}_{$testcourse->id}_summative.json";
        $formative = $exportpath . "/feedback_tracker_report_{$year}_{$testcourse->id}_formative.json";
        $this->assertFileExists($summative);
        $this->assertFileExists($formative);
    }
}
