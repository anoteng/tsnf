// Hent user-type og user-id fra body-taggen
const bodyElement = document.querySelector('body');
const userType = bodyElement.getAttribute('data-user-type');
const userId = bodyElement.getAttribute('data-user-id');
const userName = bodyElement.getAttribute('data-user-name');
const isAdmin = userType === 'admin';

console.log(userType + ' ' + userId);
function createButton(cell, type, number) {
    let value = cell.getValue() || '';
    //let id = cell.getRow().getData().id;
    //const row = cell.getRow();
    const id = cell.getData().id
    const max_ssk = cell.getData().max_ssk
    //console.log(max_ssk + ' - ' + type + ' - ' + number)
    let buttonHtml = '';

    if (value) {
        buttonHtml += `${value} `;
        if (isAdmin || (userType === 'ridderhatt' && type === 'ridder' && value === userName) || (userType === 'ssk' && type === 'ssk' && value === userName)) {
            buttonHtml += `<button class="btn-small" onclick="removeUser(${id}, '${type}', ${number})">❌</button>`;
        }
    } else if (max_ssk < number && type === 'ssk') {
        //console.log(`${id} - ${max_ssk} > ${number}`)
        buttonHtml = '';
    } else {
        if (isAdmin || (type === 'ridder' && userType === 'ridderhatt') || (type === 'ssk' && userType === 'ssk')) {
            buttonHtml += `<button class="btn-small" onclick="assignUser(${id}, '${type}', ${number})">Sett opp meg</button>`;
        } else {
            buttonHtml += 'Ledig';
        }
    }
    return buttonHtml;
}
function boolMutator(value) {
    if (value == 0){
        return false;
    }else{
        return true;
    }
}
function assignUser(arrangementId, type, number) {

    fetch('api/assign_user.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            arrangementId: arrangementId,
            type: type,
            number: number,
            userId: userId
        })
    })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                sendCalendarInvitation(arrangementId, type, number, userId); // Send calendar invitation
                location.reload(); // Reload the page to see the updated data
            } else {
                console.error('Error assigning user:', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
}
function updateAnnonsertStatus(id, field, value) {
    fetch('api/oppdater_annonsestatus.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            id: id,
            [field]: value
        })
    })
        .then(response => response.json())
        .then(data => {
            console.log(data.message);
        })
        .catch(error => {
            console.error('Error:', error);
        });
}
function sendCalendarInvitation(arrangementId, type, number, userId) {
    fetch('api/send_invitation.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            arrangementID: arrangementId,
            type: type,
            number: number,
            userid: userId
        })
    })
        .then(response => response.json())
        .then(data => {
            if(data.status === 'success') {
                console.log('Invitation sent successfully!');
            } else {
                console.error('Error sending invitation:', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
}


function removeUser(arrangementId, type, number) {
    fetch('api/remove_user.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            arrangementId: arrangementId,
            type: type,
            number: number
        })
    })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                location.reload(); // Reload the page to see the updated data
            } else {
                console.error('Error removing user:', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
}


function formatDate(value) {
    if (!value) return '';
    let date = luxon.DateTime.fromISO(value);
    return date.isValid ? date.toFormat('yyyy-MM-dd') : value;
}

function formatTime(value) {
    if (!value) return '';
    let time = luxon.DateTime.fromFormat(value, 'HH:mm:ss');
    return time.isValid ? time.toFormat('HH:mm') : value;
}

document.addEventListener("DOMContentLoaded", function() {
    console.log("Document ready. Initializing DataTable...");

    fetch('api/get_arrangements.php')
        .then(response => response.json())
        .then(data => {
            console.log('Data fetched:', data);
            if (data.error) {
                console.error('Error fetching data:', data.error);
                return;
            }
            const userType = document.body.dataset.userType;
            const isAdmin = userType === 'admin';

            // Formatere dato og tid før de sendes til Tabulator
            data.forEach(item => {
                item.dato = formatDate(item.dato);
                item.tid_fra = formatTime(item.tid_fra);
                item.tid_til = formatTime(item.tid_til);
            });

            const columns = [
                { title: "Dato", field: "dato", sorter: "date", headerFilter:"input", sorterParams: { format: "yyyy-MM-dd" }, hozAlign: "center" },
                { title: "Ukedag", field: "dato", sorter: "date", headerFilter:"input", sorterParams: { format: "yyyy-MM-dd" }, hozAlign: "center",
                    formatter: (cell) => {
                        let value = cell.getValue();
                        if (!value) return '';
                        let date = luxon.DateTime.fromFormat(value, 'yyyy-MM-dd');
                        return date.isValid ? date.setLocale('no').toFormat('cccc') : value;
                    }},
                { title: "Sted", field: "sted_navn", sorter: "string", headerFilter:"input", hozAlign: "center", formatter: (cell) => `<a href="https://maps.google.com/?q=${cell.getRow().getData().adresse}" target="_blank">${cell.getValue()}</a>` },
                { title: "Arrangementstype", field: "arrangementstype_navn", headerFilter:"input", sorter: "string", hozAlign: "center", formatter: "textarea"},
                { title: "Tid fra", field: "tid_fra", sorter: "time", headerFilter:true, headerFilterFunc:">=", headerFilterPlaceholder:"Start etter", sorterParams: { format: "HH:mm" }, hozAlign: "center" },
                { title: "Tid til", field: "tid_til", sorter: "time", headerFilter:true, headerFilterFunc:"<=", headerFilterPlaceholder:"Ferdig senest", sorterParams: { format: "HH:mm" }, hozAlign: "center" },
                { title: "SSK1", field: "ssk1_navn", sorter: "string", hozAlign: "center", formatter: (cell) => createButton(cell, 'ssk', 1) },
                { title: "SSK2", field: "ssk2_navn", sorter: "string", hozAlign: "center", formatter: (cell) => createButton(cell, 'ssk', 2) },
                { title: "SSK3", field: "ssk3_navn", sorter: "string", hozAlign: "center", formatter: (cell) => createButton(cell, 'ssk', 3) },
                { title: "Ridderhatt 1", field: "ridder1_navn", sorter: "string", hozAlign: "center", formatter: (cell) => createButton(cell, 'ridder', 1) },
                { title: "Ridderhatt 2", field: "ridder2_navn", sorter: "string", hozAlign: "center", formatter: (cell) => createButton(cell, 'ridder', 2) },
                { title: "Ridderhatt 3", field: "ridder3_navn", sorter: "string", hozAlign: "center", formatter: (cell) => createButton(cell, 'ridder', 3) },
                { title: "Kommentar", field: "kommentar", sorter: "string", hozAlign: "center", formatter: "textarea"},

            ]
            if (isAdmin) {
                // columns.push({ title: "Handling", formatter: (cell) => `<button class="edit-arrangement" data-id="${cell.getRow().getData().id}">Rediger</button>` });
                columns.push({ title: "Handling", formatter: (cell) => createEditButton(cell)});
                columns.push({
                    title: "Annonsert FB",
                    field: "annonsert_fb",
                    formatter: "tickCross",
                    mutator: boolMutator,
                    cellClick: function (e, cell) {
                        if (isAdmin) {
                            const currentValue = cell.getValue();
                            const newValue = !currentValue;

                            if (currentValue && !newValue) {
                                if (!confirm("Er du sikker på at du vil endre Annonsert FB tilbake til 'ikke annonsert'?")) {
                                    return;
                                }
                            }

                            cell.setValue(newValue);
                            updateAnnonsertStatus(cell.getRow().getData().id, 'annonsert_fb', newValue);
                        }
                    }
                });
                columns.push({
                    title: "Annonsert Kalender",
                    field: "annonsert_kalender",
                    formatter: "tickCross",
                    mutator: boolMutator,
                    cellClick: function (e, cell) {
                        if (isAdmin) {
                            const currentValue = cell.getValue();
                            const newValue = !currentValue;

                            if (currentValue && !newValue) {
                                if (!confirm("Er du sikker på at du vil endre Annonsert Kalender tilbake til 'ikke annonsert'?")) {
                                    return;
                                }
                            }

                            cell.setValue(newValue);
                            updateAnnonsertStatus(cell.getRow().getData().id, 'annonsert_kalender', newValue);
                        }
                    }
                });
            }

            const table = new Tabulator("#arrangementTable", {
                data: data,
                layout: "fitColumns",
                height: "auto",
                responsiveLayout: "collapse",
                pagination: "local",
                paginationSize: 10,
                initialSort: [
                    { column: "dato", dir: "asc" } // Initial sort on the date column
                ],
                columns: columns
            });

            // Åpner redigeringsmodalen og laster data dynamisk
            function openEditModal(data) {
                // Fyll ut skjemaet med eksisterende data
                populateFormData(data);

                // Laster nedtrekkslister kun når modalen åpnes
                loadDropdowns();

                // Viser modalen
                const modal = document.getElementById('editModal');
                modal.style.display = 'block';

                // Setter opp lukkefunksjonalitet for modalen
                setupModalCloseHandlers(modal);
            }

            function populateFormData(data) {
                document.getElementById('edit-id').value = data.id;
                document.getElementById('edit-dato').value = data.dato;
                document.getElementById('edit-tid_fra').value = data.tid_fra;
                document.getElementById('edit-tid_til').value = data.tid_til;
                document.getElementById('edit-kommune').value = data.kommune_navn; // Assumes options are already loaded and matched by names
                document.getElementById('edit-sted').value = data.sted_navn; // Assumes options are already loaded and matched by names
                document.getElementById('edit-arrtype').value = data.arrangementstype_navn; // Assumes options are already loaded and matched by names
                document.getElementById('edit-ssk1').value = data.ssk1_navn; // Assumes options are already loaded and matched by names
                document.getElementById('edit-ssk2').value = data.ssk2_navn;
                document.getElementById('edit-ssk3').value = data.ssk3_navn;
                document.getElementById('edit-ridder1').value = data.ridder1_navn;
                document.getElementById('edit-ridder2').value = data.ridder2_navn;
                document.getElementById('edit-ridder3').value = data.ridder3_navn;
            }

            function loadDropdowns() {
                loadDropdown('edit-kommune', 'kommune');
                document.getElementById('edit-kommune').addEventListener('change', function() {
                    loadDropdown('edit-sted', 'steder', this.value);
                });
                loadDropdown('edit-arrtype', 'arrtype');
                loadDropdown('edit-ssk1', 'ssk');
                loadDropdown('edit-ssk2', 'ssk');
                loadDropdown('edit-ssk3', 'ssk');
                loadDropdown('edit-ridder1', 'ridderhatt');
                loadDropdown('edit-ridder2', 'ridderhatt');
                loadDropdown('edit-ridder3', 'ridderhatt');
            }

            function loadDropdown(elementId, table, kommune_id = null) {
                const url = new URL('api/getDropdownData.php', window.location.href);
                url.search = new URLSearchParams({ table: table });
                if (kommune_id) {
                    url.search.append('kommune_id', kommune_id);
                }

                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        const select = document.getElementById(elementId);
                        select.innerHTML = ''; // Tømmer eksisterende valg
                        data.forEach(item => {
                            const option = document.createElement('option');
                            option.value = item.id;
                            option.textContent = item.navn;
                            select.appendChild(option);
                        });
                        // Kanskje sett valgt verdi her hvis det er relevant
                    })
                    .catch(error => console.error('Error loading the dropdown data:', error));
            }

            function setupModalCloseHandlers(modal) {
                document.getElementById('editModalClose').onclick = function() {
                    modal.style.display = 'none';
                };
                window.onclick = function(event) {
                    if (event.target == modal) {
                        modal.style.display = 'none';
                    }
                };
            }

            function createEditButton(cell){
                const button = document.createElement("button")
                button.className = "edit-arrangement"
                button.setAttribute('data-id', cell.getRow().getData().id)
                button.innerText = 'Rediger'
                button.addEventListener('click', function() {
                    console.log('button click registered: ' + button.dataset.id);
                    // const id = button.dataset.id;
                    const row = cell.getRow();
                    const data = row.getData();
                    openEditModal(data);
                })
                return button

            }


            document.querySelectorAll('.edit-arrangement').forEach(button => {
                button.addEventListener('click', function() {
                    console.log('button click registered: ' + button.dataset.id);
                    const id = button.dataset.id;
                    const row = table.getRow(id);
                    const data = row.getData();
                    openEditModal(data);
                });
            });

            document.getElementById('editModalClose').addEventListener('click', function() {
                document.getElementById('editModal').style.display = 'none';
            });

            document.getElementById('filterLedige').addEventListener('click', function() {
                table.setFilter([
                    [
                        { field: "ssk1_navn", type: "=", value: "" },
                        { field: "ssk2_navn", type: "=", value: "" },
                        { field: "ssk3_navn", type: "=", value: "" },
                        { field: "ridder1_navn", type: "=", value: "" },
                        { field: "ridder2_navn", type: "=", value: "" },
                        { field: "ridder3_navn", type: "=", value: "" }
                    ]
                ]);
            });

            document.getElementById('filterMine').addEventListener('click', function() {
                table.setFilter([
                    [
                        { field: "ssk1", type: "=", value: userId },
                        { field: "ssk2", type: "=", value: userId },
                        { field: "ssk3", type: "=", value: userId },
                        { field: "ridder1", type: "=", value: userId },
                        { field: "ridder2", type: "=", value: userId },
                        { field: "ridder3", type: "=", value: userId }
                    ]
                ]);
            });

            document.getElementById('clearFilters').addEventListener('click', function() {
                table.clearFilter();
            });

        })
        .catch(error => {
            console.error('Error:', error);
        });
});
