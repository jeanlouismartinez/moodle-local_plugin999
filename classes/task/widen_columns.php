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
 * Scheduled task: keep the short names widened.
 *
 * @package    local_plugin999
 * @copyright  2026 Jean-Louis Martinez <jeanlouis@martinez.pro>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_plugin999\task;

use local_plugin999\local\form_patcher;
use local_plugin999\local\widener;

/**
 * Checks the columns and widens them again if a Moodle upgrade or a restore narrowed them; when the setting is on,
 * does the same for the limit written in the forms.
 *
 * @package    local_plugin999
 * @copyright  2026 Jean-Louis Martinez <jeanlouis@martinez.pro>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class widen_columns extends \core\task\scheduled_task {
    /**
     * Name shown in the list of scheduled tasks.
     *
     * @return string
     */
    public function get_name(): string {
        return get_string('taskwiden', 'local_plugin999');
    }

    /**
     * Runs the check.
     */
    public function execute(): void {
        $columns = widener::run();
        $files = form_patcher::enabled() ? form_patcher::run() : [];
        foreach (array_merge($columns, $files) as $row) {
            mtrace(widener::line($row));
        }
        // A column that could not be widened fails the task, so that it is tried again and the administrators are told.
        // A file of the core that Moodle changed would fail it at every run for good: it is only reported, here and on
        // the settings page.
        if (widener::has_errors($columns)) {
            throw new \moodle_exception('errorwiden', 'local_plugin999');
        }
    }
}
