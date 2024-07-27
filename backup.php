#!/usr/bin/env php
<?php
/*
 * This file is part of TSNF Vaktliste.
 *
 * TSNF Vaktliste is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * TSNF Vaktliste is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with TSNF Vaktliste. If not, see <https://www.gnu.org/licenses/>.
 *
 */

require 'config.php';

// Path where the backup file will be saved
$backupDir = '/var/www/backup/';
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0777, true);
}
$backupFile = $backupDir . 'backup_' . date('Y-m-d_H-i-s') . '.sql';

// Command to create the backup
$command = "mysqldump --no-tablespaces --user={$username} --password={$password} --host={$servername} {$dbname} > {$backupFile}";

// Execute the command
$output = null;
$returnVar = null;
exec($command, $output, $returnVar);

// Check if the backup was successful
if ($returnVar === 0) {
    echo "Backup was created successfully: {$backupFile}";
} else {
    echo "An error occurred while creating the backup.";
}
?>
