/**
 * Staying DRY
 */
function getContext(element_id) {
    var el, ctx = null;
    el = document.getElementById(element_id);
    if (el) ctx = el.getContext('2d');
    return ctx;
}