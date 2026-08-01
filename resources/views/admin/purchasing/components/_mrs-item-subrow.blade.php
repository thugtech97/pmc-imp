{{--
    Per-item detail strip shown under each MRS line (PAR To / Frequency / Date Needed /
    Purpose). Replaces the old colspan-juggling second row, so every PA-DP screen renders
    it the same way regardless of how many columns that screen shows.

    Expects: $details (SalesDetail), $itemCols (int column count), $held (bool).
--}}
<tr class="pa-subrow {{ $held ? 'row-held' : '' }}">
    <td colspan="{{ $itemCols }}">
        <div class="pa-subgrid">
            <div>
                <span class="sub-label">PAR To</span>
                <span class="sub-value {{ !$details->par_to ? 'empty' : '' }}">{{ $details->par_to ?: '—' }}</span>
            </div>
            <div>
                <span class="sub-label">Frequency</span>
                <span class="sub-value {{ !$details->frequency ? 'empty' : '' }}">{{ $details->frequency ?: '—' }}</span>
            </div>
            <div>
                <span class="sub-label">Date Needed</span>
                <span class="sub-value {{ !$details->date_needed ? 'empty' : '' }}">{{ $details->date_needed ? \Carbon\Carbon::parse($details->date_needed)->format('m/d/Y') : '—' }}</span>
            </div>
            <div>
                <span class="sub-label">Purpose</span>
                <span class="sub-value {{ !$details->purpose ? 'empty' : '' }}">{{ $details->purpose ?: '—' }}</span>
            </div>
        </div>
    </td>
</tr>
