<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * One entry in the audit trail of an MRS, PA-DP or PA-SR.
 *
 * Rows are never edited or deleted — the trail is append-only. See
 * App\Services\History for how they get written.
 */
class DocumentHistory extends Model
{
    protected $table = 'document_histories';

    /** created_at is stamped by hand; there is no updated_at on an append-only log. */
    public $timestamps = false;

    protected $fillable = [
        'document_type',
        'document_id',
        'document_number',
        'pa_type',
        'action',
        'title',
        'requestor_title',
        'description',
        'status_from',
        'status_to',
        'remarks',
        'changes',
        'revision',
        'actor_id',
        'actor_name',
        'actor_role',
        'visible_to_requestor',
        'created_at',
    ];

    protected $casts = [
        'changes'              => 'array',
        'visible_to_requestor' => 'boolean',
        'created_at'           => 'datetime',
    ];

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    /**
     * Only the entries a department user (requestor) is allowed to see.
     */
    public function scopeVisibleToRequestor($query)
    {
        return $query->where('visible_to_requestor', 1);
    }

    /**
     * "Juan Dela Cruz (MCD Planner)" — falls back to the linked user when the
     * snapshot is missing, and to "System" for unattended changes.
     */
    public function getActorLabelAttribute()
    {
        $name = $this->actor_name ?: optional($this->actor)->name;

        if (!$name) {
            return 'System';
        }

        return $this->actor_role ? $name . ' (' . $this->actor_role . ')' : $name;
    }

    /**
     * Wording for the requestor: the plain-language title when one was recorded,
     * otherwise the internal title.
     */
    public function getRequestorLabelAttribute()
    {
        return $this->requestor_title ?: $this->title;
    }

    /**
     * Coarse bucket used for the timeline's colour and icon.
     * One of: created, approved, hold, revised, cancelled, item, neutral.
     */
    public function getToneAttribute()
    {
        $action = strtolower((string) $this->action);

        if (in_array($action, ['created', 'submitted'], true)) {
            return 'created';
        }
        if (in_array($action, ['verified', 'approved', 'received', 'assigned', 'completed'], true)) {
            return 'approved';
        }
        if (in_array($action, ['held', 'returned'], true)) {
            return 'hold';
        }
        if ($action === 'revised') {
            return 'revised';
        }
        if (in_array($action, ['cancelled', 'deleted'], true)) {
            return 'cancelled';
        }
        if (strpos($action, 'item') === 0) {
            return 'item';
        }

        return 'neutral';
    }

    /**
     * Font Awesome 4 glyph matching the tone (the admin theme ships FA4).
     */
    public function getIconAttribute()
    {
        switch ($this->tone) {
            case 'created':   return 'fa-plus-circle';
            case 'approved':  return 'fa-check-circle';
            case 'hold':      return 'fa-hand-paper-o';
            case 'revised':   return 'fa-pencil-square-o';
            case 'cancelled': return 'fa-times-circle';
            case 'item':      return 'fa-list-ul';
            default:          return 'fa-info-circle';
        }
    }
}
