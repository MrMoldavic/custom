$(document).on('change', '#sourcetypeid', function() {
    $.ajax({
        url: 'http://test-dolibarr.tousalamusique.com/custom/materiel/preinventaire/ajax/print_source_select.php?sourcetypeid=' + $('#sourcetypeid').val(),
        success: function(output) {
            $('#source_select_wrapper').html(output);
        }
    });
});