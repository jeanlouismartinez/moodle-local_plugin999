# 999 — noms cours élargis (local_plugin999)

Plugin local pour Moodle 5.3. Il lève la limite de 100 caractères qui s'applique au nom court des compétences, des référentiels de compétences et des objectifs.

Les intitulés officiels de nombreuses certifications (blocs de compétences du RNCP, diplômes d'État…) dépassent 100 caractères. Sans ce plugin, il faut les abréger ou les reporter dans la description.

- Version : 1.3.0 (bêta)
- Moodle requis : 5.3
- Bases de données : MySQL / MariaDB et PostgreSQL
- Licence : GNU GPL v3 ou ultérieure
- Auteur : Jean-Louis Martinez

## Ce que fait le plugin

### 1. Il élargit les colonnes en base

| Table de Moodle | Élément | Longueur du nom court |
|---|---|---|
| `competency` | Compétences | 999 caractères |
| `competency_framework` | Référentiels de compétences | 999 caractères saisissables (colonne de 1100) |
| `grade_outcomes` | Objectifs | 750 caractères |
| `grade_outcomes_history` | Historique des objectifs | 750 caractères |

Deux choix à connaître :

- La colonne des référentiels fait 1100 caractères pour qu'un référentiel au nom de 999 caractères puisse encore être dupliqué : Moodle ajoute « (copie) » au nom de la copie.
- Les objectifs s'arrêtent à 750 : leur nom court fait partie d'un index unique, que MySQL et MariaDB limitent à environ 765 caractères en utf8mb4.

L'élargissement est fait à l'installation, puis contrôlé toutes les heures par une tâche planifiée. Si une mise à jour de Moodle ramène une colonne à 100, elle est élargie de nouveau au passage suivant.

### 2. Sur option, il lève la limite des formulaires et de l'import CSV

Élargir la base ne suffit pas : le code de Moodle refuse lui-même un nom de plus de 100 caractères dans les formulaires, et l'import CSV des référentiels le coupe.

Le réglage « Lever la limite des formulaires et de l'import CSV » (désactivé par défaut) modifie trois fichiers du cœur de Moodle pour qu'ils acceptent 999 caractères :

- `admin/tool/lp/classes/form/competency.php`
- `admin/tool/lp/classes/form/competency_framework.php`
- `admin/tool/lpimportcsv/classes/framework_importer.php`

À savoir avant de l'activer :

- C'est une modification du cœur de Moodle, limitée à la valeur de la longueur dans ces trois fichiers. Elle est refaite automatiquement après chaque mise à jour de Moodle.
- Si un fichier ne ressemble pas à ce que le plugin attend, il est laissé tel quel et la page de réglages le signale.
- Désactiver le réglage rétablit aussitôt les trois fichiers. Désinstaller le plugin le fait aussi.
- Le numéro d'identification garde sa limite de 100 caractères.

## Installation

1. Copier le contenu de ce dépôt dans le dossier `public/local/plugin999` de Moodle.
2. Se connecter en administrateur et lancer la mise à jour proposée, ou exécuter `php admin/cli/upgrade.php`.
3. Ouvrir *Administration du site > Plugins > Plugins locaux > 999* : la page affiche l'état de chaque colonne et de chaque fichier.
4. Activer, si besoin, le réglage des formulaires et de l'import CSV.

## Lire la page de réglages

| Mention | Sens |
|---|---|
| OK | En place. |
| À FAIRE | Sera fait au prochain passage de la tâche planifiée (toutes les heures). |
| CORRIGÉ | Vient d'être remis en place. |
| ERREUR | À examiner : le message dit pourquoi. |
| À SAVOIR | Rien ne changera tout seul. |

## Ligne de commande

Depuis la racine de Moodle :

```
php public/local/plugin999/cli/widen.php                   # élargit et applique le réglage tout de suite
php public/local/plugin999/cli/widen.php --check           # contrôle sans rien modifier
php public/local/plugin999/cli/widen.php --restore-forms   # désactive le réglage et rétablit les trois fichiers
```

Le script refuse de s'exécuter si une mise à jour de Moodle est en attente.

## Désinstallation

La désinstallation rétablit les trois fichiers du cœur dans leur état d'origine. Les colonnes restent élargies : les rétrécir couperait les noms saisis entre-temps.

## Remplacement de local_plugin500

Ce plugin remplace `local_plugin500`. Pour passer de l'un à l'autre :

1. Désinstaller `local_plugin500` (les colonnes restent élargies).
2. Supprimer le dossier `public/local/plugin500`.
3. Installer `local_plugin999` comme indiqué plus haut.
4. Réactiver le réglage des formulaires s'il était utilisé.

## Limites connues

- Plusieurs écrans de Moodle affichent le nom court en entier : un nom de 999 caractères y prend beaucoup de place.
- Une copie de référentiel dont le nom dépasse 999 caractères (nom d'origine de plus de 991 caractères) doit être raccourcie avant de pouvoir être enregistrée depuis le formulaire.
- Un export vers un Moodle sans ce plugin échouera ou coupera les noms de plus de 100 caractères.
- Faire une sauvegarde de la base avant l'installation, comme pour toute modification de structure.

## Données personnelles

Le plugin ne conserve aucune donnée personnelle.
