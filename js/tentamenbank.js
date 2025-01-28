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
});