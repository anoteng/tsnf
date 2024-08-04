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
require '../auth.php';

if (!isset($_SESSION['email'])) {
    http_response_code(401);
    echo json_encode(["message" => "Unauthorized"]);
    exit();
}
header('Content-Type: application/json');

$query = "SELECT 
            a.id,
            a.dato,
            a.tid_fra,
            a.tid_til,
            a.sted,
            a.arrtype,
            a.ssk1,
            a.ssk2,
            a.ssk3,
            a.ridder1,
            a.ridder2,
            a.ridder3,
            s.navn AS sted_navn,
            s.adresse,
            k.navn AS kommune_navn,
            k.id AS kommune,
            at.type AS arrangementstype_navn,
            ssk1.navn AS ssk1_navn,
            ssk2.navn AS ssk2_navn,
            ssk3.navn AS ssk3_navn,
            r1.navn AS ridder1_navn,
            r2.navn AS ridder2_navn,
            r3.navn AS ridder3_navn,
            a.kommentar AS kommentar,
            a.max_ssk AS max_ssk,
            a.annonsert_fb AS annonsert_fb,
            a.annonsert_kalender AS annonsert_kalender
          FROM arrangement a
          JOIN steder s ON a.sted = s.id
          JOIN kommune k ON s.kommune = k.id
          JOIN arrtype at ON a.arrtype = at.id
          LEFT JOIN ssk ssk1 ON a.ssk1 = ssk1.id
          LEFT JOIN ssk ssk2 ON a.ssk2 = ssk2.id
          LEFT JOIN ssk ssk3 ON a.ssk3 = ssk3.id
          LEFT JOIN ridderhatt r1 ON a.ridder1 = r1.id
          LEFT JOIN ridderhatt r2 ON a.ridder2 = r2.id
          LEFT JOIN ridderhatt r3 ON a.ridder3 = r3.id";

$result = $conn->query($query);

$data = array();
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);
?>
