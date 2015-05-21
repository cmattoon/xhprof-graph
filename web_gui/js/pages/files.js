$(function() {
    $('#btnDelFiles').click(function(e) {
	var frm = $('#frmFiles');
	frm.attr('action', 'files.php');
	frm.attr('method', 'post');
	$('.inptAction').val('delete');
	frm.submit();
    });
    $('#chkToggleAll').change(function() {
	$('.file.checkbox').prop('checked', this.checked);
    });
});