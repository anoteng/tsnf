document.addEventListener('DOMContentLoaded', function() {
    const sections = document.querySelectorAll('section'); // Selektorer for alle section-elementene
    const links = document.querySelectorAll('.sidebar a'); // Selektorer for alle linker i sidebar

    function toggleSection(event) {
        event.preventDefault();
        const targetId = event.target.getAttribute('href').slice(1); // Fjerner '#' fra href for å matche id
        const targetSection = document.getElementById(targetId);
        console.log(`${targetId} clicked: ${targetSection.id}`);

        // Hvis seksjonen er synlig, skjul den, ellers skjul alle seksjoner og vis den valgte
        if (!targetSection.classList.contains('hidden')) {
            targetSection.classList.add('hidden');
        } else {
            sections.forEach(sec => sec.classList.add('hidden')); // Skjuler alle seksjoner
            targetSection.classList.remove('hidden'); // Viser den valgte seksjonen
        }
    }

    links.forEach(link => link.addEventListener('click', toggleSection)); // Legger til klikk-event til alle sidebar linker
});
