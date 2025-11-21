const einsatzId = parseInt(document.body.getAttribute('data-einsatz-id'));

function getPilot(element) {
    return element.parentNode.querySelector('.pilot').value;
}

function getCoPilot(element) {
    return element.parentNode.querySelector('.copilot').value;
}

function getAkku(element) {
    return element.parentNode.querySelector('.akku').value;
}

const drohnenData = {};

const domCache = {
    tbody: null,
    textEntry: null
};

let flugdauerInterval = null;

document.addEventListener("DOMContentLoaded", () => {
    domCache.tbody = document.querySelector("#eintraegeTabelle tbody");
    domCache.textEntry = document.getElementById("textEntry");
    
    document.querySelectorAll('.quick-action').forEach(div => {
        const id = div.dataset.drohneId;
        const storedData = localStorage.getItem(`einsatz_${einsatzId}_drohne_${id}`);
        let data = {};
        if (storedData) {
            try {
                data = JSON.parse(storedData);
            } catch (e) {
                const pilot = localStorage.getItem(`einsatz_${einsatzId}_drohne_${id}_pilot`);
                const copilot = localStorage.getItem(`einsatz_${einsatzId}_drohne_${id}_copilot`);
                const akku = localStorage.getItem(`einsatz_${einsatzId}_drohne_${id}_akku`);
                data = { pilot, copilot, akku };
            }
        }
        
        const startTime = localStorage.getItem(`einsatz_${einsatzId}_drohne_${id}_startzeit`);

        if (data.pilot) div.querySelector('.pilot').value = data.pilot;
        if (data.copilot) div.querySelector('.copilot').value = data.copilot;
        if (data.akku) div.querySelector('.akku').value = data.akku;

        if (startTime && !isNaN(startTime)) {
            drohnenData[id] = { startzeit: parseInt(startTime), aktiv: true };
            const icon = div.querySelector('img[src*="flugzeug_start"]');
            if (icon) {
                icon.src = './img/flugzeug_landung.png';
                icon.setAttribute('data-status', 'gestartet');
            }
        }
    });

    flugdauerInterval = setInterval(updateFlugdauer, 1000);
    
    window.addEventListener('beforeunload', () => {
        if (flugdauerInterval) {
            clearInterval(flugdauerInterval);
        }
    });
    
    const accordionButtons = document.querySelectorAll('.accordion');
    const container = document.querySelector('.accordion-tabs-container');
    const panels = container ? Array.from(container.parentElement.querySelectorAll('.panel')) : [];
    
    accordionButtons.forEach((button, index) => {
        if (index === 0 && panels[index]) {
            button.classList.add('active');
            panels[index].classList.add('active');
        }
        
        button.addEventListener('click', function() {
            const isCurrentlyActive = this.classList.contains('active');
            
            accordionButtons.forEach(otherButton => {
                otherButton.classList.remove('active');
            });
            
            panels.forEach(panel => {
                panel.classList.remove('active');
            });
            
            if (!isCurrentlyActive && panels[index]) {
                this.classList.add('active');
                panels[index].classList.add('active');
            }
        });
    });
});

function saveQuickData(element) {
    const parent = element.closest('.quick-action');
    const id = parent.dataset.drohneId;

    const $parent = {
        pilot: parent.querySelector('.pilot'),
        copilot: parent.querySelector('.copilot'),
        akku: parent.querySelector('.akku')
    };

    const pilot = $parent.pilot.value;
    const copilot = $parent.copilot.value;
    const akku = $parent.akku.value;

    const data = { pilot, copilot, akku };
    localStorage.setItem(`einsatz_${einsatzId}_drohne_${id}`, JSON.stringify(data));
}

function toggleFlight(img) {
    const parent = img.closest('.quick-action');
    const id = parent.dataset.drohneId;
    const name = parent.dataset.drohneName;
    
    // Cache DOM queries
    const $parent = {
        pilot: parent.querySelector('.pilot'),
        copilot: parent.querySelector('.copilot'),
        akku: parent.querySelector('.akku')
    };
    
    const pilot = $parent.pilot.value;
    const copilot = $parent.copilot.value;
    const akku = $parent.akku.value;
    const location_id = 1;

    let text = "";
    const now = Date.now();

    if (img.getAttribute("data-status") === "gelandet") {
        text = `${name} mit Pilot ${pilot} und Co-Pilot ${copilot} ist mit Akku ${akku} gestartet.`;
        img.src = "./img/flugzeug_landung.png";
        img.setAttribute("data-status", "gestartet");

        drohnenData[id] = { startzeit: now, aktiv: true };
        localStorage.setItem(`einsatz_${einsatzId}_drohne_${id}_startzeit`, now);

    } else {
        const startTimeStr = localStorage.getItem(`einsatz_${einsatzId}_drohne_${id}_startzeit`);
        const start = startTimeStr ? parseInt(startTimeStr) : null;
        
        if (!start || isNaN(start)) {
            console.error('Invalid start time for drone', id);
            return;
        }
        
        const flugdauerSek = Math.floor((now - start) / 1000);
        const min = String(Math.floor(flugdauerSek / 60)).padStart(2, '0');
        const sec = String(flugdauerSek % 60).padStart(2, '0');

        text = `${name} mit Pilot ${pilot} und Co-Pilot ${copilot} ist mit Akku ${akku} gelandet. Flugdauer: ${min} Min ${sec} Sec`;
        img.src = "./img/flugzeug_start.png";
        img.setAttribute("data-status", "gelandet");

        delete drohnenData[id];
        localStorage.removeItem(`einsatz_${einsatzId}_drohne_${id}_startzeit`);

        const data = {
            pilot: pilot,
            copilot: copilot,
            drone_id: id,
            battery_number: parseInt(akku) || 0,
            flight_start: new Date(start).toISOString(),
            flight_end: new Date(now).toISOString(),
            location_id: location_id
        };

        fetch('insert_flight.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(result => {
            if (result.error) {
                console.error('Flight insert error:', result.error);
            } else {
                console.log('Flight inserted:', result.message || result);
            }
        })
        .catch(error => {
            console.error('Error inserting flight:', error);
        });
    }

    insertQuickText(text);
}


