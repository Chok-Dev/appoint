/*
 *  Document   : datatables.js
 *  Author     : pixelcave
 *  Description: Using custom JS code to init DataTables plugin
 */

// DataTables, for more examples you can check out https://www.datatables.net/
class pageTablesDatatables {
	/*
	 * Init DataTables functionality
	 *
	 */
	static initDataTables() {
		// Override a few default classes
    jQuery.extend(true, DataTable.ext.classes, {
      search: {
        input: "form-control"
      },
      length: {
        select: "form-select"
      },
    });

    // Override a few defaults
    jQuery.extend(true, DataTable.defaults, {
      language: {
        lengthMenu: "_MENU_",
        search: "_INPUT_",
        searchPlaceholder: "ค้นหา..",
        info: "หน้า <strong>_PAGE_</strong> จาก <strong>_PAGES_</strong>",
        paginate: {
          first: '<i class="fa fa-angle-double-left"></i>',
          previous: '<i class="fa fa-angle-left"></i>',
          next: '<i class="fa fa-angle-right"></i>',
          last: '<i class="fa fa-angle-double-right"></i>'
        }
      }
    });

    // Override buttons default classes
    jQuery.extend(true, DataTable.Buttons.defaults, {
      dom: {
        button: {
          className: 'btn btn-sm btn-primary'
        },
      }
    });

		// Init full DataTable
    jQuery('.js-dataTable-full').DataTable({
      pagingType: "simple_numbers",
      layout: {
        topStart: {
          pageLength: {
            menu: [5, 10, 15, 20]
          },
        },
      },
      pageLength: 5,
      autoWidth: false,
    });

    // Init DataTable with Buttons
    jQuery('.js-dataTable-buttons').DataTable({
      pagingType: "simple_numbers",
      layout: {
        topStart: {
          buttons: ['copy', 'excel', 'csv', 'pdf', 'print']
        },
      },
      pageLength: 5,
      autoWidth: false,
    });
	}

	/*
	 * Init functionality
	 *
	 */
	static init() {
		this.initDataTables();
	}
}

// Initialize when page loads
Codebase.onLoad(() => pageTablesDatatables.init());
