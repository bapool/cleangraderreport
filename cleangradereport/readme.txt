Clean Grade Report - Local Moodle Plugin
=========================================

Author: Brian A. Pool, National Trail Local Schools
Moodle versions: 4.3+
Plugin version: 1.1.0
License: GPL v3

Description
-----------
The Clean Grade Report plugin provides a streamlined, print-friendly grade report 
designed for parent-teacher conferences and student grade discussions. It removes 
Moodle navigation clutter and presents grades in a clean, professional format that 
focuses on what matters most - student achievement.

Key Features
------------
* Clean, condensed print layout optimized for 1-2 pages instead of 8+
* Removes student navigation links from printed reports
* Student name and course name prominently displayed at top
* Hierarchical display of grade categories and items
* Bold formatting for category totals
* Prominent highlighting of final course grade at bottom
* Hides grade items that are hidden from students
* Respects Moodle restrictions (groups, conditional access, etc.)
* Shows only items visible to the selected student
* Optional display of empty/ungraded items
* Works with "View as User" mode for accurate student view
* No Moodle headers/footers in print view
* Professional table format without borders
* Print-optimized fonts and spacing

Installation
------------
1. Extract the zip file to your Moodle local/ directory
   Result: /path/to/moodle/local/cleangradereport/

2. Set proper permissions:
   chmod -R 755 local/cleangradereport/

3. Log in as administrator

4. Navigate to Site Administration > Notifications

5. Follow the installation prompts

6. The plugin will be installed and ready to use

Usage
-----
1. Navigate to any course

2. Go to Grades > User report

3. Select a student (or view your own grades)

4. Click the "Print Clean Report" button at the top of the grade table

5. A new window opens with the clean report

6. Click "Print Report" to print or save as PDF

7. Use your browser's print dialog to print or save

Configuration Options
---------------------
The plugin works out of the box with no configuration needed. However, 
administrators and teachers can modify behavior by editing lib.php:

* Show/Hide Empty Grades: Located in the local_cleangradereport_process_grade_items() 
  function, look for the TOGGLE comments to enable/disable showing assignments 
  without grades.

* Adjust Formatting: Modify print_report.php CSS styles to customize fonts, 
  colors, spacing, and layout.

Technical Details
-----------------
* Plugin Type: local
* Component: local_cleangradereport
* Requires: Moodle 4.3 or higher, PHP 8.0+
* Database: No database tables required
* JavaScript: Inline injection (no AMD compilation needed)
* Caching: Respects Moodle cache settings

File Structure
--------------
local/cleangradereport/
├── version.php                           # Plugin version information
├── lib.php                               # Core functionality and grade processing
├── print_report.php                      # Print report generation and display
├── styles.css                            # Styling for print button
├── lang/en/local_cleangradereport.php   # English language strings
└── README.txt                            # This file

Permissions
-----------
The plugin uses existing Moodle capabilities:
* gradereport/user:view - View grade reports
* moodle/grade:viewall - View all student grades (for teachers)

No additional capabilities are required.

Troubleshooting
---------------
**Button doesn't appear:**
- Clear browser cache and Moodle caches
- Ensure you're on a grade report page
- Verify you have viewing permissions

**Division by zero error:**
- Fixed in version 1.1.0
- Ensure you're running the latest version

**Print formatting issues:**
- Use landscape orientation for better results
- Ensure browser print settings don't override CSS
- Try different browsers (Chrome recommended)

**Items showing that shouldn't:**
- Version 1.1.0 respects all Moodle restrictions
- Ensure activities have proper restriction settings
- Items hidden from students won't appear

Changelog
---------
Version 1.1.0 (2024-10-22)
* Added support for Moodle activity restrictions
* Fixed division by zero error for items with grademax of 0
* Added visibility checking for group restrictions
* Improved handling of conditional access rules
* Enhanced empty grade display
* Fixed course total highlighting
* Improved print CSS for better formatting

Version 1.0.0 (2024-09-25)
* Initial release
* Clean print layout
* Category and course totals
* Hide empty grades option
* Print button integration

Support
-------
For bug reports, feature requests, or contributions:
* Moodle.org plugin page: [URL will be added after submission]
* Issues: Report via Moodle.org tracker

License
-------
This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.

Credits
-------
Developed by Brian A. Pool
National Trail Local Schools
Ohio, United States

This plugin was created to solve a real-world need for cleaner, 
more professional grade reports for parent-teacher conferences 
and student meetings.