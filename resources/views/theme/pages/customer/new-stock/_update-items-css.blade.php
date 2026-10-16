{{-- Styles for _update-items. Loaded in pagecss: the theme mounts Vue on #content, which drops <style> tags inside the page body. --}}
<style>
    #updItems { margin:0; min-width:1300px; }
    #updItems thead th { background:#f7f8fa; font-size:11px; letter-spacing:.3px; text-transform:uppercase; color:#6c757d; font-weight:700; border-bottom:1px solid #eef0f2; padding:10px 8px; white-space:nowrap; }
    #updItems td { padding:6px; vertical-align:top; border-top:1px solid #f1f3f5; }
    #updItems .form-control, #updItems .form-select { font-size:13px; padding:6px 8px; min-width:90px; }
    #updItems textarea.form-control { min-width:200px; }
    #updItems td.item-no-cell { text-align:center; font-weight:700; color:#adb5bd; width:44px; min-width:44px; padding-top:12px; }
    #updItems td.item-no-cell::before { content: counter(updno); }
    #updItems tbody { counter-reset: updno; }
    #updItems tbody tr { counter-increment: updno; }
    #updItems td.code-cell { min-width:150px; }
    #updItems .upd-code[readonly] { background:#f7f8fa; font-weight:600; }
    #updItems .upd-help { display:block; font-size:11.5px; margin-top:3px; line-height:1.3; }
    #updItems .upd-help.ok { color:#2f7d52; }
    #updItems .upd-help.bad { color:#aa0707; }
    #updItems .upd-changed, .upd-legend { background:#fff8e1 !important; border-color:#f0b429 !important; }
    .upd-legend { display:inline-block; width:12px; height:12px; border:1px solid; border-radius:3px; vertical-align:-1px; margin-right:4px; }
    #updItems .grid-del { color:#f34237; cursor:pointer; font-size:16px; display:inline-block; padding-top:8px; }
</style>
