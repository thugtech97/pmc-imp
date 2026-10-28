<?php

namespace App\Http\Controllers\Ecommerce;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class WfsStatusController extends Controller
{
    /**
     * WFS connection indicator on the MRS and IMF lists (see _wfs-status.blade.php).
     * Cached briefly so every open list polling it does not each open a WFS
     * connection.
     */
    public function status()
    {
        $status = Cache::remember('wfs_status', 30, function () {
            try {
                $wfsHealthType = config('app.name');
                $wfsHealthToken = config('app.key');
                $result = require(base_path('api/wfs-health-api.php'));
            } catch (\Throwable $e) {
                Log::warning('WFS status check failed', ['error' => $e->getMessage()]);
                $result = ['connected' => false, 'accepted' => false];
            }
            $result['checked_at'] = now()->format('M d, h:i A');
            return $result;
        });

        return response()->json($status);
    }
}
