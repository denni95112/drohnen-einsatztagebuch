function sortTable(columnIndex) {
    const table = document.getElementById("einsatzTabelle");
    const tbody = table.getElementsByTagName("tbody")[0];
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

        if (columnIndex === 2 || columnIndex === 3) {
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

