<?php
// This file is part of Moodle - http://moodle.org/
// Complete lib.php for Clean Grade Report Plugin

defined('MOODLE_INTERNAL') || die();

/**
 * Add clean grade report button to grade reports
 */
function local_cleangradereport_extend_navigation_user_settings($navigation, $user, $usercontext, $course, $coursecontext) {
    global $PAGE, $USER;
    
    // Only add if we're viewing grades and have permission
    if ($PAGE->url->get_path() === '/grade/report/user/index.php') {
        $userid = optional_param('userid', $USER->id, PARAM_INT);
        $courseid = required_param('id', PARAM_INT);
        
        if (has_capability('gradereport/user:view', $coursecontext)) {
            $url = new moodle_url('/local/cleangradereport/print_report.php', 
                array('userid' => $userid, 'courseid' => $courseid));
            
            $printnode = navigation_node::create(
                get_string('printcleanreport', 'local_cleangradereport'),
                $url,
                navigation_node::TYPE_SETTING,
                null,
                'cleangradereport',
                new pix_icon('i/report', '')
            );
            
            $navigation->add_node($printnode);
        }
    }
}

/**
 * Inject JavaScript for print button
 */
function local_cleangradereport_before_footer() {
    global $PAGE, $CFG, $USER;
    
    if ($PAGE->url->get_path() === '/grade/report/user/index.php') {
        $userid = optional_param('userid', $USER->id, PARAM_INT);
        $courseid = required_param('id', PARAM_INT);
        
        // Make sure we have a valid userid
        if (empty($userid)) {
            $userid = $USER->id;
        }
        
        // Inject JavaScript directly instead of using AMD
        $js = "
        document.addEventListener('DOMContentLoaded', function() {
            var gradeContent = document.querySelector('.path-grade-report-user, .grade-report-user, [data-region=\"grade-report\"]');
            if (gradeContent) {
                var printUrl = '{$CFG->wwwroot}/local/cleangradereport/print_report.php?courseid={$courseid}&userid={$userid}';
                var printButton = document.createElement('div');
                printButton.className = 'mb-3 local-cleangradereport-print-button';
                printButton.innerHTML = '<a href=\"' + printUrl + '\" target=\"_blank\" class=\"btn btn-primary\"><i class=\"fa fa-print\" aria-hidden=\"true\"></i> Print Clean Report</a>';
                
                var table = document.querySelector('.grade-report-user table, .generaltable');
                if (table) {
                    table.parentNode.insertBefore(printButton, table);
                } else {
                    gradeContent.insertBefore(printButton, gradeContent.firstChild);
                }
            }
        });";
        
        $PAGE->requires->js_init_code($js);
    }
}
/**
 * Get clean grade data for a user in a course
 */
function local_cleangradereport_get_grade_data($userid, $courseid) {
    global $DB, $CFG;
    
    require_once($CFG->libdir . '/gradelib.php');
    require_once($CFG->dirroot . '/grade/lib.php');
    require_once($CFG->dirroot . '/grade/report/user/lib.php');
    
    $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
    $user = $DB->get_record('user', array('id' => $userid), '*', MUST_EXIST);
    
    $context = context_course::instance($courseid);
    
    // Initialize grade tree normally
    $gtree = new grade_tree($courseid, false, false);
    
    $data = array();
    $data['studentname'] = fullname($user);
    $data['coursename'] = $course->fullname;
    $data['items'] = array();
    
    // Process the grade tree
    if (isset($gtree->top_element['children'])) {
        foreach ($gtree->top_element['children'] as $element) {
            local_cleangradereport_process_grade_items($element, $userid, $data['items'], 0);
        }
    }
    
    return $data;
}

/**
 * Recursively process grade items to match the desired format
 */
