/**
 * Click handler for the 'Wipe Everything' button.
 */
function wipe_everything() {
    if (confirm("Are you sure you want to remove all data?")) {
        $.ajax({
            url: 'ajax.php',
            type: 'post',
            dataType: 'json',
            data: {
                page: 'settings',
                action: 'wipe_all'
            },
            success: function(response) {
                if (response.errors && response.errors.length) {
                    for (var i = 0; i < response.errors.length; i++) {
                        addError(response.errors[i]);
                    }
                    return;
                } 
                if (response.response == true) {
                    addSuccess('Database wiped!');
                    return;
                }
                console.log(response);
                addWarning("Unknown response. Check the console for details.");
            }
        });
    }
}

$(function() {
    $('#btnWipeAll').click(wipe_everything);
});