document.addEventListener('DOMContentLoaded', function() {
    console.log("Document ready. Initializing Tabulator...");

    fetch('api/get_arrangementer.php')
        .then(response => response.json())
        .then(data => {
            console.log(data);
            if (data.success) {
                initializeTable(data.arrangementer);
            } else {
                console.error("Failed to load data: " + data.message);
            }
        })
        .catch(error => console.error('Error fetching data:', error));

    function initializeTable(arrangementer) {
        var table = new Tabulator("#arrangementTable", {
            data: arrangementer,
            layout: "fitColumns",
            columns: [
                { title: "Dato", field: "dato", sorter: "date", headerFilter: "input" },
                { title: "Ukedag", field: "ukedag", headerFilter: "input" },
                { title: "Sted", field: "sted_navn", headerFilter: "input", formatter: function(cell) {
                        var row = cell.getRow().getData();
                        return `<a href="https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(row.sted_adresse)}" target="_blank">${cell.getValue()}</a>`;
                    }},
                { title: "Arrangementstype", field: "arrangementstype", headerFilter: "input" },
                { title: "Tid fra", field: "tid_fra", headerFilter: "input", formatter: function(cell) {
                        return cell.getValue() ? cell.getValue().substring(0, 5) : '';
                    }},
                { title: "Tid til", field: "tid_til", headerFilter: "input", formatter: function(cell) {
                        return cell.getValue() ? cell.getValue().substring(0, 5) : '';
                    }},
                { title: "SSK1", field: "ssk1_navn", formatter: sskFormatter(1) },
                { title: "SSK2", field: "ssk2_navn", formatter: sskFormatter(2) },
                { title: "SSK3", field: "ssk3_navn", formatter: sskFormatter(3) },
                { title: "Ridderhatt 1", field: "ridder1_navn", formatter: ridderFormatter(1) },
                { title: "Ridderhatt 2", field: "ridder2_navn", formatter: ridderFormatter(2) },
                { title: "Ridderhatt 3", field: "ridder3_navn", formatter: ridderFormatter(3) },
                { title: "Handling", formatter: function(cell) {
                        return '<button class="btn btn-primary save-changes" data-id="' + cell.getRow().getData().id + '">Lagre</button>';
                    }}
            ],
            initialSort: [
                { column: "dato", dir: "asc" }
            ],
            pagination: "local",
            paginationSize: 10
        });

        function sskFormatter(index) {
            return function(cell) {
                var value = cell.getValue();
                console.log(`SSK Formatter: value=${value}, index=${index}`);
                if (!value) {
                    if (user_type === 'ssk' || user_type === 'admin') {
                        return '<button class="btn btn-success ssk-assign" data-id="' + cell.getRow().getData().id + '" data-index="' + index + '">Sett opp meg</button>';
                    } else {
                        return '';
                    }
                } else {
                    console.log(`SSK Remove: value=${value}, user_id=${user_id}, index=${index}`);
                    return value + (user_type === 'admin' || (user_type === 'ssk' && value == user_id) ? ' <button class="btn btn-danger ssk-remove" data-id="' + cell.getRow().getData().id + '" data-index="' + index + '">Fjern</button>' : '');
                }
            };
        }

        function ridderFormatter(index) {
            return function(cell) {
                var value = cell.getValue();
                console.log(`Ridder Formatter: value=${value}, index=${index}`);
                if (!value) {
                    if (user_type === 'ridderhatt' || user_type === 'admin') {
                        return '<button class="btn btn-success ridder-assign" data-id="' + cell.getRow().getData().id + '" data-index="' + index + '">Sett opp meg</button>';
                    } else {
                        return '';
                    }
                } else {
                    console.log(`Ridder Remove: value=${value}, user_id=${user_id}, index=${index}`);
                    return value + (user_type === 'admin' || (user_type === 'ridderhatt' && value == user_id) ? ' <button class="btn btn-danger ridder-remove" data-id="' + cell.getRow().getData().id + '" data-index="' + index + '">Fjern</button>' : '');
                }
            };
        }

        document.getElementById('filterLedige').addEventListener('click', function() {
            table.setFilter(function(data) {
                return data.ssk1_navn === '' || data.ssk2_navn === '' || data.ssk3_navn === '' || data.ridder1_navn === '' || data.ridder2_navn === '' || data.ridder3_navn === '';
            });
        });

        document.getElementById('filterMine').addEventListener('click', function() {
            table.setFilter(function(data) {
                return data.ssk1_navn === user_id || data.ssk2_navn === user_id || data.ssk3_navn === user_id || data.ridder1_navn === user_id || data.ridder2_navn === user_id || data.ridder3_navn === user_id;
            });
        });

        document.getElementById('clearFilters').addEventListener('click', function() {
            table.clearFilter();
        });

        document.addEventListener('click', function(event) {
            if (event.target.classList.contains('save-changes')) {
                var row = event.target.closest('.tabulator-row');
                var id = event.target.getAttribute('data-id');
                var data = table.getRow(id).getData();

                fetch('api/update_event.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                })
                    .then(response => response.json())
                    .then(response => {
                        alert(response.success ? 'Endringer lagret' : 'Feil: ' + response.message);
                    })
                    .catch(error => console.error('Error updating event:', error));
            } else if (event.target.classList.contains('ssk-assign')) {
                var id = event.target.getAttribute('data-id');
                var index = event.target.getAttribute('data-index');
                console.log(`SSK Assign: id=${id}, index=${index}`);

                fetch('api/assign_ssk.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ id: id, user_id: user_id, index: index })
                })
                    .then(response => response.json())
                    .then(response => {
                        if (response.success) {
                            table.updateData([{ id: id, ['ssk' + index + '_navn']: user_id }]);
                        } else {
                            alert('Feil: ' + response.message);
                        }
                    })
                    .catch(error => console.error('Error assigning SSK:', error));
            } else if (event.target.classList.contains('ssk-remove')) {
                if (!confirm('Er du sikker på at du vil fjerne deg fra denne vakten?')) {
                    return;
                }

                var id = event.target.getAttribute('data-id');
                var index = event.target.getAttribute('data-index');
                console.log(`SSK Remove: id=${id}, index=${index}`);

                fetch('api/remove_ssk.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ id: id, user_id: user_id, index: index })
                })
                    .then(response => response.json())
                    .then(response => {
                        if (response.success) {
                            table.updateData([{ id: id, ['ssk' + index + '_navn']: '' }]);
                        } else {
                            alert('Feil: ' + response.message);
                        }
                    })
                    .catch(error => console.error('Error removing SSK:', error));
            } else if (event.target.classList.contains('ridder-assign')) {
                var id = event.target.getAttribute('data-id');
                var index = event.target.getAttribute('data-index');
                console.log(`Ridder Assign: id=${id}, index=${index}`);

                fetch('api/assign_ridder.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ id: id, user_id: user_id, index: index })
                })
                    .then(response => response.json())
                    .then(response => {
                        if (response.success) {
                            table.updateData([{ id: id, ['ridder' + index + '_navn']: user_id }]);
                        } else {
                            alert('Feil: ' + response.message);
                        }
                    })
                    .catch(error => console.error('Error assigning ridder:', error));
            } else if (event.target.classList.contains('ridder-remove')) {
                if (!confirm('Er du sikker på at du vil fjerne deg fra denne vakten?')) {
                    return;
                }

                var id = event.target.getAttribute('data-id');
                var index = event.target.getAttribute('data-index');
                console.log(`Ridder Remove: id=${id}, index=${index}`);

                fetch('api/remove_ridder.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ id: id, user_id: user_id, index: index })
                })
                    .then(response => response.json())
                    .then(response => {
                        if (response.success) {
                            table.updateData([{ id: id, ['ridder' + index + '_navn']: '' }]);
                        } else {
                            alert('Feil: ' + response.message);
                        }
                    })
                    .catch(error => console.error('Error removing ridder:', error));
            }
        });
    }
});
