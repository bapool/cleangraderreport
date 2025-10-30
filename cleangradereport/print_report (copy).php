<?php
// This file is part of Moodle - http://moodle.org/

require_once('../../config.php');
require_once($CFG->libdir . '/gradelib.php');
require_once('lib.php');

$userid = required_param('userid', PARAM_INT);
$courseid = required_param('courseid', PARAM_INT);

// Debug: Check if we have valid parameters
if (empty($userid) || empty($courseid)) {
    echo "Debug: userid = " . $userid . ", courseid = " . $courseid;
    die();
}

$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
$user = $DB->get_record('user', array('id' => $userid), '*', MUST_EXIST);

require_login($course);

$context = context_course::instance($courseid);
require_capability('gradereport/user:view', $context);

// If not viewing own grades, need additional capability
if ($userid != $USER->id) {
    require_capability('moodle/grade:viewall', $context);
}

// Get grade data
$data = local_cleangradereport_get_grade_data($userid, $courseid);

// Set up page
$PAGE->set_url('/local/cleangradereport/print_report.php', array('userid' => $userid, 'courseid' => $courseid));
$PAGE->set_context($context);
$PAGE->set_pagelayout('print');
$PAGE->set_title(get_string('gradereport', 'local_cleangradereport'));

// Don't show header/footer for clean print
echo '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>' . $data['studentname'] . ' - ' . $data['coursename'] . '</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 10px; 
            font-size: 10pt;
            line-height: 1.0;
        }
        .header { 
            text-align: center; 
            font-size: 16pt; 
            font-weight: bold; 
            margin-bottom: 15px; 
        }
        table { 
            width: 100%; 
            border-collapse: collapse;
            margin: 0;
            padding: 0;
            page-break-inside: auto;
        }
        td { 
            padding: 1px 3px; 
            vertical-align: top;
            border: none;
            font-size: 10pt;
            line-height: 1.0;
        }
        .category { 
            font-weight: bold; 
            background-color: #f0f0f0;
            page-break-inside: avoid;
        }
        .category td {
            padding: 2px 3px;
        }
        .item td {
            padding: 0px 3px;
        }
        @media print {
            body { 
                margin: 0.25in; 
                font-size: 9pt;
            }
            .header {
                margin-bottom: 10px;
                font-size: 14pt;
            }
            td {
                font-size: 9pt;
                padding: 0px 2px;
            }
        }
        .print-button {
            margin-bottom: 20px;
            text-align: center;
        }
        .print-button button {
            padding: 10px 20px;
            font-size: 14px;
            background-color: #0066cc;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .print-button button:hover {
            background-color: #0052a3;
        }
    </style>
    <script>
        function printReport() {
            window.print();
        }
        
        // Auto-focus for keyboard users
        window.onload = function() {
            document.getElementById("printBtn").focus();
        }
    </script>
</head>
<body>';

// Print button (will be hidden when printing)
echo '<div class="print-button no-print">
    <button id="printBtn" onclick="printReport()">Print Report</button>
    <br><br>
	<a href="' . $CFG->wwwroot . '/grade/report/user/index.php?id=' . $courseid . '&userid=' . $userid . '">Back to Grade Report</a>
</div>';

// Header with student name and course
echo '<div class="header">' . htmlspecialchars($data['studentname']) . ' - ' . htmlspecialchars($data['coursename']) . '</div>';

// Grade table with exact formatting requested
echo '<table>';

foreach ($data['items'] as $item) {
    if ($item['iscategory']) {
        // Category header row
        echo '<tr class="category">
            <td colspan="4"><strong>' . htmlspecialchars($item['name']) . '</strong></td>
        </tr>';
    } else {
        // Individual grade item
        $indent = isset($item['level']) && $item['level'] > 1 ? str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $item['level'] - 1) : '';
        
        // Format the row exactly like: CH 0 Class | Ch 0.1-Our background...yours and mine | 2.38 % | 100 % (A) | A
        echo '<tr class="item">
            <td style="width: 40%;">' . $indent . htmlspecialchars($item['name']) . '</td>
            <td style="width: 15%; text-align: center;">' . htmlspecialchars($item['weight']) . '</td>
            <td style="width: 30%; text-align: center;">' . htmlspecialchars($item['grade']) . '</td>
            <td style="width: 15%; text-align: center;">' . htmlspecialchars($item['lettergrade']) . '</td>
        </tr>';
    }
}

echo '</table>';

echo '</body></html>';