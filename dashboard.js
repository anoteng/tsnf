// Hent user-type og user-id fra body-taggen
const bodyElement = document.querySelector('body');
const userType = bodyElement.getAttribute('data-user-type');
const userId = bodyElement.getAttribute('data-user-id');
const isAdmin = userType === 'admin';

function createButton(cell, type, number) {
    let value = cell.getValue() || '';
    let id = cell.getRow().getData().id;
    let buttonHtml = '';

    if (value) {
        buttonHtml += `${value} `;
        if (isAdmin) {
            buttonHtml += `<button class="btn-small" onclick="removeUser(${id}, '${type}', ${number})">Fjern</button>`;
        }
    } else {
        if (isAdmin) {
            buttonHtml += `<button class="btn-small" onclick="assignUser(${id}, '${type}', ${number})">Sett opp meg</button>`;
        } else {
            buttonHtml += 'Ingen satt opp';
        }
    }
    return buttonHtml;
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
                location.reload(); // Reload the page to see the updated data
            } else {
                console.error('Error assigning user:', data.message);
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

            // Formatere dato og tid før de sendes til Tabulator
            data.forEach(item => {
                item.dato = formatDate(item.dato);
                item.tid_fra = formatTime(item.tid_fra);
                item.tid_til = formatTime(item.tid_til);
            });

            var table = new Tabulator("#arrangementTable", {
                data: data,
                layout: "fitDataStretch",
                responsiveLayout: "collapse",
                pagination: "local",
                paginationSize: 10,
                initialSort: [
                    { column: "dato", dir: "asc" } // Initial sort on the date column
                ],
                columns: [
                    { title: "Dato", field: "dato", sorter: "date", sorterParams: { format: "yyyy-MM-dd" }, hozAlign: "center" },
                    { title: "Ukedag", field: "dato", sorter: "date", sorterParams: { format: "yyyy-MM-dd" }, hozAlign: "center",
                        formatter: (cell) => {
                            let value = cell.getValue();
                            if (!value) return '';
                            let date = luxon.DateTime.fromFormat(value, 'yyyy-MM-dd');
                            return date.isValid ? date.setLocale('no').toFormat('cccc') : value;
                        }},
                    { title: "Sted", field: "sted_navn", sorter: "string", hozAlign: "center", formatter: (cell) => `<a href="https://maps.google.com/?q=${cell.getRow().getData().adresse}" target="_blank">${cell.getValue()}</a>` },
                    { title: "Arrangementstype", field: "arrangementstype_navn", sorter: "string", hozAlign: "center" },
                    { title: "Tid fra", field: "tid_fra", sorter: "time", sorterParams: { format: "HH:mm" }, hozAlign: "center" },
                    { title: "Tid til", field: "tid_til", sorter: "time", sorterParams: { format: "HH:mm" }, hozAlign: "center" },
                    { title: "SSK1", field: "ssk1_navn", sorter: "string", hozAlign: "center", formatter: (cell) => createButton(cell, 'ssk', 1) },
                    { title: "SSK2", field: "ssk2_navn", sorter: "string", hozAlign: "center", formatter: (cell) => createButton(cell, 'ssk', 2) },
                    { title: "SSK3", field: "ssk3_navn", sorter: "string", hozAlign: "center", formatter: (cell) => createButton(cell, 'ssk', 3) },
                    { title: "Ridderhatt 1", field: "ridder1_navn", sorter: "string", hozAlign: "center", formatter: (cell) => createButton(cell, 'ridder', 1) },
                    { title: "Ridderhatt 2", field: "ridder2_navn", sorter: "string", hozAlign: "center", formatter: (cell) => createButton(cell, 'ridder', 2) },
                    { title: "Ridderhatt 3", field: "ridder3_navn", sorter: "string", hozAlign: "center", formatter: (cell) => createButton(cell, 'ridder', 3) },
                    { title: "Handling", formatter: (cell) => `<button class="edit-arrangement" data-id="${cell.getRow().getData().id}">Rediger</button>` }
                ]
            });

            document.querySelectorAll('.edit-arrangement').forEach(button => {
                button.addEventListener('click', function() {
                    const id = button.dataset.id;
                    const row = table.getRow(id);
                    const data = row.getData();
                    openEditModal(data);
                });
            });

            document.getElementById('closeModalBtn').addEventListener('click', function() {
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
