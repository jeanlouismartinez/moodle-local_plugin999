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
 * Lifts the limit of 100 characters that the code of the core puts on the short name of competencies and frameworks.
 *
 * @package    local_plugin999
 * @copyright  2026 Jean-Louis Martinez <jeanlouis@martinez.pro>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_plugin999\local;

/**
 * Edits three files of the core, and in each only the lines that limit the short name.
 *
 * The limit is written in the code, not in the database, so a wider column does not lift it: the two forms of tool_lp
 * refuse a longer name, and the CSV import of tool_lpimportcsv cuts it. A Moodle upgrade writes the original files
 * back: the scheduled task then edits them again. Nothing is touched unless the setting is on, the lines are found as
 * published (whatever number they hold), and the columns already hold the target length. The limit of the ID number
 * is left alone: its column still holds 100 characters.
 *
 * @package    local_plugin999
 * @copyright  2026 Jean-Louis Martinez <jeanlouis@martinez.pro>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class form_patcher {
    /** @var int The limit the core writes in its code. */
    public const CORE_LIMIT = 100;

    /** @var string What stands for the limit in the lines below. */
    private const NUMBER = '{LIMIT}';

    /** @var int Seconds after which a temporary file left by an interrupted run is removed. */
    private const STALE = 3600;

    /** @var string|null Directory holding the files when it is not the one of the site: only the tests set it. */
    protected static $basedir = null;

    /**
     * Makes the class work on a copy of the files instead of the files of the site. For the tests only.
     *
     * @param string|null $dir Directory standing for the public directory of Moodle, or null for the site itself.
     */
    public static function set_base_dir(?string $dir): void {
        self::$basedir = $dir;
    }

    /**
     * The files of the core that are edited, relative to the directory of the public files, and in each the lines that
     * hold the limit. Every line must be found exactly once, whatever number it holds.
     *
     * @return array[] Lines by file.
     */
    public static function edits(): array {
        $form = [
            "\$mform->addElement('text', 'shortname', get_string('shortname', 'tool_lp'), 'maxlength=\"{LIMIT}\"');",
            "\$mform->addRule('shortname', get_string('maximumchars', '', {LIMIT}), 'maxlength', {LIMIT}, 'client');",
        ];
        return [
            'admin/tool/lp/classes/form/competency.php' => $form,
            'admin/tool/lp/classes/form/competency_framework.php' => $form,
            'admin/tool/lpimportcsv/classes/framework_importer.php' => [
                "\$framework->shortname = shorten_text(clean_param(\$shortname, PARAM_TEXT), {LIMIT});",
                "\$competency->shortname = shorten_text(clean_param(\$shortname, PARAM_TEXT), {LIMIT});",
            ],
        ];
    }

    /**
     * Whether the setting that allows editing the files of the core is on.
     *
     * @return bool
     */
    public static function enabled(): bool {
        return (bool) get_config('local_plugin999', 'patchforms');
    }

    /**
     * Brings the code to the target limit.
     *
     * @param bool $checkonly True to report without changing anything.
     * @return array[] One row per file: ['table' => string, 'status' => string, 'message' => string].
     */
    public static function run(bool $checkonly = false): array {
        global $DB;

        // Code that accepts more than the column holds would fail on save: the columns come first.
        foreach (['competency', 'competency_framework'] as $tablename) {
            $columns = $DB->get_columns($tablename, false);
            if (!isset($columns['shortname']) || (int) $columns['shortname']->max_length < widener::TARGET) {
                // In check mode the columns have not been widened yet: the files are still to do, nothing went wrong.
                return [[
                    'table' => 'forms',
                    'status' => $checkonly ? widener::TODO : widener::ERROR,
                    'message' => $checkonly
                        ? widener::msg('formswait')
                        : widener::msg('formsnocolumn', ['table' => $tablename, 'target' => widener::TARGET]),
                ]];
            }
        }
        return self::apply(widener::TARGET, $checkonly);
    }

    /**
     * Puts the code back to the limit of the core.
     *
     * @return array[] One row per file, as run().
     */
    public static function restore(): array {
        return self::apply(self::CORE_LIMIT, false);
    }

    /**
     * The state of the files, for display. Nothing is changed.
     *
     * With the setting on this is what a run would do. With the setting off nothing will be done, so the rows say so
     * instead of announcing a change that will not come.
     *
     * @return array[] One row per file, as run().
     */
    public static function status(): array {
        if (self::enabled()) {
            return self::run(true);
        }
        $report = self::apply(self::CORE_LIMIT, true);
        foreach ($report as &$row) {
            if ($row['status'] === widener::OK) {
                $row['message'] = widener::msg('formpublished', ['file' => $row['table'], 'limit' => self::CORE_LIMIT]);
            } else if ($row['status'] === widener::TODO) {
                $row['status'] = widener::NOTICE;
                $row['message'] = widener::msg('formstilledited', $row['table']);
            }
        }
        unset($row);
        return $report;
    }

    /**
     * Called when the setting is saved: edits the files straight away when it is turned on, puts them back when it is
     * turned off. Without this the change would wait for the next run of the scheduled task, and turning the setting
     * off would leave the files edited.
     *
     * @param string $name Full name of the setting, not used.
     */
    public static function setting_updated(string $name = ''): void {
        $report = self::enabled() ? self::run() : self::restore();
        foreach ($report as $row) {
            if ($row['status'] === widener::ERROR) {
                \core\notification::error(s($row['message']));
            }
        }
    }

    /**
     * Brings the lines of every file to a limit.
     *
     * @param int $to The limit to write.
     * @param bool $checkonly True to report without changing anything.
     * @return array[] One row per file.
     */
    protected static function apply(int $to, bool $checkonly): array {
        global $CFG;

        $report = [];
        foreach (self::edits() as $relative => $lines) {
            $path = (self::$basedir ?? $CFG->dirroot) . '/' . $relative;
            try {
                [$status, $message] = self::apply_to_file($path, $relative, $lines, $to, $checkonly);
            } catch (\Throwable $e) {
                $status = widener::ERROR;
                $message = widener::msg('formexception', ['file' => $relative, 'error' => $e->getMessage()]);
            }
            $report[] = ['table' => $relative, 'status' => $status, 'message' => $message];
        }
        return $report;
    }

    /**
     * The pattern that finds a line whatever number it holds; the numbers are the captured groups.
     *
     * @param string $line A line of edits().
     * @return string
     */
    private static function pattern(string $line): string {
        return '/' . str_replace(preg_quote(self::NUMBER, '/'), '(\d+)', preg_quote($line, '/')) . '/';
    }

    /**
     * Brings the lines of one file to a limit.
     *
     * @param string $path Full path.
     * @param string $relative Path shown in the messages.
     * @param string[] $lines The lines of the file that hold the limit.
     * @param int $to The limit to write.
     * @param bool $checkonly True to report without changing anything.
     * @return string[] Status and message.
     */
    protected static function apply_to_file(string $path, string $relative, array $lines, int $to, bool $checkonly): array {
        if (!is_file($path) || !is_readable($path)) {
            return [widener::ERROR, widener::msg('formnotfound', $relative)];
        }
        $content = file_get_contents($path);
        if ($content === false) {
            return [widener::ERROR, widener::msg('formunreadable', $relative)];
        }

        // Every line must be there exactly once. The number it holds may be the one of the core, the target, or the
        // one an earlier version of the plugin wrote: all are brought to the limit asked for.
        $found = [];
        foreach ($lines as $line) {
            if (preg_match_all(self::pattern($line), $content, $matches, PREG_SET_ORDER) !== 1) {
                // Not the lines that were published: Moodle changed this code, and guessing would be worse than
                // doing nothing.
                return [widener::ERROR, widener::msg('formunexpected', $relative)];
            }
            $numbers = array_unique(array_map('intval', array_slice($matches[0], 1)));
            if (count($numbers) !== 1) {
                return [widener::ERROR, widener::msg('formunexpected', $relative)];
            }
            $found[] = reset($numbers);
        }
        $different = array_values(array_filter($found, fn($number) => $number !== $to));
        if (!$different) {
            return [widener::OK, widener::msg('formok', ['file' => $relative, 'limit' => $to])];
        }
        $from = $different[0];
        if ($checkonly) {
            return [widener::TODO, widener::msg('formtodo', ['file' => $relative, 'from' => $from, 'to' => $to])];
        }
        if (!is_writable($path) || !is_writable(dirname($path))) {
            return [widener::ERROR, widener::msg('formnotwritable', $relative)];
        }

        $edited = $content;
        foreach ($lines as $line) {
            $edited = preg_replace_callback(
                self::pattern($line),
                fn() => str_replace(self::NUMBER, (string) $to, $line),
                $edited
            );
            if ($edited === null) {
                return [widener::ERROR, widener::msg('formunexpected', $relative)];
            }
        }

        // Temporary files left by a run that was interrupted before its rename.
        foreach (glob($path . '.plugin999.*.tmp') ?: [] as $stale) {
            if (time() - (int) @filemtime($stale) > self::STALE) {
                @unlink($stale);
            }
        }

        // Written beside the file, then renamed over it: a reader never sees half a file.
        $temp = $path . '.plugin999.' . bin2hex(random_bytes(6)) . '.tmp';
        if (file_put_contents($temp, $edited) !== strlen($edited)) {
            @unlink($temp);
            return [widener::ERROR, widener::msg('formtempfail', $relative)];
        }
        $mode = fileperms($path);
        if ($mode !== false) {
            @chmod($temp, $mode & 0777);
        }
        if (!rename($temp, $path)) {
            @unlink($temp);
            return [widener::ERROR, widener::msg('formreplacefail', $relative)];
        }
        if (function_exists('opcache_invalidate')) {
            @opcache_invalidate($path, true);
        }
        if (self::$basedir === null && defined('CLI_SCRIPT') && CLI_SCRIPT) {
            // The line above only reached the cache of the command line: the web server keeps its own.
            opcache::mark();
        }
        return [widener::FIXED, widener::msg('formfixed', ['file' => $relative, 'from' => $from, 'to' => $to])];
    }
}
