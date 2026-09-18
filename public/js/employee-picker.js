/**
 * Employee picker — a Select2 (AJAX) wrapper shared by every department-user
 * page that asks for an employee (Requested by / PAR to).
 *
 * The <select> keeps the same value format the backend has always stored:
 *   value = "FULL NAME : DEPARTMENT"   label = "FULL NAME"
 * so nothing downstream (MRS, PA, reports) has to change.
 *
 * Usage:
 *   EmployeePicker.setup("{{ route('users.employee_search') }}");
 *   EmployeePicker.init('#requested_by');                       // blank when empty
 *   EmployeePicker.init('.par_to', { emptyValue: 'N/A' });      // "N/A" when empty
 *   EmployeePicker.setValue('#requested_by', 'JUAN DELA CRUZ : ENGINEERING');
 *   EmployeePicker.setReadonly('#requested_by', true);
 */
(function ($) {
    'use strict';

    var ENDPOINT = null;
    var PLACEHOLDER = 'Search employee…';

    function nameOf(value) {
        return String(value || '').split(':')[0].trim();
    }

    function deptOf(value) {
        var parts = String(value || '').split(':');
        return parts.length > 1 ? parts.slice(1).join(':').trim() : '';
    }

    function escapeRegExp(s) {
        return s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }

    // Wrap every typed word in <mark> so the match is visible, Google-style.
    function highlight(text, term) {
        var $out = $('<span>').text(text);
        var tokens = String(term || '').trim().split(/\s+/).filter(Boolean);
        if (!tokens.length) return $out.html();
        var html = $out.html();
        var re = new RegExp('(' + tokens.map(escapeRegExp).join('|') + ')', 'ig');
        return html.replace(re, '<mark>$1</mark>');
    }

    function ensureOption($el, value, label) {
        if (value === null || value === undefined || value === '') return;
        var exists = $el.find('option').filter(function () { return this.value === value; }).length;
        if (!exists) {
            $el.append($('<option>', { value: value, text: label || nameOf(value) }));
        }
    }

    var EmployeePicker = {
        setup: function (url) {
            ENDPOINT = url;
        },

        /**
         * opts.emptyValue     – value submitted when nothing is chosen ("" or "N/A")
         * opts.placeholder    – placeholder text
         * opts.dropdownParent – container for the dropdown (auto: closest .modal)
         */
        init: function (selector, opts) {
            opts = opts || {};
            var emptyValue = opts.emptyValue !== undefined ? opts.emptyValue : '';

            $(selector).each(function () {
                var $el = $(this);
                if ($el.hasClass('select2-hidden-accessible')) return; // already initialised

                // The placeholder option doubles as the "nothing chosen" value.
                var $blank = $el.find('option').filter(function () { return this.value === emptyValue; });
                if (!$blank.length) {
                    $blank = $('<option>', { value: emptyValue, text: opts.placeholder || PLACEHOLDER });
                    $el.prepend($blank);
                }
                if (!$el.find('option:selected').length || $el.val() === null) {
                    $blank.prop('selected', true);
                }

                var $parent = opts.dropdownParent ? $(opts.dropdownParent) : $el.closest('.modal');
                if (!$parent.length) $parent = $(document.body);

                var lastTerm = '';

                $el.select2({
                    width: '100%',
                    placeholder: { id: emptyValue, text: opts.placeholder || PLACEHOLDER },
                    allowClear: true,
                    dropdownParent: $parent,
                    minimumInputLength: 0,
                    ajax: {
                        url: ENDPOINT,
                        dataType: 'json',
                        delay: 200,
                        cache: true,
                        data: function (params) {
                            lastTerm = params.term || '';
                            return { q: lastTerm, page: params.page || 1 };
                        },
                        processResults: function (data) {
                            return {
                                results: data.results || [],
                                pagination: { more: !!(data.pagination && data.pagination.more) }
                            };
                        }
                    },
                    escapeMarkup: function (m) { return m; },
                    templateResult: function (item) {
                        if (item.loading) {
                            return '<div class="emp-pick emp-pick-loading">Searching…</div>';
                        }
                        var dept = item.dept !== undefined ? item.dept : deptOf(item.id);
                        return '<div class="emp-pick">'
                            +   '<div class="emp-pick-name">' + highlight(item.text, lastTerm) + '</div>'
                            +   (dept ? '<div class="emp-pick-dept">' + highlight(dept, lastTerm) + '</div>' : '')
                            + '</div>';
                    },
                    templateSelection: function (item) {
                        if (!item.id || item.id === emptyValue) {
                            return $('<span class="emp-pick-placeholder">').text(opts.placeholder || PLACEHOLDER);
                        }
                        return $('<span>').text(nameOf(item.text || item.id));
                    },
                    language: {
                        noResults: function () { return 'No employee found'; },
                        searching: function () { return 'Searching…'; },
                        loadingMore: function () { return 'Loading more…'; },
                        errorLoading: function () { return 'Could not reach the employee list'; }
                    }
                });

                // The slim Select2 build has no dropdownCssClass/containerCssClass,
                // so the hooks for the picker's styling are added by hand.
                var inst = $el.data('select2');
                if (inst) {
                    if (inst.$container) inst.$container.addClass('emp-picker-container');
                    if (inst.$dropdown) inst.$dropdown.addClass('emp-picker-dropdown');
                }

                // Clearing must fall back to the empty value ("N/A" for PAR to),
                // not to "no option selected", so the field still submits.
                $el.on('select2:clear', function () {
                    $el.val(emptyValue).trigger('change.select2');
                });

                // Focus the search box as soon as the dropdown opens.
                $el.on('select2:open', function () {
                    var field = $parent.find('.emp-picker-dropdown .select2-search__field').get(0);
                    if (field) setTimeout(function () { field.focus(); }, 0);
                });
            });
        },

        setValue: function (selector, value, label) {
            $(selector).each(function () {
                var $el = $(this);
                if (value === null || value === undefined || value === '') {
                    var blank = $el.find('option').first().val();
                    $el.val(blank).trigger('change.select2');
                    return;
                }
                ensureOption($el, value, label);
                $el.val(value).trigger('change.select2');
            });
        },

        // Select2 has no readonly mode; a disabled <select> would not submit, so
        // the dropdown is just prevented from opening.
        setReadonly: function (selector, readonly) {
            $(selector).each(function () {
                var $el = $(this);
                $el.off('select2:opening.empReadonly');
                if (readonly) {
                    $el.on('select2:opening.empReadonly', function (e) { e.preventDefault(); });
                }
                $el.next('.select2').toggleClass('emp-picker-readonly', !!readonly);
            });
        },

        nameOf: nameOf,
        deptOf: deptOf
    };

    window.EmployeePicker = EmployeePicker;
})(jQuery);
