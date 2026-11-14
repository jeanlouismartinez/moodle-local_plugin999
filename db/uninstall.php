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
 * Uninstallation: put the forms of the core back as published.
 *
 * @package    local_plugin999
 * @copyright  2026 Jean-Louis Martinez <jeanlouis@martinez.pro>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Puts the three files of the core back to their limit of 100 characters. The columns are left widened: narrowing them
 * would cut the names entered meanwhile.
 *
 * @return bool
 */
function xmldb_local_plugin999_uninstall() {
    foreach (\local_plugin999\local\form_patcher::restore() as $row) {
        if ($row['status'] === \local_plugin999\local\widener::ERROR) {
            debugging('local_plugin999: ' . $row['message'], DEBUG_NORMAL);
        }
    }
    return true;
}
