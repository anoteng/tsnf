<?php
require '../config.php';

$response = array('success' => false, 'arrangementer' => array());

$current_year = date("Y");
$query = "SELECT a.*, 
          s1.navn AS ssk1_navn, s2.navn AS ssk2_navn, s3.navn AS ssk3_navn, 
          r1.navn AS ridder1_navn, r2.navn AS ridder2_navn, r3.navn AS ridder3_navn, 
          st.navn AS sted_navn, st.adresse AS sted_adresse, 
          at.type AS arrangementstype 
          FROM arrangement a 
          LEFT JOIN ssk s1 ON a.ssk1 = s1.id 
          LEFT JOIN ssk s2 ON a.ssk2 = s2.id 
          LEFT JOIN ssk s3 ON a.ssk3 = s3.id 
          LEFT JOIN ridderhatt r1 ON a.ridder1 = r1.id 
          LEFT JOIN ridderhatt r2 ON a.ridder2 = r2.id 
          LEFT JOIN ridderhatt r3 ON a.ridder3 = r3.id 
          LEFT JOIN steder st ON a.sted = st.id 
          LEFT JOIN arrtype at ON a.arrtype = at.id 
          WHERE YEAR(a.dato) = $current_year 
          ORDER BY a.dato ASC";

setlocale(LC_TIME, 'nb_NO.UTF-8');

if ($result = $conn->query($query)) {
    while ($row = $result->fetch_assoc()) {
        $row['ukedag'] = strftime('%A', strtotime($row['dato']));
        $response['arrangementer'][] = $row;
    }
    $response['success'] = true;
} else {
    $response['message'] = $conn->error;
}

header('Content-Type: application/json');
echo json_encode($response);
?>
