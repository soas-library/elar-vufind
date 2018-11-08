/*SCB; Datatables settings*/
$(document).ready(function () {

    $('#example').DataTable( {
        "lengthMenu": [[5, 10, -1], [5, 10, "All"]]
    } );
    $('#example2').DataTable( {
        "lengthMenu": [[5, 10, -1], [5, 10, "All"]]
    } );
    $('#example3').DataTable( {
        "lengthMenu": [[5, 10, -1], [5, 10, "All"]]
    } );

});


$.extend( true, $.fn.dataTable.defaults, {
    "searching": false,
    "ordering": true
} );

