{{--
    Audit trail panel for department users (requestors).

    Usage:
        @include('theme.pages.customer._request-history', ['histories' => $sale->histories])

    Only entries flagged visible_to_requestor are shown, and each one is worded
    with its plain-language title — the stored statuses carry internal MCD
    jargon that requestor screens deliberately never print. For the same reason
    the raw status row and MCD's own stamp columns (*_at / *_by) are left out of
    the change table: the entry title and its "by … on …" line already say it.

    Styles live in public/css/request-history.css, NOT inline: the theme mounts
    Vue on #content and Vue strips <style> tags from its template.
--}}
@php
    $entries = collect($histories ?? [])->filter(function ($entry) {
        return $entry->visible_to_requestor;
    })->values();
    $title    = $title    ?? 'Request History';
    $subtitle = $subtitle ?? 'What has happened to this request so far';

    $requestorChanges = function ($entry) {
        return collect($entry->changes ?: [])->reject(function ($change) {
            $field = (string) ($change['field'] ?? '');
            return $field === 'status'
                || substr($field, -3) === '_at'
                || substr($field, -3) === '_by';
        })->values();
    };

    $icons = [
        'created'   => 'icon-plus-circle',
        'approved'  => 'icon-check-circle',
        'hold'      => 'icon-hand-paper',
        'revised'   => 'icon-pencil-alt',
        'cancelled' => 'icon-times-circle',
        'item'      => 'icon-list-ul',
    ];
@endphp

@once
<link rel="stylesheet" href="{{ asset('css/request-history.css') }}">
@endonce

<div class="rh-card">
    <div class="rh-head">
        <div class="rh-head-icon"><i class="icon-history"></i></div>
        <div>
            <h5>{{ $title }}</h5>
            <p>{{ $subtitle }}</p>
        </div>
        <span class="rh-count">{{ $entries->count() }} {{ \Illuminate\Support\Str::plural('update', $entries->count()) }}</span>
    </div>

    <div class="rh-body">
        @if ($entries->isEmpty())
            <p class="rh-empty"><i class="icon-line-clock"></i> No updates have been recorded for this request yet.</p>
        @else
            <ul class="rh-list">
                @foreach ($entries as $entry)
                    @php $changes = $requestorChanges($entry); @endphp
                    <li class="rh-item tone-{{ $entry->tone }}">
                        <span class="rh-dot"><i class="{{ $icons[$entry->tone] ?? 'icon-info-circle' }}"></i></span>

                        <div class="rh-title">
                            {{ $entry->requestor_label }}
                            @if ($entry->revision > 0)
                                <span class="rh-rev">Rev{{ $entry->revision }}</span>
                            @endif
                        </div>

                        <div class="rh-meta">
                            @if ($entry->actor_name)
                                <strong>{{ $entry->actor_role ?: $entry->actor_name }}</strong>
                                &middot;
                            @endif
                            @if ($entry->created_at)
                                {{ $entry->created_at->format('M d, Y h:i A') }}
                                &middot; {{ $entry->created_at->diffForHumans() }}
                            @endif
                        </div>

                        @if ($entry->remarks)
                            <div class="rh-remark"><i class="icon-comment"></i> {{ $entry->remarks }}</div>
                        @endif

                        @if ($changes->isNotEmpty())
                            <div class="rh-changes">
                                <table>
                                    <thead>
                                        <tr><th style="width:38%;">Field</th><th style="width:31%;">From</th><th style="width:31%;">To</th></tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($changes as $change)
                                            @php
                                                // Item rows are labelled "Item name — Field"; show the item as a
                                                // small caption over the field instead of one long string.
                                                $label = (string) ($change['label'] ?? $change['field'] ?? '');
                                                $item  = null;
                                                if (strpos($label, ' — ') !== false) {
                                                    list($item, $label) = explode(' — ', $label, 2);
                                                }
                                                $old = ($change['old'] ?? null) === null || $change['old'] === '' ? '—' : $change['old'];
                                                $new = ($change['new'] ?? null) === null || $change['new'] === '' ? '—' : $change['new'];
                                            @endphp
                                            <tr>
                                                <td class="field">
                                                    @if ($item)
                                                        <span class="item">{{ $item }}</span>
                                                    @endif
                                                    {{ $label }}
                                                </td>
                                                <td class="old">{{ $old }}</td>
                                                <td class="new">{{ $new }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
