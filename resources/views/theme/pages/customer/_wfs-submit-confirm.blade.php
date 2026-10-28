{{-- Yes/No prompt before an MRS goes to WFS. Any link with class "wfs-submit"
     (rows are drawn by DataTables, hence the delegated handler) asks first,
     using its data-confirm-title / data-confirm-text, then follows its href.
     SweetAlert v1 (js/sweetalert.min.js) must be loaded on the page. --}}
<script>
    $(document).on('click', 'a.wfs-submit', function (e) {
        e.preventDefault();
        var href = $(this).attr('href');
        swal({
            title: $(this).data('confirm-title') || 'Submit for Approval',
            text: $(this).data('confirm-text') || 'Are you sure you want to submit this MRS to WFS for approval?',
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#2ecc71',
            confirmButtonText: 'Yes, submit!',
            cancelButtonText: 'No',
            closeOnConfirm: false,
            showLoaderOnConfirm: true
        },
        function (isConfirm) {
            if (isConfirm) {
                window.location.href = href;
            }
        });
    });
</script>
