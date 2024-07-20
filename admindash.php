<!DOCTYPE html>
<html lang="no">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrasjon</title>
    <link rel="stylesheet" href="adminstyle.css">
    <script src="admindash.js"></script>
</head>
<body>
<div class="sidebar">
    <h2>Admin Dashboard</h2>
    <a href="#user-admin">Brukere</a>
    <a href="#commune-admin">Legg til/fjern kommune</a>
    <a href="#location-admin">Sted-administrasjon</a>
    <a href="#event-admin">Arrangementsadministrasjon</a>
    <a href="#arrtype-admin">Arrangementstypeadministrasjon</a>
    <a href="#economy">Økonomirapporter</a>
    <a href="dashboard.php">Tilbake til dashbord</a>
</div>
<div class="content">
    <h1>Welcome to the Admin Dashboard</h1>
    <section id="user-admin">
        <h2>Brukeradministrasjon</h2>
        <button onclick="openModal('user')">Add User</button>
        <table>
            <tr>
                <th>Name</th>
                <th>Role</th>
                <th>Actions</th>
            </tr>
            <!-- User rows go here -->
        </table>
    </section>
    <section id="commune-admin">
        <h2>Legg til/fjern kommune</h2>
        <button onclick="openModal('commune')">Add Commune</button>
        <table>
            <tr>
                <th>Name</th>
                <th>Actions</th>
            </tr>
            <!-- Commune rows go here -->
        </table>
    </section>
    <section id="location-admin">
        <h2>Stedadministrasjon</h2>
        <button onclick="openModal('location')">Add Location</button>
        <table>
            <tr>
                <th>Name</th>
                <th>Address</th>
                <th>Actions</th>
            </tr>
            <!-- Location rows go here -->
        </table>
    </section>
    <section id="event-admin">
        <h2>Arrangementsadministrasjon</h2>
        <button onclick="openModal('event')">Add Event</button>
        <table>
            <tr>
                <th>Name</th>
                <th>Date</th>
                <th>Location</th>
                <th>Actions</th>
            </tr>
            <!-- Event rows go here -->
        </table>
    </section>
    <section id="arrtype-admin">
        <h2>Arrangementstypeadministrasjon</h2>
        <button onclick="openModal('event')">Add Event</button>
        <table>
            <tr>
                <th>Name</th>
                <th>Date</th>
                <th>Location</th>
                <th>Actions</th>
            </tr>
            <!-- Event rows go here -->
        </table>
    </section>
    <section id="economy">
        <h2>Økonomi</h2>
        <button onclick="openModal('event')">Add Event</button>
        <table>
            <tr>
                <th>Name</th>
                <th>Date</th>
                <th>Location</th>
                <th>Actions</th>
            </tr>
            <!-- Event rows go here -->
        </table>
    </section>
</div>

<!-- Modals for Adding/Editing Entities -->
<!-- Additional HTML for Modals would go here -->

<script>
    function openModal(type) {
        console.log('Opening modal for type:', type);
        // Logic to handle different types of modals
    }
</script>
</body>
</html>
