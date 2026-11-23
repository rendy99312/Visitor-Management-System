
/*
==========================================
REPORT PAGE
==========================================
*/

const searchInput = document.getElementById("searchReport");
const reportTable = document.getElementById("reportTable");
const exportPdf = document.getElementById("exportPdf");

/*
==========================================
SEARCH REPORT
==========================================
*/

if (searchInput && reportTable) {

    searchInput.addEventListener("keyup", function () {

        const keyword = this.value.toLowerCase().trim();

        const rows = reportTable.querySelectorAll("tr");

        rows.forEach(function (row) {

            const text = row.innerText.toLowerCase();

            row.style.display = text.includes(keyword) ? "" : "none";

        });

    });

}

/*
==========================================
EXPORT PDF (PRINT)
==========================================
*/

if (exportPdf) {

    exportPdf.addEventListener("click", function () {

        window.print();

    });

}

/*
==========================================
PAGE READY
==========================================
*/

window.addEventListener("load", function () {

    console.log("Report Page Loaded");

});
