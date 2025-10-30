/**
 * Clean Grade Report JavaScript
 *
 * @module     local_cleangradereport/print_grades
 * @copyright  2024
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery'], function($) {
    'use strict';

    /**
     * Initialize the print grades functionality
     */
    function init() {
        // Add print button to grade report page
        addPrintButton();
    }

    /**
     * Add print button to the grade report
     */
    function addPrintButton() {
        // Wait for page to be ready
        $(document).ready(function() {
            // Look for the grade report content
            var gradeContent = $('.path-grade-report-user, .grade-report-user, [data-region="grade-report"]');
            
            if (gradeContent.length > 0) {
                // Get current URL parameters
                var urlParams = new URLSearchParams(window.location.search);
                var userid = urlParams.get('userid');
                var courseid = urlParams.get('id');
                
                if (!userid) {
                    // Try to get from different parameter name or use current user
                    userid = $('input[name="userid"]').val() || M.cfg.userid;
                }
                
                if (courseid && userid) {
                    // Create print button
                    var printUrl = M.cfg.wwwroot + '/local/cleangradereport/print_report.php?courseid=' + 
                                  courseid + '&userid=' + userid;
                    
                    var printButton = $('<div class="mb-3 local-cleangradereport-print-button">' +
                        '<a href="' + printUrl + '" target="_blank" class="btn btn-primary">' +
                        '<i class="fa fa-print" aria-hidden="true"></i> Print Clean Report' +
                        '</a></div>');
                    
                    // Try different locations to insert the button
                    var insertLocation = $('.grade-report-user table').first();
                    if (insertLocation.length > 0) {
                        insertLocation.before(printButton);
                    } else {
                        // Fallback - insert at top of grade content
                        gradeContent.first().prepend(printButton);
                    }
                }
            }
        });
    }

    return {
        init: init
    };
});