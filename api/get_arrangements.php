<?php
require '../config.php';

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
            at.type AS arrangementstype_navn,
            ssk1.navn AS ssk1_navn,
            ssk2.navn AS ssk2_navn,
            ssk3.navn AS ssk3_navn,
            r1.navn AS ridder1_navn,
            r2.navn AS ridder2_navn,
            r3.navn AS ridder3_navn,
            a.kommentar AS kommentar,
            a.max_ssk AS max_ssk
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
