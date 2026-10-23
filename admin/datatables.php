<?php
/**
 * Shared DataTables assets for admin list tables.
 * Call adminDataTablesHead() in <head> and adminDataTablesFooter() before </body>.
 */
function adminDataTablesHead(): void {
    static $printed = false;
    if ($printed) {
        return;
    }
    $printed = true;
    echo '<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">' . "\n";
}

function adminDataTablesFooter(): void {
    static $printed = false;
    if ($printed) {
        return;
    }
    $printed = true;
    ?>
  <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
  <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
  <script>
  (function () {
    if (!window.jQuery || !jQuery.fn.DataTable) return;

    function actionColumnIndexes(table) {
      var targets = [];
      table.querySelectorAll('thead th').forEach(function (th, i) {
        var label = (th.textContent || '').trim().toLowerCase();
        if (th.classList.contains('no-sort') || label === 'action' || label === 'actions') {
          targets.push(i);
        }
      });
      return targets;
    }

    document.querySelectorAll('table.data-table').forEach(function (table) {
      if (jQuery.fn.DataTable.isDataTable(table)) return;
      if (!table.tBodies.length || !table.tBodies[0].rows.length) return;

      var pageLength = parseInt(table.getAttribute('data-page-length') || '10', 10);
      var orderAttr = table.getAttribute('data-order');
      var order = [];
      if (orderAttr) {
        try { order = JSON.parse(orderAttr); } catch (e) { order = []; }
      }

      jQuery(table).DataTable({
        order: order,
        pageLength: pageLength,
        lengthMenu: [5, 10, 25, 50, 100],
        autoWidth: false,
        columnDefs: [
          { orderable: false, targets: actionColumnIndexes(table) }
        ],
        language: {
          search: 'Search:',
          lengthMenu: 'Show _MENU_ rows',
          info: 'Showing _START_–_END_ of _TOTAL_',
          infoEmpty: 'No entries',
          zeroRecords: 'No matching records found',
          emptyTable: 'No data available'
        }
      });
    });
  })();
  </script>
    <?php
}
