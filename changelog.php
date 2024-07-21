<!DOCTYPE html>
<html lang="no">
<head>
    <meta charset="UTF-8">
    <title>Changelog</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<div class="container">
    <h2>Changelog</h2>
    <pre>
<?php
echo file_get_contents('changelog.txt');
?>
    </pre>
    <a href="dashboard.php" class="btn btn-primary">Tilbake til Dashboard</a>
</div>
</body>
</html>
