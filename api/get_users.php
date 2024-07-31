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

require '../config.php';

$search = $_GET['q'];

$sql = "SELECT id, navn as text FROM ssk WHERE navn LIKE ? UNION SELECT id, navn as text FROM ridderhatt WHERE navn LIKE ?";
$stmt = $conn->prepare($sql);
$searchParam = '%' . $search . '%';
$stmt->bind_param('ss', $searchParam, $searchParam);
$stmt->execute();
$result = $stmt->get_result();
$users = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode($users);
?>
