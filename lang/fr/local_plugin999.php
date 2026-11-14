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
 * French strings.
 *
 * @package    local_plugin999
 * @copyright  2026 Jean-Louis Martinez <jeanlouis@martinez.pro>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['clihelp'] = 'Porte à 999 caractères le nom court des compétences et des référentiels de compétences, et à 750 celui des objectifs.

Options :
-c, --check   Affiche sans rien modifier
--restore-forms  Rétablit les trois fichiers de Moodle et désactive le réglage
-h, --help    Affiche cette aide

Exemple :
\$ php public/local/plugin999/cli/widen.php --check';
$string['errorupgradepending'] = 'Une mise à jour de Moodle est en attente. Terminez-la, puis relancez. Rien n\'a été modifié.';
$string['errorwiden'] = 'Au moins une colonne de nom court n\'a pas pu être élargie. Voir les lignes ci-dessus.';
$string['formsoff'] = 'Formulaires et import CSV : laissés tels quels (le réglage est désactivé).';
$string['msg_columnfixed'] = '{$a->table} : le nom court tenait {$a->from} caractères, élargi à {$a->to}';
$string['msg_columnnotfound'] = 'colonne du nom court introuvable dans la table {$a}';
$string['msg_columnok'] = '{$a->table} : le nom court tient {$a->length} caractères';
$string['msg_columnstill'] = '{$a->table} : le nom court tient toujours {$a->length} caractères après la modification';
$string['msg_columntodo'] = '{$a->table} : le nom court tient {$a->length} caractères, à élargir à {$a->target}';
$string['msg_formexception'] = '{$a->file} : {$a->error}';
$string['msg_formfixed'] = '{$a->file} : nom court limité à {$a->from} caractères auparavant, {$a->to} maintenant';
$string['msg_formnotfound'] = '{$a} : fichier introuvable ou illisible';
$string['msg_formnotwritable'] = '{$a} : fichier ou dossier non modifiable';
$string['msg_formok'] = '{$a->file} : nom court limité à {$a->limit} caractères';
$string['msg_formpublished'] = '{$a->file} : nom court limité à {$a->limit} caractères, tel que publié par Moodle (le réglage est désactivé)';
$string['msg_formreplacefail'] = '{$a} : le fichier n\'a pas pu être remplacé';
$string['msg_formsnocolumn'] = 'formulaires et import CSV laissés tels quels : dans la table {$a->table}, le nom court ne tient pas {$a->target} caractères';
$string['msg_formstilledited'] = '{$a} : encore modifié alors que le réglage est désactivé ; enregistrez de nouveau le réglage, ou lancez cli/widen.php --restore-forms pour le rétablir';
$string['msg_formswait'] = 'formulaires et import CSV : à modifier une fois les colonnes élargies';
$string['msg_formtempfail'] = '{$a} : le fichier temporaire n\'a pas pu être écrit';
$string['msg_formtodo'] = '{$a->file} : nom court limité à {$a->from} caractères, à porter à {$a->to}';
$string['msg_formunexpected'] = '{$a} : les lignes à modifier ne sont pas celles attendues, fichier laissé tel quel';
$string['msg_formunreadable'] = '{$a} : le fichier n\'a pas pu être lu';
$string['msg_indexmissing'] = '{$a} : l\'index unique (courseid, shortname) est absent';
$string['msg_indexrestored'] = '{$a} : index unique (courseid, shortname) rétabli';
$string['msg_rowformat'] = '{$a->table} est au format de ligne « {$a->format} » : l\'index unique ne tiendrait pas à {$a->target} caractères, table laissée telle quelle';
$string['msg_tablenotfound'] = 'table {$a} introuvable';
$string['patchforms'] = 'Lever la limite des formulaires et de l\'import CSV';
$string['patchforms_desc'] = 'Le code de Moodle limite lui-même le nom court à 100 caractères : les formulaires de compétence et de référentiel de compétences refusent un nom plus long, et l\'import CSV des référentiels le coupe. Quand ce réglage est activé, le plugin modifie trois fichiers du cœur (admin/tool/lp/classes/form/competency.php, competency_framework.php, et admin/tool/lpimportcsv/classes/framework_importer.php) pour qu\'ils acceptent 999 caractères, et les modifie de nouveau après chaque mise à jour de Moodle. Le numéro d\'identification garde sa limite de 100 caractères. La modification est faite dès l\'enregistrement de ce réglage. Le désactiver rétablit aussitôt les trois fichiers ; désinstaller le plugin le fait aussi.';
$string['pluginname'] = '999';
$string['privacy:metadata'] = 'Le plugin ne conserve aucune donnée personnelle.';
$string['status'] = 'État actuel';
$string['status_desc'] = 'OK : en place. À FAIRE : sera fait au prochain passage de la tâche planifiée (toutes les heures). ERREUR : à examiner, le message dit pourquoi. À SAVOIR : rien ne changera tout seul.';
$string['status_error'] = 'ERREUR';
$string['status_fixed'] = 'CORRIGÉ';
$string['status_notice'] = 'À SAVOIR';
$string['status_ok'] = 'OK';
$string['status_todo'] = 'À FAIRE';
$string['statusline'] = '{$a->status} : {$a->message}';
$string['taskwiden'] = 'Maintenir élargi le nom court des compétences, des référentiels (999 caractères) et des objectifs (750)';
