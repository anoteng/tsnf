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

// Hent user-type og user-id fra body-taggen
const bodyElement = document.querySelector('body');
const userType = bodyElement.getAttribute('data-user-type');
const userId = bodyElement.getAttribute('data-user-id');
const userName = bodyElement.getAttribute('data-user-name');
const isAdmin = userType === 'admin';

console.log(userType + ' ' + userId);
function createButton(cell, type, number) {
    let value = cell.getValue() || '';
    const id = cell.getData().id;
    const max_ssk = cell.getData().max_ssk;
    let buttonHtml = '';

    if (value) {
        buttonHtml += `${value} `;
        if (isAdmin || (userType === 'ridderhatt' && type === 'ridder' && value === userName) || (userType === 'ssk' && type === 'ssk' && value === userName)) {
            buttonHtml += `<button class="btn-small" onclick="removeUser(${id}, '${type}', ${number})">❌</button>`;
        }
    } else if (max_ssk < number && type === 'ssk') {
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
    return value == 0 ? false : true;
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
    return date.isValid ? date.setLocale('no').toFormat('dd.MM.yyyy') : value;
}

function formatTime(value) {
    if (!value) return '';
    let time = luxon.DateTime.fromFormat(value, 'HH:mm:ss');
    return time.isValid ? time.toFormat('HH:mm') : value;
}

document.addEventListener("DOMContentLoaded", function() {
    console.log("Document ready. Initializing DataTable...");
    document.getElementById('logout').addEventListener('click', function() {
        window.location.href = 'logout.php';
    });
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
            data.forEach(item => {
                item.dato = formatDate(item.dato);
                item.tid_fra = formatTime(item.tid_fra);
                item.tid_til = formatTime(item.tid_til);
            });
            const columns = [
                { title: "Dato", field: "dato", sorter: "date", headerFilter:"input", sorterParams: { format: "yyyy-MM-dd" }, hozAlign: "center" },
                {
                    title: "Ukedag",
                    field: "dato",
                    sorter: "date",
                    headerFilter:"input",
                    sorterParams: { format: "yyyy-MM-dd" },
                    hozAlign: "center",
                    formatter: (cell) => {
                        let value = cell.getValue();
                        if (!value) return '';
                        let date = luxon.DateTime.fromFormat(value, 'dd.MM.yyyy');
                        return date.isValid ? date.setLocale('no').toFormat('cccc') : value;
                    }
                },
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
                    { column: "dato", dir: "asc" }
                ],
                columns: columns
            });

            async function openEditModal(data) {
                try {
                    await loadDropdowns();
                    document.getElementById('edit-kommune').addEventListener('change', async function() {
                        await loadDropdown('edit-sted', 'steder', this.value);
                    });
                    await populateFormData(data);
                    setTimeout(() => {
                        document.getElementById('edit-sted').value = data.sted;
                    }, 1000); // Forsinkelse for å sikre at sted-listen er fullstendig oppdatert

                    const modal = document.getElementById('editModal');
                    modal.style.display = 'block';
                    setupModalCloseHandlers(modal);
                } catch (error) {
                    console.error('Error opening edit modal:', error);
                }
            }

            async function findAndPopulate(id, data) {
                document.getElementById(id).value = data;
                await triggerChange(id);
            }

            function triggerChange(elem) {
                return new Promise((resolve) => {
                    const e = document.getElementById(elem);
                    const event = new Event('change');
                    e.dispatchEvent(event);
                    resolve();
                });
            }

            async function populateFormData(data) {
                console.log(data);
                await findAndPopulate('edit-id', data.id);
                await findAndPopulate('edit-dato', data.dato);
                await findAndPopulate('edit-tid_fra', data.tid_fra);
                await findAndPopulate('edit-tid_til', data.tid_til);
                await findAndPopulate('edit-kommune', data.kommune);
                await new Promise(resolve => setTimeout(resolve, 100)); // Forsinkelse for å sikre at dropdown er oppdatert
                await findAndPopulate('edit-sted', data.sted);
                await findAndPopulate('edit-arrtype', data.arrtype);
                await findAndPopulate('edit-max_ssk', data.max_ssk);
                await findAndPopulate('edit-ssk1', data.ssk1);
                await findAndPopulate('edit-ssk2', data.ssk2);
                await findAndPopulate('edit-ssk3', data.ssk3);
                await findAndPopulate('edit-ridder1', data.ridder1);
                await findAndPopulate('edit-ridder2', data.ridder2);
                await findAndPopulate('edit-ridder3', data.ridder3);
            }

            async function loadDropdowns() {
                try {
                    await loadDropdown('edit-kommune', 'kommune');
                    await loadDropdown('edit-arrtype', 'arrtype');
                    await loadDropdown('edit-ssk1', 'users', 'user_role', 'ssk');
                    await loadDropdown('edit-ssk2', 'users', 'user_role', 'ssk');
                    await loadDropdown('edit-ssk3', 'users', 'user_role', 'ssk');
                    await loadDropdown('edit-ridder1', 'users', 'user_role', 'ridderhatt');
                    await loadDropdown('edit-ridder2', 'users', 'user_role', 'ridderhatt');
                    await loadDropdown('edit-ridder3', 'users', 'user_role', 'ridderhatt');
                } catch (error) {
                    console.error('Error loading dropdowns:', error);
                }
            }

            function loadDropdown(elementId, table, filter = null, filter_id = null) {
                return new Promise((resolve, reject) => {
                    const url = new URL('api/getDropdownData.php', window.location.href);
                    const params = new URLSearchParams({ table: table });
                    if (filter) {
                        params.append('filter', filter);
                        params.append('filter_string', filter_id);
                    }

                    url.search = params.toString();

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
                            resolve(data); // Løser promise med data
                        })
                        .catch(error => {
                            console.error('Error loading the dropdown data:', error);
                            reject(error); // Avviser promise med error
                        });
                });
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

            function createEditButton(cell) {
                const button = document.createElement("button");
                button.className = "edit-arrangement";
                button.setAttribute('data-id', cell.getRow().getData().id);
                button.innerText = 'Rediger';
                button.addEventListener('click', function() {
                    console.log('button click registered: ' + button.dataset.id);
                    const row = cell.getRow();
                    const data = row.getData();
                    openEditModal(data);
                });
                return button;
            }

            document.getElementById('editModalClose').addEventListener('click', function() {
                document.getElementById('editModal').style.display = 'none';
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
            // Initialiser Flatpickr for tid- og datoinput-feltene
            flatpickr.localize(flatpickr.l10ns.no);
            flatpickr("#edit-dato", {
                dateFormat: "d.m.Y",
                locale: "no"
            });

            flatpickr("#edit-tid_fra", {
                enableTime: true,
                noCalendar: true,
                dateFormat: "H:i",
                time_24hr: true
            });

            flatpickr("#edit-tid_til", {
                enableTime: true,
                noCalendar: true,
                dateFormat: "H:i",
                time_24hr: true
            });


        })
        .catch(error => {
            console.error('Error:', error);
        });
});
