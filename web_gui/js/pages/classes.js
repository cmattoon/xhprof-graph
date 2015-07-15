$(function() {
    $('.mr-toggle').click(function(e) {
        e.preventDefault();
        e.stopPropagation();

        var tr, ico, cmd, plus, minus;
        plus = 'uk-icon-plus-square-o';
        minus = 'uk-icon-minus-square-o';
        tr = $(this).parents('tr').next('tr.methods');
        ico = $(this).find('i');

        if (tr.length) {
            if (ico.hasClass(plus)) {
                // Expand
                ico.removeClass(plus).addClass(minus);
                tr.show();
            } else {
                // Collapse
                ico.removeClass(minus).addClass(plus);
                tr.hide();
            }
        }
    });
});