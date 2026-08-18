@php
    // Badge family from the extension. Kept in step with the same mapping in the
    // page's JS, which builds these tiles after an upload without a round trip.
    $ext = strtolower(pathinfo($doc['name'], PATHINFO_EXTENSION));

    if ($ext === 'pdf') {
        $type = 'pdf';   $icon = 'fa-file-pdf';
    } elseif (in_array($ext, ['xls', 'xlsx'], true)) {
        $type = 'excel'; $icon = 'fa-file-excel';
    } elseif (in_array($ext, ['doc', 'docx'], true)) {
        $type = 'word';  $icon = 'fa-file-word';
    } elseif (in_array($ext, ['png', 'jpg', 'jpeg'], true)) {
        $type = 'image'; $icon = 'fa-file-image';
    } else {
        $type = 'file';  $icon = 'fa-file';
    }
@endphp
<div class="doc-tile" data-path="{{ $doc['path'] }}">
    <div class="doc-badge type-{{ $type }}">
        <i class="fa {{ $icon }}"></i>
        <span>{{ $ext ?: 'file' }}</span>
    </div>
    <div class="doc-meta">
        <a href="{{ asset('storage/' . $doc['path']) }}" target="_blank" title="{{ $doc['name'] }}">{{ $doc['name'] }}</a>
        <small>Click to open in a new tab</small>
    </div>
    @if ($canEditDocs)
        <div class="doc-actions">
            <button type="button" class="doc-btn doc-replace" title="Replace this file"><i class="fa fa-sync-alt"></i></button>
            <button type="button" class="doc-btn doc-delete" title="Remove this file"><i class="fa fa-trash"></i></button>
        </div>
    @endif
</div>
