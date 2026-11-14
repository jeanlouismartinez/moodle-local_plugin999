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
 * Tells the web server that files of the core were edited from the command line.
 *
 * @package    local_plugin999
 * @copyright  2026 Jean-Louis Martinez <jeanlouis@martinez.pro>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_plugin999\local;

/**
 * Makes the web server read again the files that the scheduled task or the command line edited.
 *
 * PHP keeps compiled code in a cache, and the web server has its own, apart from the one of the command line. Most
 * servers check the date of the files and notice an edit by themselves. Some are set not to (validate_timestamps off):
 * there the edited forms would not be seen by the site until PHP is reloaded. So an edit made outside the web server
 * leaves a mark, and the next page served by the site drops the three files from its cache.
 *
 * @package    local_plugin999
 * @copyright  2026 Jean-Louis Martinez <jeanlouis@martinez.pro>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class opcache {
    /** @var string Name of the setting that holds the mark. */
    public const FLAG = 'opcachepending';

    /**
     * Leaves the mark: the web server has files to read again.
     */
    public static function mark(): void {
        set_config(self::FLAG, time(), 'local_plugin999');
    }

    /**
     * Whether the mark is there.
     *
     * @return bool
     */
    public static function pending(): bool {
        return (bool) get_config('local_plugin999', self::FLAG);
    }

    /**
     * Called at the start of every request. Does nothing from the command line, whose cache is not the one of the site.
     *
     * @param \core\hook\after_config $hook
     */
    public static function after_config(\core\hook\after_config $hook): void {
        if ((defined('CLI_SCRIPT') && CLI_SCRIPT) || during_initial_install()) {
            return;
        }
        self::flush_pending();
    }

    /**
     * Drops the edited files from the cache of the process that runs this, and removes the mark.
     *
     * @return bool True when there was something to do.
     */
    public static function flush_pending(): bool {
        global $CFG;

        if (!self::pending()) {
            return false;
        }
        if (function_exists('opcache_invalidate')) {
            foreach (array_keys(form_patcher::edits()) as $relative) {
                @opcache_invalidate($CFG->dirroot . '/' . $relative, true);
            }
        }
        unset_config(self::FLAG, 'local_plugin999');
        return true;
    }
}
