{{-- WFS connection indicator, plus the error line for a failed approval-status
     poll (main.blade.php). Styles are inline because the theme mounts Vue on
     #content, which drops <style> tags; the script is _wfs-status-js, included
     in the page's pagejs section. --}}
<span id="wfsStatus" data-url="{{ route('wfs.status') }}" title="Checking the connection to WFS…"
      style="display:inline-flex;align-items:center;gap:7px;padding:4px 12px;border-radius:999px;font-size:12px;font-weight:600;line-height:1.4;background:#f1f3f4;color:#5f6368;white-space:nowrap;">
    <span class="wfs-status-dot" style="width:8px;height:8px;border-radius:50%;background:#9aa0a6;flex:none;"></span>
    <span class="wfs-status-text">Checking WFS…</span>
</span>
<div id="wfsPollError" role="alert"
     style="display:none;margin-top:8px;padding:8px 12px;border-radius:8px;background:#fce8e6;color:#b3261e;font-size:13px;line-height:1.45;text-align:left;"></div>
