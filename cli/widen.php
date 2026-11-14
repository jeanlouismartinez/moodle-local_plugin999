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
 * Command line: check or widen the columns now.
 *
 * @package    local_plugin999
 * @copyright  2026 Jean-Louis Martinez <jeanlouis@martinez.pro>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/clilib.php');

use local_plugin999\local\form_patcher;
use local_plugin999\local\widener;

[$options, $unrecognised] = cli_get_params(
    ['check' => false, 'restore-forms' => false, 'help' => false],
    ['c' => 'check', 'h' => 'help']
);

if ($unrecognised) {
    cli_error(get_string('cliunknowoption', 'admin', implode(PHP_EOL . '  ', $unrecognised)));
}
if ($options['help']) {
    cli_writeln(get_string('clihelp', 'local_plugin999'));
    exit(0);
}
if (moodle_needs_upgrading()) {
    cli_error(get_string('errorupgradepending', 'local_plugin999'));
}

if ($options['restore-forms']) {
    // Puts the three files of the core back as published, and turns the setting off so that they stay that way.
    set_config('patchforms', 0, 'local_plugin999');
    $report = form_patcher::restore();
    foreach ($report as $row) {
        cli_writeln(widener::line($row));
    }
    exit(widener::has_errors($report) ? 1 : 0);
}

$report = widener::run((bool) $options['check']);
if (form_patcher::enabled()) {
    $report = array_merge($report, form_patcher::run((bool) $options['check']));
} else {
    cli_writeln(get_string('formsoff', 'local_plugin999'));
}
foreach ($report as $row) {
    cli_writeln(widener::line($row));
}
exit(widener::has_errors($report) ? 1 : 0);
