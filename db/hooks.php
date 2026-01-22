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

defined('MOODLE_INTERNAL') || die();

$callbacks = [
    [
        'hook' => \ltiservice_gradebookservices\hook\scorereceived::class,
        'callback' => [\report_feedback_tracker\local\hook_callbacks::class, 'scorereceived'],
        'priority' => 500,
    ],
    [
        'hook' => \ltiservice_gradebookservices\hook\lineitemreceived::class,
        'callback' => [\report_feedback_tracker\local\hook_callbacks::class, 'lineitemreceived'],
        'priority' => 500,
    ],
];
