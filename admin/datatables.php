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

    // Collapsible add/edit forms: table first, form revealed on demand
    function syncAdminFormToggles() {
      document.querySelectorAll('.admin-form-toggle').forEach(function (btn) {
        var panel = document.getElementById(btn.getAttribute('data-form-target') || '');
        if (!panel) return;
        var open = !panel.hasAttribute('hidden');
        var showLabel = btn.getAttribute('data-label-show') || 'Add';
        var hideLabel = btn.getAttribute('data-label-hide') || 'Hide form';
        var icon = open ? 'fa-xmark' : 'fa-plus';
        btn.innerHTML = '<i class="fa-solid ' + icon + '"></i> ' + (open ? hideLabel : showLabel);
        btn.setAttribute('aria-expanded', open ? 'true' : 'false');
      });
    }

    function setAdminFormOpen(panel, open) {
      if (!panel) return;
      if (open) {
        panel.removeAttribute('hidden');
        panel.classList.add('is-open');
        panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
        if (window.tinymce && tinymce.get('blog-content-editor')) {
          setTimeout(function () {
            try {
              var ed = tinymce.get('blog-content-editor');
              if (ed) { ed.fire('ResizeEditor'); }
            } catch (e) {}
          }, 80);
        }
      } else {
        panel.setAttribute('hidden', '');
        panel.classList.remove('is-open');
      }
      syncAdminFormToggles();
    }

    document.querySelectorAll('.admin-form-toggle').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var panel = document.getElementById(btn.getAttribute('data-form-target') || '');
        if (!panel) return;
        setAdminFormOpen(panel, panel.hasAttribute('hidden'));
      });
    });

    document.querySelectorAll('.admin-form-close').forEach(function (btn) {
      btn.addEventListener('click', function (e) {
        var target = btn.getAttribute('data-form-target');
        if (!target) return;
        var panel = document.getElementById(target);
        if (!panel) return;
        // If this is a plain hide (not a navigation cancel), prevent default
        if (btn.tagName === 'BUTTON') {
          e.preventDefault();
          setAdminFormOpen(panel, false);
        }
      });
    });

    // Open form when landing on #form-anchor / edit hash
    if (window.location.hash) {
      var hashPanel = document.querySelector(window.location.hash);
      if (hashPanel && hashPanel.classList.contains('admin-form-panel')) {
        setAdminFormOpen(hashPanel, true);
      }
    }

    syncAdminFormToggles();
  })();
  </script>
    <?php
}
