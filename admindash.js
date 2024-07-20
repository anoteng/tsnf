// Funksjon for å åpne en modal
function openModal(modalId) {
    var modal = document.getElementById(modalId);
    modal.style.display = "block";
}

// Funksjon for å lukke en modal
function closeModal(modalId) {
    var modal = document.getElementById(modalId);
    modal.style.display = "none";
}

// Lukk modalen når brukeren klikker utenfor den
window.onclick = function(event) {
    var modals = document.getElementsByClassName("modal");
    for (var i = 0; i < modals.length; i++) {
        if (event.target == modals[i]) {
            modals[i].style.display = "none";
        }
    }
}

// Last inn kommuner for stedsadministrasjon
function loadMunicipalitiesForLocation() {
    fetch('api/get_municipalities.php')
        .then(response => response.json())
        .then(municipalities => {
            console.log('Municipalities for Location:', municipalities); // Log municipalities data
            const municipalitySelect = document.getElementById('kommuneLocation');
            if (municipalitySelect) {
                console.log('Municipality select found for Location:', municipalitySelect); // Confirm municipality select element
                municipalitySelect.innerHTML = ''; // Tøm nedtrekksmenyen
                municipalities.forEach(municipality => {
                    const option = document.createElement('option');
                    option.value = municipality.id;
                    option.textContent = municipality.navn;
                    console.log('Adding option for Location:', option); // Log each option
                    municipalitySelect.appendChild(option);
                });
                console.log('Municipality select updated for Location.'); // Confirm update
            } else {
                console.error('Municipality select element not found for Location.'); // Log error if element not found
            }
        })
        .catch(error => console.error('Error loading municipalities for Location:', error)); // Log errors
}

// Last inn kommuner for arrangementadministrasjon
function loadMunicipalitiesForEvent() {
    fetch('api/get_municipalities.php')
        .then(response => response.json())
        .then(municipalities => {
            console.log('Municipalities for Event:', municipalities); // Log municipalities data
            const municipalitySelect = document.getElementById('kommuneEvent');
            if (municipalitySelect) {
                console.log('Municipality select found for Event:', municipalitySelect); // Confirm municipality select element
                municipalitySelect.innerHTML = ''; // Tøm nedtrekksmenyen
                municipalities.forEach(municipality => {
                    const option = document.createElement('option');
                    option.value = municipality.id;
                    option.textContent = municipality.navn;
                    console.log('Adding option for Event:', option); // Log each option
                    municipalitySelect.appendChild(option);
                });
                console.log('Municipality select updated for Event.'); // Confirm update
            } else {
                console.error('Municipality select element not found for Event.'); // Log error if element not found
            }
        })
        .catch(error => console.error('Error loading municipalities for Event:', error)); // Log errors
}

// Last inn steder basert på valgt kommune for arrangementadministrasjon
function loadLocations() {
    const kommuneId = document.getElementById('kommuneEvent').value;
    fetch(`api/get_locations.php?kommune_id=${kommuneId}`)
        .then(response => response.json())
        .then(locations => {
            console.log('Locations:', locations); // Log locations data
            const locationSelect = document.getElementById('sted');
            locationSelect.innerHTML = ''; // Tøm nedtrekksmenyen
            locations.forEach(location => {
                const option = document.createElement('option');
                option.value = location.id;
                option.textContent = location.navn;
                console.log('Adding option:', option); // Log each option
                locationSelect.appendChild(option);
            });
        })
        .catch(error => console.error('Error loading locations:', error)); // Log errors
}

// Last inn arrangementstyper
function loadEventTypes() {
    fetch('api/get_event_types.php')
        .then(response => response.json())
        .then(eventTypes => {
            console.log('Event Types:', eventTypes); // Log event types data
            const eventTypeSelect = document.getElementById('arrtype');
            eventTypeSelect.innerHTML = ''; // Tøm nedtrekksmenyen
            eventTypes.forEach(type => {
                const option = document.createElement('option');
                option.value = type.id;
                option.textContent = type.type;
                console.log('Adding option:', option); // Log each option
                eventTypeSelect.appendChild(option);
            });
        })
        .catch(error => console.error('Error loading event types:', error)); // Log errors
}

// Last inn data ved last inn siden
function loadSelectOptions() {
    loadMunicipalitiesForLocation();
    loadMunicipalitiesForEvent();
    loadEventTypes();
}

// Funksjon for å tømme skjemaet
function resetForm(formId) {
    console.log('Resetting form:', formId); // Log form reset
    document.getElementById(formId).reset();
}

// Send legg til bruker skjema
document.getElementById('addUserForm').addEventListener('submit', function(event) {
    event.preventDefault();
    const formData = new FormData(this);
    fetch('api/add_user.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.text())
        .then(responseText => {
            alert(responseText);
            console.log('Response from add_user.php:', responseText); // Log response text
            if (responseText.includes("Bruker lagt til")) {
                resetForm('addUserForm');
            }
            closeModal('userAdminModal');
        });
});

// Send legg til sted skjema
document.getElementById('addLocationForm').addEventListener('submit', function(event) {
    event.preventDefault();
    const formData = new FormData(this);
    fetch('api/add_location.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.text())
        .then(responseText => {
            alert(responseText);
            console.log('Response from add_location.php:', responseText); // Log response text
            if (responseText.includes("Lokasjon lagt til")) {
                resetForm('addLocationForm');
            }
            closeModal('locationAdminModal');
        });
});

// Send legg til arrangement skjema
document.getElementById('addEventForm').addEventListener('submit', function(event) {
    event.preventDefault();
    const formData = new FormData(this);
    fetch('api/add_event.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.text())
        .then(responseText => {
            alert(responseText);
            console.log('Response from add_event.php:', responseText); // Log response text
            if (responseText.includes("Arrangement lagt til")) {
                resetForm('addEventForm');
            }
            closeModal('eventAdminModal');
        });
});

document.addEventListener('DOMContentLoaded', loadSelectOptions);
