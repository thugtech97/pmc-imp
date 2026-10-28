{{--
    Audit trail panel for MCD / Admin screens (IMF, MRS, PA-DP, PA-SR).

    Usage:
        @include('admin.components._document-history', ['histories' => $sale->histories])
        @include('admin.components._document-history', [
            'histories' => $paHeader->histories,
            'title'     => 'PA History',
            'subtitle'  => 'Every change made to this purchase advice',
        ])

    Shows every entry, including the ones hidden from department users.
--}}
@php
    $histories = collect($histories ?? []);
    $title     = $title    ?? 'History Log';
    $subtitle  = $subtitle ?? 'Every change made to this request';
@endphp

{{-- A PA-DP screen includes this panel twice (its own trail + the source MRS's). --}}
@once
<style>
    .dh-panel { border: 1px solid #e5e7eb; border-radius: 12px; background: #fff; margin-bottom: 20px; overflow: hidden; }
    .dh-head { padding: 16px 22px; border-bottom: 1px solid #e5e7eb; display: flex; align-items: center; gap: 10px; background: #fafbfd; }
    .dh-head .dh-icon { width: 32px; height: 32px; border-radius: 8px; background: #eff6ff; color: #2563eb; display: flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0; }
    .dh-head h6 { margin: 0; font-size: 13px; font-weight: 600; color: #1f2937; }
    .dh-head p { margin: 0; font-size: 11.5px; color: #6b7280; }
    .dh-count { margin-left: auto; font-size: 11px; font-weight: 700; color: #6b7280; background: #f3f4f6; border-radius: 20px; padding: 3px 11px; }

    .dh-body { padding: 20px 22px; max-height: 560px; overflow-y: auto; }
    .dh-empty { padding: 26px; text-align: center; color: #9ca3af; font-size: 13px; }

    .dh-list { list-style: none; margin: 0; padding: 0 0 0 26px; position: relative; }
    .dh-list::before { content: ''; position: absolute; left: 7px; top: 6px; bottom: 6px; width: 2px; background: #e5e7eb; }
    .dh-item { position: relative; padding: 0 0 20px 0; }
    .dh-item:last-child { padding-bottom: 0; }
    .dh-dot { position: absolute; left: -26px; top: 1px; width: 16px; height: 16px; border-radius: 50%; background: #9ca3af; color: #fff; font-size: 8px; display: flex; align-items: center; justify-content: center; box-shadow: 0 0 0 3px #fff; }
    .dh-item.tone-created  .dh-dot { background: #2563eb; }
    .dh-item.tone-approved .dh-dot { background: #059669; }
    .dh-item.tone-hold     .dh-dot { background: #d97706; }
    .dh-item.tone-revised  .dh-dot { background: #7c3aed; }
    .dh-item.tone-cancelled .dh-dot { background: #dc2626; }
    .dh-item.tone-item     .dh-dot { background: #0891b2; }

    .dh-title { font-size: 13px; font-weight: 600; color: #111827; line-height: 1.4; }
    .dh-meta { font-size: 11.5px; color: #6b7280; margin-top: 2px; }
    .dh-meta strong { color: #374151; font-weight: 600; }
    .dh-private { display: inline-block; font-size: 9.5px; font-weight: 700; letter-spacing: .4px; text-transform: uppercase; color: #92400e; background: #fef3c7; border-radius: 4px; padding: 1px 6px; margin-left: 6px; vertical-align: middle; }

    .dh-remark { margin-top: 7px; padding: 8px 12px; background: #fffbeb; border-left: 3px solid #f59e0b; border-radius: 0 6px 6px 0; font-size: 12.5px; color: #78350f; }
    .dh-status { margin-top: 6px; font-size: 11.5px; color: #4b5563; }
    .dh-status .from { color: #9ca3af; text-decoration: line-through; }
    .dh-status .to { font-weight: 600; color: #111827; }

    .dh-changes { margin-top: 8px; border: 1px solid #eef0f3; border-radius: 8px; overflow: hidden; }
    .dh-changes table { width: 100%; border-collapse: collapse; font-size: 12px; }
    .dh-changes th { background: #f9fafb; color: #6b7280; font-weight: 600; text-align: left; padding: 6px 10px; font-size: 10.5px; text-transform: uppercase; letter-spacing: .4px; }
    .dh-changes td { padding: 6px 10px; border-top: 1px solid #f3f4f6; color: #374151; vertical-align: top; word-break: break-word; }
    .dh-changes td.old { color: #9ca3af; text-decoration: line-through; }
    .dh-changes td.new { color: #065f46; font-weight: 600; }
</style>
@endonce

<div class="dh-panel">
    <div class="dh-head">
        <div class="dh-icon"><i class="fa fa-history"></i></div>
        <div>
            <h6>{{ $title }}</h6>
            <p>{{ $subtitle }}</p>
        </div>
        <span class="dh-count">{{ $histories->count() }} {{ \Illuminate\Support\Str::plural('entry', $histories->count()) }}</span>
    </div>

    <div class="dh-body">
        @if ($histories->isEmpty())
            <div class="dh-empty"><i class="fa fa-clock-o"></i> &nbsp;No changes have been recorded for this request yet.</div>
        @else
            <ul class="dh-list">
                @foreach ($histories as $entry)
                    <li class="dh-item tone-{{ $entry->tone }}">
                        <span class="dh-dot"><i class="fa {{ $entry->icon }}"></i></span>

                        <div class="dh-title">
                            {{ $entry->title }}
                            @if ($entry->revision > 0)
                                <span style="display:inline-block;background:#f6931d;color:#fff;font-size:10px;font-weight:700;padding:1px 8px;border-radius:10px;margin-left:6px;">Rev{{ $entry->revision }}</span>
                            @endif
                            @unless ($entry->visible_to_requestor)
                                <span class="dh-private" title="Not shown to the department user">Internal</span>
                            @endunless
                        </div>

                        <div class="dh-meta">
                            <strong>{{ $entry->actor_label }}</strong>
                            &middot; {{ $entry->created_at ? $entry->created_at->format('M d, Y h:i A') : '' }}
                            @if ($entry->created_at)
                                &middot; {{ $entry->created_at->diffForHumans() }}
                            @endif
                        </div>

                        @if ($entry->status_from || $entry->status_to)
                            <div class="dh-status">
                                @if ($entry->status_from)
                                    <span class="from">{{ $entry->status_from }}</span> &rarr;
                                @endif
                                <span class="to">{{ $entry->status_to }}</span>
                            </div>
                        @endif

                        @if ($entry->remarks)
                            <div class="dh-remark"><i class="fa fa-comment-o"></i> {{ $entry->remarks }}</div>
                        @endif

                        @if (!empty($entry->changes))
                            <div class="dh-changes">
                                <table>
                                    <thead>
                                        <tr><th style="width:34%;">Field</th><th style="width:33%;">From</th><th style="width:33%;">To</th></tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($entry->changes as $change)
                                            <tr>
                                                <td>{{ $change['label'] ?? $change['field'] ?? '' }}</td>
                                                <td class="old">{{ ($change['old'] ?? null) === null || $change['old'] === '' ? '—' : $change['old'] }}</td>
                                                <td class="new">{{ ($change['new'] ?? null) === null || $change['new'] === '' ? '—' : $change['new'] }}</td>
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
