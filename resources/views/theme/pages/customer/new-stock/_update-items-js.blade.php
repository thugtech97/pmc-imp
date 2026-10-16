{{-- Script for _update-items. Exposes window.ImfUpdateGrid:
       .enable(on)   include / exclude the grid's inputs from the form post
       .validate()   null when the rows can be saved, otherwise the message to show
     Pass $updateRows (rows already on the IMF, edit page) and $exceptImf. --}}
<script>
window.ImfUpdateGrid = (function ($) {
    var SEARCH_URL = "{{ route('products.search') }}";
    var EXCEPT_IMF = @json($exceptImf ?? null);
    var FREQ = ['Daily', 'Weekly', 'Monthly', 'Yearly', 'As Needed'];
    var FIELDS = ['item_description', 'brand', 'OEM_ID', 'UoM', 'usage_rate_qty', 'usage_frequency', 'min_qty', 'max_qty', 'purpose'];
    var REQUIRED = ['item_description', 'UoM', 'usage_rate_qty', 'usage_frequency', 'min_qty', 'max_qty'];
    var $body = $('#updItems tbody');

    function input(name, type) {
        return $('<input class="form-control form-input">').attr({ name: name + '[]', type: type || 'text' }).attr('data-field', name);
    }

    function cell($el) { return $('<td>').append($el); }

    function setHelp($tr, text, ok) {
        $tr.find('.upd-help').text(text || '').toggleClass('ok', !!ok).toggleClass('bad', !ok);
    }

    function fill($tr, values) {
        FIELDS.forEach(function (f) {
            var v = values[f];
            $tr.find('[data-field="' + f + '"]').val(v === null || v === undefined ? '' : v);
        });
    }

    function same(a, b) {
        a = (a === null || a === undefined) ? '' : String(a).trim();
        b = (b === null || b === undefined) ? '' : String(b).trim();
        if (a !== '' && b !== '' && !isNaN(a) && !isNaN(b)) { return parseFloat(a) === parseFloat(b); }
        return a === b;
    }

    function markChanged($tr) {
        var orig = $tr.data('orig');
        FIELDS.forEach(function (f) {
            var $el = $tr.find('[data-field="' + f + '"]');
            $el.toggleClass('upd-changed', !!orig && !same($el.val(), orig[f]));
        });
    }

    /**
     * row: { item_id, stock_code, values, original } — omit for a blank row.
     */
    function addRow(row) {
        row = row || {};
        var $tr = $('<tr>').attr('data-valid', row.item_id ? '1' : '0');

        var opts = '<option value="">Select</option>' + FREQ.map(function (f) { return '<option value="' + f + '">' + f + '</option>'; }).join('');

        var $code = $('<input class="form-control form-input upd-code" name="stock_code[]" placeholder="Stock code" autocomplete="off">');
        var $codeCell = $('<td class="code-cell">')
            .append($('<input type="hidden" name="item_id[]">').val(row.item_id || ''))
            .append($code)
            .append('<small class="upd-help"></small>');

        $tr.append('<td class="item-no-cell"></td>')
            .append($codeCell)
            .append(cell($('<textarea rows="2" class="form-control form-input" name="item_description[]" data-field="item_description"></textarea>')))
            .append(cell(input('brand')))
            .append(cell(input('OEM_ID')))
            .append(cell(input('UoM')))
            .append(cell(input('usage_rate_qty', 'number')))
            .append(cell($('<select class="form-select" name="usage_frequency[]" data-field="usage_frequency">').html(opts)))
            .append(cell(input('min_qty', 'number')))
            .append(cell(input('max_qty', 'number')))
            .append(cell(input('purpose')))
            .append(cell($('<input type="file" name="attachment[]" class="form-control-file">')))
            .append('<td class="text-center"><i class="icon-trash grid-del" title="Remove"></i></td>');

        if (row.item_id) {
            // Already on the IMF: the item is fixed. To change it, remove the row and add another.
            $code.val(row.stock_code).prop('readonly', true).attr('title', 'Remove this row and add a new one to update a different item.');
            $tr.data('orig', row.original || null);
            fill($tr, row.values || {});
            markChanged($tr);
        }

        $body.append($tr);
        return $tr;
    }

    function lookup($tr) {
        var code = ($tr.find('.upd-code').val() || '').trim();
        $tr.attr('data-valid', '0').removeData('orig');
        markChanged($tr);

        if (code === '') { setHelp($tr, ''); return; }

        var dup = $body.find('tr').not($tr).filter(function () {
            return ($(this).find('.upd-code').val() || '').trim().toUpperCase() === code.toUpperCase();
        }).length;
        if (dup) { setHelp($tr, 'Already listed in another row.', false); return; }

        setHelp($tr, 'Looking up...', true);
        $.ajax({
            url: SEARCH_URL,
            type: 'POST',
            data: { code: code, except_imf: EXCEPT_IMF },
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (response) {
                // The code may have been edited again while this was in flight.
                if (($tr.find('.upd-code').val() || '').trim() !== code) { return; }

                if (response.status === 'success' && response.valid == 1) {
                    $tr.attr('data-valid', '1').data('orig', response.data);
                    fill($tr, response.data);
                    markChanged($tr);
                    setHelp($tr, 'Item found', true);
                    $tr.removeClass('table-danger');
                } else if (response.status === 'success') {
                    setHelp($tr, 'Already on IMF #' + response.open_imf + ', still in process.', false);
                } else {
                    setHelp($tr, 'Stock code not found.', false);
                }
            },
            error: function () { setHelp($tr, 'Lookup failed. Please try again.', false); }
        });
    }

    $body.on('input', '.upd-code', function () {
        var $tr = $(this).closest('tr');
        clearTimeout($tr.data('timer'));
        $tr.data('timer', setTimeout(function () { lookup($tr); }, 450));
    });

    $body.on('input change', 'input, select, textarea', function () {
        if (!$(this).hasClass('upd-code')) { markChanged($(this).closest('tr')); }
    });

    $body.on('click', '.grid-del', function () {
        var $tr = $(this).closest('tr');
        if ($body.find('tr').length > 1) { $tr.remove(); return; }
        // Keep at least one row — just clear it.
        $tr.find('input[type=hidden]').val('');
        $tr.find('.upd-code').val('').prop('readonly', false);
        $tr.find('input, select, textarea').not('[type=hidden]').val('');
        $tr.attr('data-valid', '0').removeData('orig').removeClass('table-danger');
        setHelp($tr, '');
        markChanged($tr);
    });

    $('#updAddRow').on('click', function () { addRow().find('.upd-code').trigger('focus'); });

    var existing = @json($updateRows ?? []);
    if (existing.length) { existing.forEach(addRow); } else { addRow(); }

    return {
        enable: function (on) {
            $('#updItems').find('input, select, textarea').prop('disabled', !on);
        },

        validate: function () {
            var filled = 0, message = null;

            $body.find('tr').each(function () {
                var $tr = $(this);
                var code = ($tr.find('.upd-code').val() || '').trim();
                var anyValue = FIELDS.some(function (f) { return ($tr.find('[data-field="' + f + '"]').val() || '').toString().trim() !== ''; });

                if (code === '' && !anyValue) {
                    // Blank row: ignored (the server drops it too), unless it is the only one.
                    $tr.removeClass('table-danger');
                    return;
                }
                filled++;

                var bad = null;
                if (code === '') {
                    bad = 'Enter the stock code of every item to update.';
                } else if ($tr.attr('data-valid') !== '1') {
                    bad = 'Stock code ' + code + ' is not valid: ' + ($tr.find('.upd-help').text() || 'look it up first.');
                } else if (REQUIRED.some(function (f) { return ($tr.find('[data-field="' + f + '"]').val() || '').toString().trim() === ''; })) {
                    bad = 'Please complete all required fields in the highlighted rows.';
                }

                $tr.toggleClass('table-danger', !!bad);
                if (bad && !message) { message = bad; }
            });

            if (!filled) { return 'Add at least one item to update.'; }
            return message;
        }
    };
})(jQuery);
</script>
