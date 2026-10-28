{{--
    Audit trail panel for department users (requestors).

    Usage:
        @include('theme.pages.customer._request-history', ['histories' => $sale->histories])

    Only entries flagged visible_to_requestor are shown, and each one is worded
    with its plain-language title — the stored statuses carry internal MCD
    jargon that requestor screens deliberately never print.
--}}
@php
    $entries = collect($histories ?? [])->filter(function ($entry) {
        return $entry->visible_to_requestor;
    });
    $title = $title ?? 'Request History';
@endphp

@once
<style>
    .rh-card { background:#fff; border:1px solid #e9ecf2; border-radius:12px; box-shadow:0 1px 3px rgba(16,24,40,.05); padding:22px 24px; margin-bottom:20px; }
    .rh-card h5 { font-size:14px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:#f6931d; margin:0 0 18px; }
    .rh-empty { color:#9aa0a6; font-size:13px; }

    .rh-list { list-style:none; margin:0; padding:0 0 0 24px; position:relative; }
    .rh-list::before { content:''; position:absolute; left:6px; top:6px; bottom:6px; width:2px; background:#eef0f3; }
    .rh-item { position:relative; padding-bottom:20px; }
    .rh-item:last-child { padding-bottom:0; }
    .rh-dot { position:absolute; left:-24px; top:2px; width:14px; height:14px; border-radius:50%; background:#9aa0a6; box-shadow:0 0 0 3px #fff; }
    .rh-item.tone-created  .rh-dot { background:#2563eb; }
    .rh-item.tone-approved .rh-dot { background:#2e9e5b; }
    .rh-item.tone-hold     .rh-dot { background:#f0a020; }
    .rh-item.tone-revised  .rh-dot { background:#7c3aed; }
    .rh-item.tone-cancelled .rh-dot { background:#dc3545; }
    .rh-item.tone-item     .rh-dot { background:#0891b2; }

    .rh-title { font-size:13.5px; font-weight:700; color:#1f2733; line-height:1.4; }
    .rh-meta { font-size:11.5px; color:#8a94a6; margin-top:2px; }
    .rh-remark { margin-top:7px; padding:8px 12px; background:#fff8e1; border-left:3px solid #ff9800; border-radius:0 6px 6px 0; font-size:12.5px; color:#5d4037; }
    .rh-changes { margin-top:8px; font-size:12px; color:#4b5563; }
    .rh-changes .row-line { padding:3px 0; border-top:1px solid #f3f4f6; }
    .rh-changes .row-line:first-child { border-top:0; }
    .rh-changes .lbl { color:#8a94a6; }
    .rh-changes .old { color:#9aa0a6; text-decoration:line-through; }
    .rh-changes .new { color:#1f2733; font-weight:600; }
</style>
@endonce

<div class="rh-card">
    <h5>{{ $title }}</h5>

    @if ($entries->isEmpty())
        <p class="rh-empty">No updates have been recorded for this request yet.</p>
    @else
        <ul class="rh-list">
            @foreach ($entries as $entry)
                <li class="rh-item tone-{{ $entry->tone }}">
                    <span class="rh-dot"></span>

                    <div class="rh-title">
                        {{ $entry->requestor_label }}
                        @if ($entry->revision > 0)
                            <span style="display:inline-block;background:#f6931d;color:#fff;font-size:10px;font-weight:700;padding:1px 8px;border-radius:10px;margin-left:6px;">Rev{{ $entry->revision }}</span>
                        @endif
                    </div>

                    <div class="rh-meta">
                        {{ $entry->created_at ? $entry->created_at->format('M d, Y h:i A') : '' }}
                        @if ($entry->actor_name)
                            &middot; by {{ $entry->actor_role ?: $entry->actor_name }}
                        @endif
                    </div>

                    @if ($entry->remarks)
                        <div class="rh-remark">{{ $entry->remarks }}</div>
                    @endif

                    @if (!empty($entry->changes))
                        <div class="rh-changes">
                            @foreach ($entry->changes as $change)
                                <div class="row-line">
                                    <span class="lbl">{{ $change['label'] ?? $change['field'] ?? '' }}:</span>
                                    <span class="old">{{ ($change['old'] ?? null) === null || $change['old'] === '' ? '—' : $change['old'] }}</span>
                                    &rarr;
                                    <span class="new">{{ ($change['new'] ?? null) === null || $change['new'] === '' ? '—' : $change['new'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </li>
            @endforeach
        </ul>
    @endif
</div>
