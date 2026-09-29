<?php

include(__DIR__ . '/config.php');

// Can IMP reach WFS, and will WFS accept its submissions? Both matter: WFS
// can be up while rejecting IMP because the token in allowed_transactions no
// longer matches this app's key. Reads $wfsHealthType / $wfsHealthToken from
// the caller. Returns ['connected' => bool, 'accepted' => bool]; never echoes.
if (!$conn) {
    return ['connected' => false, 'accepted' => false];
}

$wfsHealthQuery = sqlsrv_query($conn, "select token from allowed_transactions where name = ?", [$wfsHealthType]);
if (!$wfsHealthQuery) {
    return ['connected' => false, 'accepted' => false];
}
$wfsHealthRow = sqlsrv_fetch_array($wfsHealthQuery, SQLSRV_FETCH_ASSOC);

return [
    'connected' => true,
    'accepted'  => $wfsHealthRow && $wfsHealthRow['token'] == $wfsHealthToken,
];