function local_cleangradereport_process_grade_items($element, $userid, &$items, $level = 0) {
    global $CFG;
    
    if ($element['type'] == 'category') {
        $category = $element['object'];
        
        // Skip the root course category
        if ($level == 0 && ($category->fullname == '?' || empty(trim($category->fullname)))) {
            if (isset($element['children'])) {
                foreach ($element['children'] as $child) {
                    local_cleangradereport_process_grade_items($child, $userid, $items, $level);
                }
            }
            return;
        }
        
        // Check if category is hidden from students
        if ($category->is_hidden()) {
            return;
        }
        
        // Add category header
        if (!empty(trim($category->fullname)) && $category->fullname != '?') {
            $items[] = array(
                'type' => 'category',
                'name' => $category->fullname,
                'weight' => '',
                'grade' => '',
                'lettergrade' => '',
                'iscategory' => true,
                'level' => $level
            );
        }
        
        // Process child items
        if (isset($element['children'])) {
            foreach ($element['children'] as $child) {
                local_cleangradereport_process_grade_items($child, $userid, $items, $level + 1);
            }
        }
        
        // Add category total if not root level
        if ($level > 0 && !empty(trim($category->fullname)) && $category->fullname != '?') {
            $gradeitem = $category->load_grade_item();
            
            // Skip if grade item is hidden
            if ($gradeitem->is_hidden()) {
                return;
            }
            
            $grade = new grade_grade(array('itemid' => $gradeitem->id, 'userid' => $userid));
            
            if ($grade->id && !is_null($grade->finalgrade)) {
                $percentage = ($grade->finalgrade / $gradeitem->grademax) * 100;
                $gradestr = number_format($percentage, 2) . '% (' . 
                           grade_format_gradevalue($grade->finalgrade, $gradeitem, true, GRADE_DISPLAY_TYPE_LETTER) . ')';
                
                $lettergrade = grade_format_gradevalue($grade->finalgrade, $gradeitem, true, GRADE_DISPLAY_TYPE_LETTER);
                $lettergrade = preg_replace('/[()]/', '', $lettergrade);
                $lettergrade = trim($lettergrade);
                
                $weight = '';
                if (isset($element['weight']) && $element['weight'] > 0) {
                    $weight = number_format($element['weight'], 2) . '%';
                }
                
                $items[] = array(
                    'type' => 'total',
                    'name' => $category->fullname . ' total',
                    'weight' => $weight,
                    'grade' => $gradestr,
                    'lettergrade' => $lettergrade,
                    'iscategory' => false,
                    'level' => $level
                );
            }
        }
        
    } else if ($element['type'] == 'item') {
        $item = $element['object'];
        
        // Skip category items and hidden items
        if ($item->itemtype == 'category' || $item->is_hidden()) {
            return;
        }
        
        // Get the user's grade
        $grade = new grade_grade(array('itemid' => $item->id, 'userid' => $userid));
        
        $gradestr = '';
        $lettergrade = '';
        $weight = '';
        
        // Calculate weight percentage
        if (isset($element['weight']) && $element['weight'] > 0) {
            $weight = number_format($element['weight'], 2) . '%';
        }
        
        if ($grade->id) {
            if (!is_null($grade->finalgrade)) {
                if ($item->grademax > 0) {
                    $percentage = ($grade->finalgrade / $item->grademax) * 100;
                    $letterval = grade_format_gradevalue($grade->finalgrade, $item, true, GRADE_DISPLAY_TYPE_LETTER);
                    $gradestr = number_format($percentage, 2) . '% (' . $letterval . ')';
                    
                    $lettergrade = preg_replace('/[()]/', '', $letterval);
                    $lettergrade = trim($lettergrade);
                } else {
                    $gradestr = grade_format_gradevalue($grade->finalgrade, $item, true);
                    $lettergrade = grade_format_gradevalue($grade->finalgrade, $item, true, GRADE_DISPLAY_TYPE_LETTER);
                    $lettergrade = preg_replace('/[()]/', '', $lettergrade);
                    $lettergrade = trim($lettergrade);
                }
            } else {
                $gradestr = '( Empty )';
                $lettergrade = '-';
            }
        } else {
            $gradestr = '( Empty )';
            $lettergrade = '-';
        }
        
        $items[] = array(
            'type' => 'item',
            'name' => $item->itemname,
            'weight' => $weight,
            'grade' => $gradestr,
            'lettergrade' => $lettergrade,
            'iscategory' => false,
            'level' => $level
        );
    }
}

/**
 * Hook to add CSS for the plugin
 */
function local_cleangradereport_before_http_headers() {
    global $PAGE;
    
    if ($PAGE->url->get_path() === '/grade/report/user/index.php') {
        $PAGE->requires->css('/local/cleangradereport/styles.css');
    }
}