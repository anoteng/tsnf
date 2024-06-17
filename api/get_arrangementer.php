<?php
require '../config.php';

$current_year = date("Y");

$result = $conn->query("SELECT arrangement.*, 
    ssk1.navn AS ssk1_navn, ssk2.navn AS ssk2_navn, ssk3.navn AS ssk3_navn, 
    ridder1.navn AS ridder1_navn, ridder2.navn AS ridder2_navn, ridder3.navn AS ridder3_navn,
    steder.navn AS sted_navn, steder.adresse AS sted_adresse,
    arrtype.type AS arrangementstype
    FROM arrangement
    LEFT JOIN ssk AS ssk1 ON arrangement.ssk1 = ssk1.id
    LEFT JOIN ssk AS ssk2 ON arrangement.ssk2 = ssk2.id
    LEFT JOIN ssk AS ssk3 ON arrangement.ssk3 = ssk3.id
    LEFT JOIN ridderhatt AS ridder1 ON arrangement.ridder1 = ridder1.id
    LEFT JOIN ridderhatt AS ridder2 ON arrangement.ridder2 = ridder2.id
    LEFT JOIN ridderhatt AS ridder3 ON arrangement.ridder3 = ridder3.id
    LEFT JOIN steder ON arrangement.sted = steder.id
    LEFT JOIN arrtype ON arrangement.arrtype = arrtype.id
    WHERE YEAR(arrangement.dato) = $current_year
    ORDER BY arrangement.dato ASC");

$arrangementer = [];

while ($row = $result->fetch_assoc()) {
    $row['ukedag'] = strftime('%A', strtotime($row['dato']));
    $arrangementer[] = $row;
}

header('Content-Type: application/json');
echo json_encode(['success' => true, 'arrangementer' => $arrangementer]);
?>
