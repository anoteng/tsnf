// Get the modal
const modal = document.getElementById("editModal");

// Get the button that opens the modal
const btns = document.querySelectorAll(".edit-arrangement");

// Get the <span> element that closes the modal
const span = document.getElementsByClassName("close")[0];

// When the user clicks on the button, open the modal
btns.forEach(function(btn) {
    btn.onclick = function() {
        modal.style.display = "block";
    }
});

// When the user clicks on <span> (x), close the modal
span.onclick = function() {
    modal.style.display = "none";
}

// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
    if (event.target == modal) {
        modal.style.display = "none";
    }
}
function saveChanges() {
    var data = {
        id: document.getElementById('edit-id').value,
        dato: document.getElementById('edit-dato').value,
        tid_fra: document.getElementById('edit-tid_fra').value,
        tid_til: document.getElementById('edit-tid_til').value,
        sted: document.getElementById('edit-sted').value,
        arrtype: document.getElementById('edit-arrtype').value,
        ssk1: document.getElementById('edit-ssk1').value,
        ssk2: document.getElementById('edit-ssk2').value,
        ssk3: document.getElementById('edit-ssk3').value,
        ridder1: document.getElementById('edit-ridder1').value,
        ridder2: document.getElementById('edit-ridder2').value,
        ridder3: document.getElementById('edit-ridder3').value
    };

    fetch('api/saveChanges.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
        .then(response => response.text())
        .then(result => {
            alert(result);
            // Lukk modalen og oppdater siden eller listevisningen her om nødvendig
            document.getElementById('editModal').style.display = 'none';
        })
        .catch(error => {
            console.error('Error:', error);
        });
}

// Knytt denne funksjonen til lagre-knappen
document.getElementById('editModalSave').addEventListener('click', saveChanges);

