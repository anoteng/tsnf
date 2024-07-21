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



// Include database and functions files
require_once 'config.php';

// Query to fetch necessary columns
$query = "SELECT 
            a.id,
            a.dato,
            DATE_FORMAT(a.tid_fra, '%H:%i') AS tid_fra,
            DATE_FORMAT(a.tid_til, '%H:%i') AS tid_til,
            a.sted,
            a.arrtype,
            a.ssk1,
            a.ssk2,
            a.ssk3,
            s.navn AS sted_navn,
            s.adresse,
            at.type AS arrangementstype_navn,
            ssk1.fornavn AS ssk1_navn,
            ssk2.fornavn AS ssk2_navn,
            ssk3.fornavn AS ssk3_navn,
            a.kommentar AS kommentar
          FROM arrangement a
          JOIN steder s ON a.sted = s.id
          JOIN arrtype at ON a.arrtype = at.id
          LEFT JOIN ssk ssk1 ON a.ssk1 = ssk1.id
          LEFT JOIN ssk ssk2 ON a.ssk2 = ssk2.id
          LEFT JOIN ssk ssk3 ON a.ssk3 = ssk3.id ORDER BY a.dato ASC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="no">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Overview</title>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/gh/mobius1/vanilla-Datatables@latest/vanilla-dataTables.min.css">
    <link rel="stylesheet" href="style.css">
    <script type="text/javascript" src="https://cdn.jsdelivr.net/gh/mobius1/vanilla-Datatables@latest/vanilla-dataTables.min.js"></script>
</head>
<body>
<div class="container">
    <h1>Vaktliste</h1>
    <p><a href="login.php">Logg inn for å gjøre endringer</a></p>
    <table id="eventsTable">
        <thead>
        <tr>
            <th>Dato</th>
            <th>Ukedag</th>
            <th>Sted</th>
            <th>Type</th>
            <th>Start</th>
            <th>Slutt</th>
            <th>SSK1</th>
            <th>SSK2</th>
            <th>SSK3</th>
            <th>Kommentar</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $ukedag = strftime('%A', strtotime($row['dato']));
                echo "<tr>
                                <td>{$row['dato']}</td>
                                <td>{$ukedag}</td>
                                <td>{$row['sted_navn']}</td>
                                <td>{$row['arrangementstype_navn']}</td>
                                <td>{$row['tid_fra']}</td>
                                <td>{$row['tid_til']}</td>
                                <td>{$row['ssk1_navn']}</td>
                                <td>{$row['ssk2_navn']}</td>
                                <td>{$row['ssk3_navn']}</td>
                                <td>{$row['kommentar']}</td>
                              </tr>";
            }
        } else {
            echo "<tr><td colspan='7'>No events found</td></tr>";
        }
        ?>
        </tbody>
    </table>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var dataTable = new DataTable("#eventsTable", {
            searchable: true,
            sortable: true,
            perPageSelect: [5, 10, 15, 20],
            labels: {
                placeholder: "Søk...", // Placeholder for the search box
                perPage: "{select} eventer per side", // per-page text
                noRows: "Ingen eventer funnet", // Message shown when no entries are found
                info: "Viser {start} til {end} av {rows} eventer", // Information about the current page
            }
        });
    });
</script>
<footer>
    <p>&copy; <?php echo date("Y"); ?> Andreas Noteng. <a href="changelog.php">Changelog</a>. <a href="https://github.com/anoteng/tsnf/issues">report bugs</a></p>
</footer>
</body>
</html>

<?php
// Close the connection
$conn->close();
?>
