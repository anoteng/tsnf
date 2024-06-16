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
        var user_type = "<?php echo $user_type; ?>";
        var user_id = "<?php echo $user_id; ?>";

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
                { title: "Tid fra", field: "tid_fra", headerFilter: "input", editor: timeEditor },
                { title: "Tid til", field: "tid_til", headerFilter: "input", editor: timeEditor },
                { title: "SSK1", field: "ssk1_navn", editor: sskEditor },
                { title: "SSK2", field: "ssk2_navn", editor: sskEditor },
                { title: "SSK3", field: "ssk3_navn", editor: sskEditor },
                { title: "Ridderhatt 1", field: "ridder1_navn", editor: ridderEditor },
                { title: "Ridderhatt 2", field: "ridder2_navn", editor: ridderEditor },
                { title: "Ridderhatt 3", field: "ridder3_navn", editor: ridderEditor },
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

        function timeEditor(cell, onRendered, success, cancel) {
            var editor = document.createElement("input");
            editor.setAttribute("type", "time");
            editor.value = cell.getValue();

            onRendered(function() {
                editor.focus();
                editor.style.cssText = "width:100%; height:100%; padding:0; margin:0; box-sizing:border-box";
            });

            editor.addEventListener("change", function() {
                success(editor.value);
            });

            return editor;
        }

        function sskEditor(cell, onRendered, success, cancel) {
            var editor = document.createElement("select");

            fetch('api/get_ssk.php')
                .then(response => response.json())
                .then(data => {
                    editor.innerHTML = "<option value=''>Ingen</option>";
                    data.ssk.forEach(function(ssk) {
                        var option = document.createElement("option");
                        option.value = ssk.id;
                        option.text = ssk.navn;
                        if (ssk.id == cell.getValue()) {
                            option.selected = true;
                        }
                        editor.appendChild(option);
                    });

                    if (user_type === 'ssk' && user_id) {
                        var option = document.createElement("option");
                        option.value = user_id;
                        option.text = "Meg";
                        editor.appendChild(option);
                    }

                    // Ensure the editor is displayed after options are loaded
                    onRendered(function() {
                        editor.focus();
                        editor.style.cssText = "width:100%; height:100%; padding:0; margin:0; box-sizing:border-box";
                    });

                    editor.addEventListener("change", function() {
                        success(editor.value);
                    });
                })
                .catch(error => {
                    console.error('Error fetching SSK data:', error);
                    cancel();
                });

            return editor;
        }

        function ridderEditor(cell, onRendered, success, cancel) {
            var editor = document.createElement("select");

            fetch('api/get_ridderhatt.php')
                .then(response => response.json())
                .then(data => {
                    editor.innerHTML = "<option value=''>Ingen</option>";
                    data.ridderhatt.forEach(function(ridder) {
                        var option = document.createElement("option");
                        option.value = ridder.id;
                        option.text = ridder.navn;
                        if (ridder.id == cell.getValue()) {
                            option.selected = true;
                        }
                        editor.appendChild(option);
                    });

                    if (user_type === 'ridderhatt' && user_id) {
                        var option = document.createElement("option");
                        option.value = user_id;
                        option.text = "Meg";
                        editor.appendChild(option);
                    }

                    // Ensure the editor is displayed after options are loaded
                    onRendered(function() {
                        editor.focus();
                        editor.style.cssText = "width:100%; height:100%; padding:0; margin:0; box-sizing:border-box";
                    });

                    editor.addEventListener("change", function() {
                        success(editor.value);
                    });
                })
                .catch(error => {
                    console.error('Error fetching Ridderhatt data:', error);
                    cancel();
                });

            return editor;
        }

        // Filter functionality
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
    }

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
        }
    });
});
