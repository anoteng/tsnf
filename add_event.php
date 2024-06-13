<?php
session_start();
require 'config.php';

// Sjekk om brukeren er logget inn som admin
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] != 'admin') {
    echo "Du har ikke tilgang til denne siden.";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $dato = $_POST['dato'];
    $sted = $_POST['sted'];
    $arrtype = $_POST['arrtype'];
    $tid_fra = $_POST['tid_fra'];
    $tid_til = $_POST['tid_til'];
    $ssk1 = !empty($_POST['ssk1']) ? $_POST['ssk1'] : NULL;
    $ssk2 = !empty($_POST['ssk2']) ? $_POST['ssk2'] : NULL;
    $ssk3 = !empty($_POST['ssk3']) ? $_POST['ssk3'] : NULL;
    $ridder1 = !empty($_POST['ridder1']) ? $_POST['ridder1'] : NULL;
    $ridder2 = !empty($_POST['ridder2']) ? $_POST['ridder2'] : NULL;
    $ridder3 = !empty($_POST['ridder3']) ? $_POST['ridder3'] : NULL;
    $max_ssk = $_POST['max_ssk']; // Legg til max_ssk

    $stmt = $conn->prepare("INSERT INTO arrangement (dato, sted, arrtype, tid_fra, tid_til, ssk1, ssk2, ssk3, ridder1, ridder2, ridder3, max_ssk) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("siissiiiiiii", $dato, $sted, $arrtype, $tid_fra, $tid_til, $ssk1, $ssk2, $ssk3, $ridder1, $ridder2, $ridder3, $max_ssk);

    if ($stmt->execute()) {
        echo "Nytt arrangement ble opprettet!";
    } else {
        echo "Feil ved oppretting av arrangement: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="no">
<head>
    <meta charset="UTF-8">
    <title>Legg til arrangement</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#kommune').change(function() {
                var kommune_id = $(this).val();
                $.ajax({
                    url: 'get_steder.php',
                    method: 'GET',
                    data: { kommune_id: kommune_id },
                    dataType: 'json',
                    success: function(response) {
                        $('#sted').empty();
                        $('#sted').append('<option value="">Velg sted</option>');
                        $.each(response, function(index, value) {
                            $('#sted').append('<option value="' + value.id + '">' + value.navn + '</option>');
                        });
                    }
                });
            });
        });
    </script>
</head>
<body>
<div class="container">
    <h1>Legg til arrangement</h1>
    <form action="add_event.php" method="post">
        <div class="form-group">
            <label for="dato">Dato:</label>
            <input type="date" class="form-control" id="dato" name="dato" required>
        </div>
        <div class="form-group">
            <label for="kommune">Kommune:</label>
            <select class="form-control" id="kommune" name="kommune" required>
                <option value="">Velg kommune</option>
                <?php
                $result = $conn->query("SELECT id, navn FROM kommune");
                while ($row = $result->fetch_assoc()) {
                    echo "<option value='" . $row['id'] . "'>" . $row['navn'] . "</option>";
                }
                ?>
            </select>
        </div>
        <div class="form-group">
            <label for="sted">Sted:</label>
            <select class="form-control" id="sted" name="sted" required>
                <option value="">Velg sted</option>
            </select>
        </div>
        <div class="form-group">
            <label for="arrtype">Arrangementstype:</label>
            <select class="form-control" id="arrtype" name="arrtype" required>
                <option value="">Velg type</option>
                <?php
                $result = $conn->query("SELECT id, type FROM arrtype");
                while ($row = $result->fetch_assoc()) {
                    echo "<option value='" . $row['id'] . "'>" . $row['type'] . "</option>";
                }
                ?>
            </select>
        </div>
        <div class="form-group">
            <label for="tid_fra">Tid fra:</label>
            <input type="time" class="form-control" id="tid_fra" name="tid_fra" required>
        </div>
        <div class="form-group">
            <label for="tid_til">Tid til:</label>
            <input type="time" class="form-control" id="tid_til" name="tid_til" required>
        </div>
        <div class="form-group">
            <label for="max_ssk">Maksimalt antall SSK:</label>
            <input type="number" class="form-control" id="max_ssk" name="max_ssk" min="0" max="3" value="2" required>
        </div>
        <button type="submit" class="btn btn-primary">Opprett arrangement</button>
    </form>
</div>
</body>
</html>
