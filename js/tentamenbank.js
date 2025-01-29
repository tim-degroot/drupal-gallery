var currentPage = 1;
var rowsPerPage = 10;

function filterTable(level, button) {
    var table, rows, i, study;
    table = document.getElementById("subjects");
    rows = table.getElementsByTagName("tr");
    var searchFilter = document.getElementById('search').value.toLowerCase();
    
    for (i = 1; i < rows.length; i++) {
        study = rows[i].getElementsByTagName("td")[1];
        var subject = rows[i].getElementsByTagName("td")[0].textContent.toLowerCase();
        if (study) {
            if ((study.textContent === level || level === "") && subject.indexOf(searchFilter) > -1) {
                rows[i].style.display = "";
            } else {
                rows[i].style.display = "none";
            }
        }       
    }

    // Update button classes
    var buttons = document.querySelectorAll('.btn-group .btn');
    buttons.forEach(function(btn) {
        btn.classList.remove('btn-primary');
        btn.classList.add('btn-outline-primary');
    });
    button.classList.remove('btn-outline-primary');
    button.classList.add('btn-primary');

    updatePagination();
}

function updatePagination() {
    var table, rows, i;
    table = document.getElementById("subjects");
    rows = table.getElementsByTagName("tr");
    var totalRows = rows.length - 1; // excluding header row
    var totalPages = Math.ceil(totalRows / rowsPerPage);

    document.getElementById('pageInfo').textContent = `Page ${currentPage} of ${totalPages}`;

    for (i = 1; i < rows.length; i++) {
        if (i > (currentPage - 1) * rowsPerPage && i <= currentPage * rowsPerPage) {
            rows[i].style.display = "";
        } else {
            rows[i].style.display = "none";
        }
    }

    document.getElementById('prevPage').disabled = currentPage === 1;
    document.getElementById('nextPage').disabled = currentPage === totalPages;
}

function prevPage() {
    if (currentPage > 1) {
        currentPage--;
        updatePagination();
    }
}

function nextPage() {
    var table, rows;
    table = document.getElementById("subjects");
    rows = table.getElementsByTagName("tr");
    var totalRows = rows.length - 1; // excluding header row
    var totalPages = Math.ceil(totalRows / rowsPerPage);

    if (currentPage < totalPages) {
        currentPage++;
        updatePagination();
    }
}

document.getElementById('search').addEventListener('input', function() {
    var filter = this.value.toLowerCase();
    var rows = document.querySelectorAll('#subjects tbody tr');
    rows.forEach(function(row) {
        var subject = row.getElementsByTagName('td')[0].textContent.toLowerCase();
        if (subject.indexOf(filter) > -1) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
    currentPage = 1;
    updatePagination();
});

// Initial pagination setup
updatePagination();