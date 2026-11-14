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
 * English strings.
 *
 * @package    local_plugin999
 * @copyright  2026 Jean-Louis Martinez <jeanlouis@martinez.pro>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['clihelp'] = 'Brings the short name of competencies and competency frameworks to 999 characters, and that of outcomes to 750.

Options:
-c, --check   Report without changing anything
--restore-forms  Put the three files of Moodle back and turn the setting off
-h, --help    Print this help

Example:
\$ php public/local/plugin999/cli/widen.php --check';
$string['errorupgradepending'] = 'A Moodle upgrade is pending. Finish it, then run this again. Nothing was changed.';
$string['errorwiden'] = 'At least one short name column could not be widened. See the lines above.';
$string['formsoff'] = 'Forms and CSV import: left as they are (the setting is off).';
$string['msg_columnfixed'] = '{$a->table}: the short name held {$a->from} characters, widened to {$a->to}';
$string['msg_columnnotfound'] = 'short name column not found in table {$a}';
$string['msg_columnok'] = '{$a->table}: the short name holds {$a->length} characters';
$string['msg_columnstill'] = '{$a->table}: the short name still holds {$a->length} characters after the change';
$string['msg_columntodo'] = '{$a->table}: the short name holds {$a->length} characters, to be widened to {$a->target}';
$string['msg_formexception'] = '{$a->file}: {$a->error}';
$string['msg_formfixed'] = '{$a->file}: short name was limited to {$a->from} characters, now {$a->to}';
$string['msg_formnotfound'] = '{$a}: file not found or not readable';
$string['msg_formnotwritable'] = '{$a}: file or directory not writable';
$string['msg_formok'] = '{$a->file}: short name limited to {$a->limit} characters';
$string['msg_formpublished'] = '{$a->file}: short name limited to {$a->limit} characters, as published by Moodle (the setting is off)';
$string['msg_formreplacefail'] = '{$a}: the file could not be replaced';
$string['msg_formsnocolumn'] = 'forms and CSV import left as they are: in table {$a->table}, the short name does not hold {$a->target} characters';
$string['msg_formstilledited'] = '{$a}: still edited while the setting is off; save the setting again, or run cli/widen.php --restore-forms to put it back';
$string['msg_formswait'] = 'forms and CSV import: to be edited once the columns are widened';
$string['msg_formtempfail'] = '{$a}: the temporary file could not be written';
$string['msg_formtodo'] = '{$a->file}: short name limited to {$a->from} characters, to be brought to {$a->to}';
$string['msg_formunexpected'] = '{$a}: the lines to edit were not found as expected, file left as it is';
$string['msg_formunreadable'] = '{$a}: the file could not be read';
$string['msg_indexmissing'] = '{$a}: the unique index (courseid, shortname) is missing';
$string['msg_indexrestored'] = '{$a}: unique index (courseid, shortname) restored';
$string['msg_rowformat'] = '{$a->table} uses the row format "{$a->format}": the unique index would not fit {$a->target} characters, table left as it is';
$string['msg_tablenotfound'] = 'table {$a} not found';
$string['patchforms'] = 'Lift the limit of the forms and of the CSV import';
$string['patchforms_desc'] = 'The code of Moodle itself limits the short name to 100 characters: the forms of competencies and competency frameworks refuse a longer name, and the CSV import of frameworks cuts it. When this is on, the plugin edits three files of the core (admin/tool/lp/classes/form/competency.php, competency_framework.php, and admin/tool/lpimportcsv/classes/framework_importer.php) so that they accept 999 characters, and edits them again after each Moodle upgrade. The ID number keeps its limit of 100 characters. The files are edited as soon as this setting is saved. Turning it off puts the three files back at once; so does uninstalling the plugin.';
$string['pluginname'] = '999';
$string['privacy:metadata'] = 'The plugin stores no personal data.';
$string['status'] = 'Current state';
$string['status_desc'] = 'OK: in place. TO DO: will be done at the next run of the scheduled task (every hour). ERROR: to be looked at, the message says why. NOTICE: nothing will change by itself.';
$string['status_error'] = 'ERROR';
$string['status_fixed'] = 'FIXED';
$string['status_notice'] = 'NOTICE';
$string['status_ok'] = 'OK';
$string['status_todo'] = 'TO DO';
$string['statusline'] = '{$a->status}: {$a->message}';
$string['taskwiden'] = 'Keep the short names of competencies and frameworks (999 characters) and outcomes (750) widened';
