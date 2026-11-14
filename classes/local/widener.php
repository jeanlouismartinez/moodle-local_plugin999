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
 * Widens the short name of competencies and competency frameworks to 999 characters, and that of outcomes to 750.
 *
 * @package    local_plugin999
 * @copyright  2026 Jean-Louis Martinez <jeanlouis@martinez.pro>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_plugin999\local;

use xmldb_field;
use xmldb_index;
use xmldb_table;

/**
 * Checks the four columns and widens the ones that are narrower than their target.
 *
 * Nothing is done to a column that already holds the target length or more, so this can run as often as wanted:
 * at installation, from the scheduled task, from the command line. Columns are never narrowed, not even when the
 * plugin is uninstalled: narrowing would cut the names that were entered meanwhile.
 *
 * @package    local_plugin999
 * @copyright  2026 Jean-Louis Martinez <jeanlouis@martinez.pro>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class widener {
    /** @var int The longest short name of a competency or a competency framework, and the length of the first column. */
    public const TARGET = 999;

    /**
     * @var int The length of the column of the frameworks. It is wider than the longest name: duplicating a framework
     * adds a suffix to its name ("(copy)" in English), and the copy of a framework with the longest name must still fit.
     */
    public const FRAMEWORK_COLUMN = 1100;

    /**
     * @var int The length the short name of outcomes is brought to. It stays lower: that column is part of a unique
     * index, and MySQL and MariaDB cap an ordinary index at 3072 bytes, about 765 characters in utf8mb4.
     */
    public const OUTCOME_TARGET = 750;

    /** @var string Nothing to do. */
    public const OK = 'ok';

    /** @var string Something would be done (check mode). */
    public const TODO = 'todo';

    /** @var string Something was done. */
    public const FIXED = 'fixed';

    /** @var string Something went wrong. */
    public const ERROR = 'error';

    /** @var string Something the administrator should know, that will not be changed by itself. */
    public const NOTICE = 'notice';

    /**
     * The columns: table, whether the column is mandatory in the schema of the core, unique index to drop and restore,
     * length to reach.
     *
     * @return array[]
     */
    protected static function targets(): array {
        return [
            ['competency', false, null, self::TARGET],
            ['competency_framework', false, null, self::FRAMEWORK_COLUMN],
            ['grade_outcomes', true, ['courseid-shortname', ['courseid', 'shortname']], self::OUTCOME_TARGET],
            ['grade_outcomes_history', true, null, self::OUTCOME_TARGET],
        ];
    }

    /**
     * Checks every column and, unless in check mode, widens the ones that need it.
     *
     * @param bool $checkonly True to report without changing anything.
     * @return array[] One row per table: ['table' => string, 'status' => string, 'message' => string].
     */
    public static function run(bool $checkonly = false): array {
        $report = [];
        foreach (self::targets() as [$tablename, $notnull, $indexdef, $target]) {
            try {
                [$status, $message] = self::process($tablename, $notnull, $indexdef, $target, $checkonly);
            } catch (\Throwable $e) {
                $status = self::ERROR;
                $message = $e->getMessage();
                if ($e instanceof \moodle_exception && !empty($e->debuginfo)) {
                    $message .= ' / ' . $e->debuginfo;
                }
            }
            $report[] = ['table' => $tablename, 'status' => $status, 'message' => $message];
        }
        return $report;
    }

    /**
     * A message of the reports, in the language of the site.
     *
     * @param string $key Name of the string, without its prefix.
     * @param mixed $a Values put in the string.
     * @return string
     */
    public static function msg(string $key, $a = null): string {
        return get_string('msg_' . $key, 'local_plugin999', $a);
    }

    /**
     * One row of a report as a line of text: the status, then the message.
     *
     * @param array $row A row of a report.
     * @return string
     */
    public static function line(array $row): string {
        return get_string('statusline', 'local_plugin999', [
            'status' => get_string('status_' . $row['status'], 'local_plugin999'),
            'message' => $row['message'],
        ]);
    }

    /**
     * Whether a report holds at least one error.
     *
     * @param array[] $report What run() returned.
     * @return bool
     */
    public static function has_errors(array $report): bool {
        foreach ($report as $row) {
            if ($row['status'] === self::ERROR) {
                return true;
            }
        }
        return false;
    }

    /**
     * Checks one column and widens it when needed.
     *
     * @param string $tablename Table, without prefix.
     * @param bool $notnull Whether the column is mandatory in the schema of the core.
     * @param array|null $indexdef Name and columns of the unique index holding the column, if any.
     * @param int $target The length to reach.
     * @param bool $checkonly True to report without changing anything.
     * @return string[] Status and message.
     */
    protected static function process(string $tablename, bool $notnull, ?array $indexdef, int $target, bool $checkonly): array {
        global $DB;

        $dbman = $DB->get_manager();
        $table = new xmldb_table($tablename);
        $index = $indexdef === null ? null : new xmldb_index($indexdef[0], XMLDB_INDEX_UNIQUE, $indexdef[1]);

        if (!$dbman->table_exists($table)) {
            return [self::ERROR, self::msg('tablenotfound', $tablename)];
        }
        // Read from the database itself, never from the cache: the column may have been changed outside Moodle.
        $columns = $DB->get_columns($tablename, false);
        if (!isset($columns['shortname'])) {
            return [self::ERROR, self::msg('columnnotfound', $tablename)];
        }
        $length = (int) $columns['shortname']->max_length;

        if ($length >= $target) {
            // Nothing to widen. Only restore the unique index if an earlier run was interrupted.
            if ($index !== null && !$dbman->index_exists($table, $index)) {
                if ($checkonly) {
                    return [self::TODO, self::msg('indexmissing', $tablename)];
                }
                $dbman->add_index($table, $index);
                return [self::FIXED, self::msg('indexrestored', $tablename)];
            }
            return [self::OK, self::msg('columnok', ['table' => $tablename, 'length' => $length])];
        }

        // A wide index only fits the Dynamic and Compressed row formats (MySQL, MariaDB).
        if ($index !== null && $DB->get_dbfamily() === 'mysql') {
            $format = strtolower((string) $DB->get_row_format($tablename));
            if (!in_array($format, ['dynamic', 'compressed'], true)) {
                return [
                    self::ERROR,
                    self::msg('rowformat', ['table' => $tablename, 'format' => $format, 'target' => $target]),
                ];
            }
        }

        if ($checkonly) {
            return [self::TODO, self::msg('columntodo', ['table' => $tablename, 'length' => $length, 'target' => $target])];
        }

        // The DDL layer works out the change from the cached description of the columns. If the database was changed
        // outside Moodle (plain SQL, a restore), that cache is wrong and nothing would be altered: purge it first.
        $DB->reset_caches([$tablename]);

        $field = new xmldb_field('shortname', XMLDB_TYPE_CHAR, (string) $target, null, $notnull ? XMLDB_NOTNULL : null);

        // A column held by an index cannot be altered: drop the index, then put it back.
        if ($index !== null && $dbman->index_exists($table, $index)) {
            $dbman->drop_index($table, $index);
        }
        try {
            $dbman->change_field_precision($table, $field);
        } finally {
            // Whether the change went through or not, the table is not left without its unique index.
            if ($index !== null && !$dbman->index_exists($table, $index)) {
                $dbman->add_index($table, $index);
            }
        }

        // Read the database again before claiming anything.
        $after = (int) $DB->get_columns($tablename, false)['shortname']->max_length;
        if ($after < $target) {
            return [self::ERROR, self::msg('columnstill', ['table' => $tablename, 'length' => $after])];
        }
        return [self::FIXED, self::msg('columnfixed', ['table' => $tablename, 'from' => $length, 'to' => $after])];
    }
}
