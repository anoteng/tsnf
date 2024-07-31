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
            const municipalitySelect = document.getElementById('kommuneLocation');
            if (municipalitySelect) {
                municipalitySelect.innerHTML = '';
                municipalities.forEach(municipality => {
                    const option = document.createElement('option');
                    option.value = municipality.id;
                    option.textContent = municipality.navn;
                    municipalitySelect.appendChild(option);
                });
            } else {
                console.error('Municipality select element not found for Location.');
            }
        })
        .catch(error => console.error('Error loading municipalities for Location:', error));
}

// Last inn kommuner for arrangementadministrasjon
function loadMunicipalitiesForEvent() {
    fetch('api/get_municipalities.php')
        .then(response => response.json())
        .then(municipalities => {
            const municipalitySelect = document.getElementById('kommuneEvent');
            if (municipalitySelect) {
                municipalitySelect.innerHTML = '';
                municipalities.forEach(municipality => {
                    const option = document.createElement('option');
                    option.value = municipality.id;
                    option.textContent = municipality.navn;
                    municipalitySelect.appendChild(option);
                });
            } else {
                console.error('Municipality select element not found for Event.');
            }
        })
        .catch(error => console.error('Error loading municipalities for Event:', error));
}

// Last inn steder basert på valgt kommune for arrangementadministrasjon
function loadLocations() {
    const kommuneId = document.getElementById('kommuneEvent').value;
    fetch(`api/get_locations.php?kommune_id=${kommuneId}`)
        .then(response => response.json())
        .then(locations => {
            const locationSelect = document.getElementById('sted');
            locationSelect.innerHTML = '';
            locations.forEach(location => {
                const option = document.createElement('option');
                option.value = location.id;
                option.textContent = location.navn;
                locationSelect.appendChild(option);
            });
        })
        .catch(error => console.error('Error loading locations:', error));
}

// Last inn arrangementstyper
function loadEventTypes() {
    fetch('api/get_event_types.php')
        .then(response => response.json())
        .then(eventTypes => {
            const eventTypeSelect = document.getElementById('arrtype');
            eventTypeSelect.innerHTML = '';
            eventTypes.forEach(type => {
                const option = document.createElement('option');
                option.value = type.id;
                option.textContent = type.type;
                eventTypeSelect.appendChild(option);
            });
        })
        .catch(error => console.error('Error loading event types:', error));
}

// Funksjon for å tømme skjemaet
function resetForm(formId) {
    document.getElementById(formId).reset();
}

// Hent brukere og fyll ut select elementene
function fetchUsers(query, callback) {
    fetch(`api/get_users.php?q=${query}`)
        .then(response => response.json())
        .then(data => {
            callback(data);
        })
        .catch(error => console.error('Error fetching users:', error));
}

// Initialiser søkbare nedtrekkslister
function initSearchableSelect(selectId) {
    const selectElement = document.getElementById(selectId);

    function fetchAndPopulateUsers(query) {
        fetchUsers(query, function(data) {
            // Lagre valgt verdi og tekst
            const selectedValue = selectElement.dataset.selectedValue;
            const selectedText = selectElement.dataset.selectedText;

            selectElement.innerHTML = '';
            data.forEach(user => {
                const option = document.createElement('option');
                option.value = user.id;
                option.textContent = user.text;
                selectElement.appendChild(option);
            });

            // Gjenopprett valgt verdi og tekst
            if (selectedValue && selectedText) {
                const option = document.createElement('option');
                option.value = selectedValue;
                option.textContent = selectedText;
                option.selected = true;
                selectElement.appendChild(option);
            }
        });
    }

    selectElement.addEventListener('focus', function() {
        fetchAndPopulateUsers('');
    });

    selectElement.addEventListener('input', function() {
        const query = selectElement.value;
        fetchAndPopulateUsers(query);
    });

    selectElement.addEventListener('change', function() {
        const selectedOption = selectElement.options[selectElement.selectedIndex];
        const selectedValue = selectedOption ? selectedOption.value : '';
        const selectedText = selectedOption ? selectedOption.textContent : '';
        selectElement.dataset.selectedValue = selectedValue;
        selectElement.dataset.selectedText = selectedText;
    });
}

// Last inn kommuner og arrangementstyper ved sideinnlasting
function loadSelectOptions() {
    loadMunicipalitiesForLocation();
    loadMunicipalitiesForEvent();
    loadEventTypes();
}

document.addEventListener('DOMContentLoaded', function() {
    loadSelectOptions();
    initSearchableSelect('ssk1');
    initSearchableSelect('ssk2');
    initSearchableSelect('ssk3');
    initSearchableSelect('ridderhatt1');
    initSearchableSelect('ridderhatt2');
    initSearchableSelect('ridderhatt3');

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
                if (responseText.includes("Bruker lagt til")) {
                    resetForm('addUserForm');
                }
                closeModal('userAdminModal');
            });
    });

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
                if (responseText.includes("Lokasjon lagt til")) {
                    resetForm('addLocationForm');
                }
                closeModal('locationAdminModal');
            });
    });

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
                if (responseText.includes("Arrangement lagt til")) {
                    resetForm('addEventForm');
                }
                closeModal('eventAdminModal');
            });
    });
});
