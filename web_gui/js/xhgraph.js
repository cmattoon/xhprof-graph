/**
 * Appends a UIKit Alert object to a standard location
 * in the header.
 * This is used by addWarning, addSuccess, addError
 * @param string message The message to display to the user.
 * @param string errorlevel One of 'success', 'warning' or 'error'
 */
function addAlert(message, errorlevel) {
    var allowed = {
        'success': 'uk-alert-success', 
        'warning': 'uk-alert-warning', 
        'error': 'uk-alert-danger'
    };
    var el = $('<div/>').addClass('uk-alert');
    var modifier = allowed['success'];
    if (allowed.hasOwnProperty(errorlevel)) {
        modifier = allowed[errorlevel];
    }
    el.addClass(modifier).text(message);
    el.append('<a href="" class="uk-alert-close uk-close"></a>');
    $('#divAlerts').append(el);
}

function addSuccess(message) { return addAlert(message, 'success'); }
function addWarning(message) { return addAlert(message, 'warning'); }
function addError(message) { return addAlert(message, 'error'); }