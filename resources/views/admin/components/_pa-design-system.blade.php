{{--
    Shared "PA" design system.

    Originally inline in admin/purchasing/planner_pa_view.blade.php (the PA SR screen);
    extracted so the PA-DP / MRS screens (MCD Planner / Verifier / Approver, Purchasing
    Officer and Canvasser) render identically. Include it from @section('pagecss'):

        @include('admin.components._pa-design-system')

    Add only view-specific rules on top of this — anything that should look the same
    everywhere belongs in here.
--}}
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
    :root {
        --pa-bg: #f4f6f9; --pa-white: #ffffff; --pa-border: #e2e8f0;
        --pa-border-dark: #cbd5e1; --pa-text: #1e293b; --pa-text-muted: #64748b;
        --pa-text-light: #94a3b8; --pa-primary: #1d4ed8; --pa-primary-light: #eff6ff;
        --pa-primary-hover: #1e40af; --pa-success: #059669; --pa-success-light: #ecfdf5;
        --pa-warning: #d97706; --pa-warning-light: #fffbeb; --pa-danger: #dc2626;
        --pa-danger-light: #fef2f2; --pa-shadow-sm: 0 1px 3px rgba(0,0,0,0.06);
        --pa-radius: 10px; --pa-radius-sm: 6px;
    }
    body { font-family: 'DM Sans', sans-serif; background: var(--pa-bg); }

    /* ---- Page header ---- */
    .pa-page-header { margin-bottom: 28px; }
    .pa-page-header .breadcrumb { background: none; padding: 0; margin-bottom: 6px; font-size: 12px; }
    .pa-page-header .breadcrumb-item a { color: var(--pa-text-muted); text-decoration: none; }
    .pa-page-header .breadcrumb-item.active { color: var(--pa-primary); font-weight: 500; }
    .pa-page-header h4 { font-size: 22px; font-weight: 600; color: var(--pa-text); letter-spacing: -0.4px; margin: 0; }
    .pa-page-header .pa-subtitle { font-size: 12px; color: var(--pa-text-muted); margin-top: 4px; }
    .pa-rev-badge { display: inline-block; vertical-align: middle; background: #f6931d; color: #fff; font-size: 11px; font-weight: 700; padding: 2px 9px; border-radius: 10px; margin-left: 8px; }

    /* ---- Status badge ---- */
    .pa-status-badge { display: inline-flex; align-items: center; gap: 6px; padding: 5px 12px; border-radius: 20px; font-size: 11.5px; font-weight: 600; text-align: center; }
    .pa-status-badge.status-pending   { background: var(--pa-warning-light); color: var(--pa-warning); border: 1px solid #fde68a; }
    .pa-status-badge.status-approved  { background: var(--pa-success-light); color: var(--pa-success); border: 1px solid #a7f3d0; }
    .pa-status-badge.status-cancelled { background: var(--pa-danger-light);  color: var(--pa-danger);  border: 1px solid #fecaca; }
    .pa-status-badge.status-default   { background: var(--pa-primary-light); color: var(--pa-primary); border: 1px solid #bfdbfe; }

    /* ---- Cards ---- */
    .pa-card { background: var(--pa-white); border: 1px solid var(--pa-border); border-radius: var(--pa-radius); box-shadow: var(--pa-shadow-sm); margin-bottom: 20px; overflow: hidden; }
    .pa-card-header { padding: 16px 22px; border-bottom: 1px solid var(--pa-border); display: flex; align-items: center; gap: 10px; background: #fafbfd; }
    .pa-card-header .card-icon { width: 32px; height: 32px; border-radius: 8px; background: var(--pa-primary-light); display: flex; align-items: center; justify-content: center; color: var(--pa-primary); font-size: 14px; flex-shrink: 0; }
    .pa-card-header h6 { margin: 0; font-size: 13px; font-weight: 600; color: var(--pa-text); }
    .pa-card-header p  { margin: 0; font-size: 11.5px; color: var(--pa-text-muted); }
    .pa-card-body { padding: 22px; }

    .pa-number-display { display: flex; align-items: center; gap: 10px; padding: 10px 14px; background: var(--pa-primary-light); border: 1px solid #bfdbfe; border-radius: var(--pa-radius-sm); }
    .pa-number-display .pa-number-icon { color: var(--pa-primary); font-size: 16px; }
    .pa-number-display .pa-number-value { font-family: 'DM Mono', monospace; font-size: 14px; font-weight: 500; color: var(--pa-primary); }

    .pa-meta-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 16px; }
    .pa-meta-item .meta-label { font-size: 10.5px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; color: var(--pa-text-light); margin-bottom: 4px; }
    .pa-meta-item .meta-value { font-size: 13px; font-weight: 500; color: var(--pa-text); word-break: break-word; }
    .pa-meta-item .meta-value.empty { color: var(--pa-text-light); font-style: italic; font-weight: 400; }

    /* ---- Form controls ---- */
    .pa-label { font-size: 12px; font-weight: 600; color: var(--pa-text); letter-spacing: 0.3px; text-transform: uppercase; margin-bottom: 7px; display: block; }
    .pa-textarea { width: 100%; padding: 10px 12px; border: 1px solid var(--pa-border-dark); border-radius: var(--pa-radius-sm); font-family: 'DM Sans', sans-serif; font-size: 13.5px; color: var(--pa-text); background: var(--pa-white); resize: vertical; min-height: 90px; outline: none; transition: border-color 0.15s; }
    .pa-textarea:focus { border-color: var(--pa-primary); box-shadow: 0 0 0 3px rgba(29,78,216,0.1); }
    .pa-textarea[readonly], .pa-textarea:disabled { background: #f8fafc; color: var(--pa-text-muted); }

    .pa-select { width: 100%; height: 38px; padding: 0 12px; border: 1px solid var(--pa-border-dark); border-radius: var(--pa-radius-sm); font-family: 'DM Sans', sans-serif; font-size: 13.5px; color: var(--pa-text); background: var(--pa-white); outline: none; }
    .pa-select:focus { border-color: var(--pa-primary); box-shadow: 0 0 0 3px rgba(29,78,216,0.1); }

    /* ---- Table ---- */
    .pa-table-wrapper { overflow: auto; }
    .pa-table { width: 100%; border-collapse: collapse; font-size: 12.5px; margin: 0; }
    .pa-table thead tr { background: #f1f5f9; }
    .pa-table thead th { padding: 10px 12px; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; color: var(--pa-text-muted); border-bottom: 1px solid var(--pa-border); border-right: 1px solid var(--pa-border); white-space: nowrap; text-align: left; }
    .pa-table thead th:last-child { border-right: none; }
    .pa-table thead th.purchaser-col { background: #fefce8; color: var(--pa-warning); }
    .pa-table tbody tr { border-bottom: 1px solid var(--pa-border); transition: background 0.1s; }
    .pa-table tbody tr:hover { background: #fafbff; }
    .pa-table tbody td { padding: 8px 10px; color: var(--pa-text); border-right: 1px solid var(--pa-border); vertical-align: middle; }
    .pa-table tbody td:last-child { border-right: none; }
    .pa-table tbody td.purchaser-col { background: #fefce8; }
    .pa-table tbody tr.row-held > td { background: #f8fafc; color: var(--pa-text-muted); }
    .pa-table tbody tr.row-held:hover > td { background: #f1f5f9; }
    .pa-table .mono { font-family: 'DM Mono', monospace; font-size: 12px; }

    .row-num { display: inline-flex; align-items: center; justify-content: center; width: 22px; height: 22px; background: var(--pa-primary-light); color: var(--pa-primary); border-radius: 50%; font-size: 11px; font-weight: 600; }

    .pa-table .form-control { height: 32px; padding: 0 8px; font-size: 12.5px; border: 1px solid var(--pa-border-dark); border-radius: 4px; font-family: 'DM Sans', sans-serif; min-width: 80px; }
    .pa-table .form-control:focus { border-color: var(--pa-primary); box-shadow: 0 0 0 2px rgba(29,78,216,0.1); outline: none; }
    .pa-table .form-control[readonly], .pa-table .form-control:disabled { background: #f8fafc; color: var(--pa-text-muted); cursor: default; }

    /* Detail strip under each item row (PAR To / Frequency / Date Needed / Purpose). */
    .pa-table tbody tr.pa-subrow { border-bottom: 2px solid var(--pa-border); }
    .pa-table tbody tr.pa-subrow:hover { background: transparent; }
    .pa-table tbody tr.pa-subrow > td { background: #fbfcfe; padding: 10px 12px 12px 42px; }
    .pa-subgrid { display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: 10px 22px; }
    .pa-subgrid .sub-label { display: block; font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; color: var(--pa-text-light); margin-bottom: 2px; }
    .pa-subgrid .sub-value { font-size: 12.5px; color: var(--pa-text); }
    .pa-subgrid .sub-value.empty { color: var(--pa-text-light); font-style: italic; }

    /* ---- Attachments ---- */
    .doc-list { list-style: none; padding: 0; margin: 0; display: flex; flex-wrap: wrap; gap: 8px; }
    .doc-list li a { display: inline-flex; align-items: center; gap: 7px; padding: 7px 14px; background: var(--pa-primary-light); border: 1px solid #bfdbfe; border-radius: var(--pa-radius-sm); color: var(--pa-primary); font-size: 12.5px; font-weight: 500; text-decoration: none; transition: all 0.15s; }
    .doc-list li a:hover { background: #dbeafe; }

    /* ---- Notice banner ---- */
    .pa-notice { display: flex; align-items: flex-start; gap: 12px; padding: 14px 18px; border-radius: var(--pa-radius); margin-bottom: 20px; border: 1px solid; }
    .pa-notice.notice-warning { background: var(--pa-warning-light); border-color: #fde68a; border-left: 5px solid var(--pa-warning); }
    .pa-notice .notice-icon { font-size: 20px; color: var(--pa-warning); margin-top: 1px; }
    .pa-notice .notice-title { font-size: 12.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.3px; color: var(--pa-text); }
    .pa-notice .notice-body { font-size: 13px; color: var(--pa-text); margin-top: 2px; }

    /* ---- Action bar + buttons ---- */
    .pa-action-bar { background: var(--pa-white); border: 1px solid var(--pa-border); border-radius: var(--pa-radius); padding: 16px 22px; display: flex; align-items: center; gap: 10px; flex-wrap: wrap; box-shadow: var(--pa-shadow-sm); }
    .pa-action-bar .spacer { margin-left: auto; }

    .btn-pa { display: inline-flex; align-items: center; gap: 7px; padding: 9px 18px; border-radius: var(--pa-radius-sm); font-family: 'DM Sans', sans-serif; font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.15s; text-decoration: none; border: none; }
    .btn-pa:hover { transform: translateY(-1px); text-decoration: none; }
    .btn-pa:disabled, .btn-pa.disabled { opacity: 0.55; cursor: not-allowed; transform: none; }
    .btn-pa-primary   { background: var(--pa-primary); color: white; }
    .btn-pa-primary:hover { background: var(--pa-primary-hover); color: white; box-shadow: 0 4px 12px rgba(29,78,216,0.25); }
    .btn-pa-success   { background: var(--pa-success); color: white; }
    .btn-pa-success:hover { background: #047857; color: white; box-shadow: 0 4px 12px rgba(5,150,105,0.25); }
    .btn-pa-danger    { background: var(--pa-danger); color: white; }
    .btn-pa-danger:hover { background: #b91c1c; color: white; }
    .btn-pa-secondary { background: var(--pa-white); color: var(--pa-text-muted); border: 1px solid var(--pa-border-dark); }
    .btn-pa-secondary:hover { background: #f8fafc; color: var(--pa-text); }
    .btn-pa-warning { background: var(--pa-warning); color: white; }
    .btn-pa-warning:hover { background: #b45309; color: white; }
    .btn-pa-info { background: #0891b2; color: white; }
    .btn-pa-info:hover { background: #0e7490; color: white; }
    .btn-done { display: inline-flex; align-items: center; gap: 7px; padding: 9px 18px; border-radius: var(--pa-radius-sm); font-family: 'DM Sans', sans-serif; font-size: 13px; font-weight: 600; background: var(--pa-success-light); color: var(--pa-success); border: 1px solid #a7f3d0; cursor: default; }
    .pa-action-note { font-size: 11.5px; color: var(--pa-danger); font-weight: 600; }

    /* ---- Hold toggle (kept from the legacy MRS screens, restyled) ---- */
    .switch { position: relative; display: inline-block; width: 38px; height: 22px; margin: 0; }
    .switch input { opacity: 0; width: 0; height: 0; }
    .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: var(--pa-border-dark); transition: .3s; }
    .slider:before { position: absolute; content: ""; height: 16px; width: 16px; left: 3px; bottom: 3px; background-color: #fff; transition: .3s; box-shadow: 0 1px 2px rgba(0,0,0,.2); }
    input:checked + .slider { background-color: var(--pa-danger); }
    input:focus + .slider { box-shadow: 0 0 0 3px rgba(220,38,38,.15); }
    input:checked + .slider:before { transform: translateX(16px); }
    input:disabled + .slider { opacity: .5; cursor: not-allowed; }
    .slider.round { border-radius: 34px; }
    .slider.round:before { border-radius: 50%; }

    /* Strip the browser spinners so numeric cells line up with the text inputs. */
    .pa-table input[type="number"]::-webkit-inner-spin-button,
    .pa-table input[type="number"]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
    .pa-table input[type="number"] { -moz-appearance: textfield; }

    /* ---- Approver cards (WFS trail) ---- */
    .pa-approver { border: 1px solid var(--pa-border); border-radius: var(--pa-radius-sm); overflow: hidden; height: 100%; }
    .pa-approver .ap-body { padding: 12px 14px; background: var(--pa-white); }
    .pa-approver.is-current .ap-body { background: var(--pa-primary-light); }
    .pa-approver .ap-name { font-size: 12.5px; font-weight: 600; color: var(--pa-text); margin: 0 0 3px; }
    .pa-approver .ap-meta { font-size: 11px; color: var(--pa-text-muted); }
    .pa-approver .ap-status { display: block; text-align: center; font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; padding: 6px; color: #fff; background: var(--pa-text-light); }
    .pa-approver .ap-status.is-approved { background: var(--pa-success); }
</style>
