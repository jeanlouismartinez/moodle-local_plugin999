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
 * Settings.
 *
 * @package    local_plugin999
 * @copyright  2026 Jean-Louis Martinez <jeanlouis@martinez.pro>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_plugin999', get_string('pluginname', 'local_plugin999'));

    if ($ADMIN->fulltree) {
        // The state of the columns and of the files, read only when this very page is shown: reading it costs a few
        // queries, and the tree is also built for other pages. Nothing is changed here.
        $shown = optional_param('section', '', PARAM_ALPHANUMEXT) === 'local_plugin999';
        $report = $shown ? \local_plugin999\local\widener::run(true) : [];
        $report = $shown ? array_merge($report, \local_plugin999\local\form_patcher::status()) : [];
        $lines = '';
        foreach ($report as $row) {
            $lines .= html_writer::tag('li', s(\local_plugin999\local\widener::line($row)));
        }
        $settings->add(new admin_setting_heading(
            'local_plugin999/status',
            get_string('status', 'local_plugin999'),
            html_writer::tag('ul', $lines) . get_string('status_desc', 'local_plugin999')
        ));

        $setting = new admin_setting_configcheckbox(
            'local_plugin999/patchforms',
            get_string('patchforms', 'local_plugin999'),
            get_string('patchforms_desc', 'local_plugin999'),
            0
        );
        $setting->set_updatedcallback('local_plugin999\\local\\form_patcher::setting_updated');
        $settings->add($setting);
    }

    $ADMIN->add('localplugins', $settings);
}
