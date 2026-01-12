let updateTableInterval = null;

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

function updateTable() {
    const einsatzId = document.body.getAttribute('data-einsatz-id');
    if (!einsatzId) return;
    
    fetch(`/api/v1/einsatz/${einsatzId}/dokumentation`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                const eintraege = data.data;
                const tbody = document.querySelector("#eintraegeTabelle tbody");
                if (!tbody) return;
                
                const fragment = document.createDocumentFragment();
                eintraege.forEach(e => {
                    const row = document.createElement("tr");
                    row.innerHTML = `<td>${e.zeilennummer}</td><td>${e.zeitpunkt}</td><td>${e.text}</td>`;
                    fragment.appendChild(row);
                });
                tbody.innerHTML = "";
                tbody.appendChild(fragment);
            } else {
                console.error('Error:', data.error?.message || 'Unknown error');
            }
        })
        .catch(error => {
            console.error('Network error updating table:', error);
        });
}

document.addEventListener("DOMContentLoaded", () => {
    updateTable();
    updateTableInterval = setInterval(updateTable, 10000);
    
    window.addEventListener('beforeunload', () => {
        if (updateTableInterval) {
            clearInterval(updateTableInterval);
        }
    });
});

