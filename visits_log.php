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
 * Show the visits log.
 *
 * @package    report_feedback_tracker
 * @copyright  2025 onwards UCL {@link https://www.ucl.ac.uk/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Matthias Opitz <m.opitz@ucl.ac.uk>
 */

use report_feedback_tracker\form\visits_log_filters;

require('../../config.php');
require_once($CFG->libdir . '/csvlib.class.php');
require_once($CFG->libdir . '/adminlib.php');

require_login();

if (!is_siteadmin()) {
    redirect(new moodle_url('/report/feedback_tracker/index.php'));
}

admin_externalpage_setup(
    'feedback_tracker_visits',
    '',
    ['search' => ''],
    '',
    ['pagelayout' => 'report']
);

$context = context_system::instance();
$PAGE->set_url('/report/feedback_tracker/visits_log.php');
$PAGE->set_context($context);
$PAGE->set_title(get_string('visitslog:title', 'report_feedback_tracker'));
$PAGE->set_heading(get_string('visitslog:heading', 'report_feedback_tracker'));

$sql = "SELECT *
        FROM {logstore_standard_log}
        WHERE component = :component
        AND action = :action
        ORDER BY timecreated DESC";
$params = ['component' => 'report_feedback_tracker', 'action' => 'viewed'];

$allrecords = $DB->get_records_sql($sql, $params);

// CSV export.
$download = optional_param('download', '', PARAM_ALPHA);
if ($download === 'csv') {
    require_sesskey();
    $csv = new csv_export_writer('browser');
    $csv->filename = 'feedback_tracker_logs_' . date('Y-m-d') . '.csv';

    $csv->add_data([
        'Time',
        'User',
        'Programme',
    ]);

    foreach ($allrecords as $log) {
        $user = fullname(core_user::get_user($log->userid));
        $other = json_decode($log->other);
        $programme = $other->programme ?? get_string('visitslog:no_programme', 'report_feedback_tracker');

        $csv->add_data([
            date_format_string((int) $log->timecreated, '%Y-%m-%d %H:%M:%S'),
            $user,
            $programme,
        ]);
    }

    $csv->download_file();
    exit;
}

// Pagination.
$recordsperpage = 25;
$page = optional_param('page', 0, PARAM_INT);
$totalrecords = count($allrecords);

$start = $page * $recordsperpage;
$pagedrecords = array_slice($allrecords, $start, $recordsperpage);

// Build table data for template.
$records = [];
foreach ($pagedrecords as $log) {
    $records[] = [
        'time'      => userdate($log->timecreated),
        'user'      => fullname(core_user::get_user($log->userid)),
        'programme' => (json_decode($log->other)->programme ?? get_string('visitslog:no_programme', 'report_feedback_tracker')),
    ];
}

$pager = new paging_bar($totalrecords, $page, $recordsperpage, $PAGE->url);

$templatecontext = [
    'records'   => $records,
    'pagingbar' => $OUTPUT->render($pager),
    'csvurl'    => new moodle_url($PAGE->url, ['download' => 'csv', 'sesskey' => sesskey()]),
];

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('report_feedback_tracker/visits_log', $templatecontext);
echo $OUTPUT->footer();
