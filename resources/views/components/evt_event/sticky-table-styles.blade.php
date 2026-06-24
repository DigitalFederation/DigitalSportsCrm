<style>
    table {
        position: relative;
    }

    table th {
        position: sticky;
        top: 0;
        z-index: 2;
    }

    table th:nth-last-child(1) {
        position: sticky;
        right: 0;
        z-index: 10;
        background: #f8fafc
    }

    table tbody tr td:nth-last-child(1) {
        position: sticky;
        right: 0;
        z-index: 1;
        background: white;
        background-clip: padding-box;
    }

    table th:nth-last-child(1),
    table tbody tr td:nth-last-child(1) {
        z-index: 10;
    }
</style>
