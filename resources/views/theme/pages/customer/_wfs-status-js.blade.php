{{-- Drives the WFS indicator in _wfs-status: checks on load, then every minute.
     Looks #wfsStatus up on every paint since Vue may re-render #content. --}}
<script>
    (function () {
        var looks = {
            ok:      { bg: '#e6f4ea', fg: '#137333', dot: '#1e8e3e' },
            bad:     { bg: '#fce8e6', fg: '#b3261e', dot: '#d93025' },
            unknown: { bg: '#f1f3f4', fg: '#5f6368', dot: '#9aa0a6' }
        };

        function paint(look, text, title) {
            var el = document.getElementById('wfsStatus');
            if (!el) {
                return;
            }
            el.style.background = looks[look].bg;
            el.style.color = looks[look].fg;
            el.querySelector('.wfs-status-dot').style.background = looks[look].dot;
            el.querySelector('.wfs-status-text').textContent = text;
            el.title = title;
        }

        function check() {
            var el = document.getElementById('wfsStatus');
            if (!el) {
                return;
            }
            $.ajax({ url: el.getAttribute('data-url'), dataType: 'json', timeout: 30000 })
                .done(function (r) {
                    var checked = 'Last checked ' + r.checked_at;
                    if (r.connected && r.accepted) {
                        paint('ok', 'WFS connected', checked);
                    } else if (r.connected) {
                        paint('bad', 'WFS is not accepting submissions from IMP — contact IT',
                            'WFS is reachable but does not recognise this app (app key / token mismatch). ' + checked);
                    } else {
                        paint('bad', 'WFS not reachable — submissions will fail',
                            'IMP cannot connect to the WFS database. ' + checked);
                    }
                })
                .fail(function () {
                    paint('unknown', 'WFS status unknown', 'Could not check the WFS connection.');
                });
        }

        $(check);
        setInterval(check, 60000);

        // Failed approval-status polls. main.blade.php runs the poll before this
        // script loads, so it queues each failure in window.wfsPollErrors and
        // calls this if it exists; queued ones are shown once the page is ready.
        window.wfsShowPollErrors = function () {
            var box = document.getElementById('wfsPollError');
            if (!box) {
                return;
            }
            var seen = {}, reasons = [];
            (window.wfsPollErrors || []).forEach(function (m) {
                if (!seen[m]) {
                    seen[m] = true;
                    reasons.push(m);
                }
            });
            if (!reasons.length) {
                box.style.display = 'none';
                return;
            }
            box.textContent = 'Could not get the latest approval updates from WFS: ' + reasons.join(' ')
                + ' Statuses shown may be out of date.';
            box.style.display = 'block';
        };
        $(window.wfsShowPollErrors);
    })();
</script>