function getDrohnentext(el, message) {
    const parent = el.closest('.quick-action');
    const name = parent.dataset.drohneName;
    const pilot = parent.querySelector('.pilot').value;
    return `${name} mit Pilot ${pilot}${message}`;
}

function updateFlugdauer() {
    const now = Date.now();
    for (const id in drohnenData) {
        const div = document.querySelector(`.quick-action[data-drohne-id="${id}"]`);
        const start = drohnenData[id].startzeit;
        const durationSec = Math.floor((now - start) / 1000);
        const min = String(Math.floor(durationSec / 60)).padStart(2, '0');
        const sec = String(durationSec % 60).padStart(2, '0');
        div.querySelector('.flugdauer').textContent = `Flugdauer: ${min}:${sec}`;
    }
}
function insertQuickText(text) {
    ajaxQuickInsert(text);
}

function ajaxQuickInsert(text) {
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'ajax_insert.php?einsatz_id=' + einsatzId, true);
    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (xhr.status === 200) {
            try {
                const response = JSON.parse(xhr.responseText);
                if (response.error) {
                    alert('Fehler: ' + response.error);
                } else {
                    addEntryToTable(response.zeilennummer, response.zeitpunkt, response.text);
                }
            } catch (e) {
                alert('Fehler beim Verarbeiten der Antwort.');
                console.error('Parse error:', e);
            }
        } else {
            alert('Fehler beim Speichern des Eintrags. Status: ' + xhr.status);
        }
    };
    xhr.onerror = function() {
        alert('Netzwerkfehler beim Speichern des Eintrags.');
    };
    xhr.send('text=' + encodeURIComponent(text));
}

const newEntryForm = document.getElementById("newEntryForm");
if (newEntryForm) {
    newEntryForm.addEventListener("submit", function(event) {
        event.preventDefault();

        let text = domCache.textEntry.value;
        if (text.trim() === "") return;

        const xhr = new XMLHttpRequest();
        xhr.open("POST", "ajax_insert.php?einsatz_id=" + einsatzId, true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

        xhr.onload = function() {
            if (xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.error) {
                        alert('Fehler: ' + response.error);
                    } else {
                        addEntryToTable(response.zeilennummer, response.zeitpunkt, response.text);
                        domCache.textEntry.value = "";
                    }
                } catch (e) {
                    alert('Fehler beim Verarbeiten der Antwort.');
                    console.error('Parse error:', e);
                }
            } else {
                alert("Fehler beim Speichern des Eintrags. Status: " + xhr.status);
            }
        };
        
        xhr.onerror = function() {
            alert("Netzwerkfehler beim Speichern des Eintrags.");
        };

        xhr.send("text=" + encodeURIComponent(text));
    });
}

function addEntryToTable(zeilennummer, zeitpunkt, text) {
    if (!domCache.tbody) return;
    const row = document.createElement("tr");
    row.innerHTML = `<td>${zeilennummer}</td><td>${zeitpunkt}</td><td>${text}</td>`;
    domCache.tbody.prepend(row);
}


function sortTable(columnIndex) {
    const table = document.getElementById("eintraegeTabelle");
    if (!table) return;
    
    const tbody = table.getElementsByTagName("tbody")[0];
    if (!tbody) return;
    
    const rows = Array.from(tbody.getElementsByTagName("tr"));
    const headers = table.getElementsByTagName("th");

    let ascending = table.getAttribute("data-sort-" + columnIndex) !== "asc";
    table.setAttribute("data-sort-" + columnIndex, ascending ? "asc" : "desc");

    for (let i = 0; i < headers.length; i++) {
        headers[i].innerHTML = headers[i].innerHTML.replace(" 🔽", "").replace(" 🔼", "");
    }
    headers[columnIndex].innerHTML += ascending ? " 🔼" : " 🔽";

    rows.sort((rowA, rowB) => {
        let cellA = rowA.getElementsByTagName("td")[columnIndex].innerText.trim();
        let cellB = rowB.getElementsByTagName("td")[columnIndex].innerText.trim();

        if (columnIndex === 1) {
            let dateA = new Date(cellA);
            let dateB = new Date(cellB);
            return ascending ? dateA - dateB : dateB - dateA;
        }

        if (!isNaN(cellA) && !isNaN(cellB)) {
            return ascending ? cellA - cellB : cellB - cellA;
        }

        return ascending ? cellA.localeCompare(cellB) : cellB.localeCompare(cellA);
    });

    const fragment = document.createDocumentFragment();
    rows.forEach(row => fragment.appendChild(row));
    tbody.innerHTML = "";
    tbody.appendChild(fragment);
}

