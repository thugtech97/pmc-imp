<?php

namespace App\Constants;

class Status
{
    public const APPROVED_WFS = 'APPROVED - WFS';
    public const APPROVED_MCD = 'APPROVED - MCD (Planner)';
    public const SAVED = 'SAVED';
    public const SUBMITTED = 'SUBMITTED';
    public const CANCELLED = 'CANCELLED';
    public const APPROVED_APPROVER = 'APPROVED - MCD (Approver)';

    // MCD review workflow — hold (return for re-edit) and terminal reject
    public const HOLD_PLANNER = 'HOLD - MCD (Planner)';        // returned to the department user
    public const HOLD_APPROVER = 'HOLD - MCD (Approver)';      // returned to the MCD Planner
    public const REJECTED_PLANNER = 'REJECTED - MCD (Planner)';
    public const REJECTED_APPROVER = 'REJECTED - MCD (Approver)';

    // The Planning Supervisor took over the final IMF approval from the MCD Approver.
    // The three *_APPROVER statuses above are kept for IMFs decided before the change.
    public const APPROVED_SUPERVISOR = 'APPROVED - Planning Supervisor';
    public const HOLD_SUPERVISOR = 'HOLD - Planning Supervisor';      // returned to the MCD Planner
    public const REJECTED_SUPERVISOR = 'REJECTED - Planning Supervisor';

    /**
     * Final approval, current and legacy. Use these anywhere an IMF is matched by
     * its last stage so pre-existing records keep behaving the same.
     *
     * @return array
     */
    public static function imfFinalApproved()
    {
        return [self::APPROVED_SUPERVISOR, self::APPROVED_APPROVER];
    }

    /**
     * Held at the final stage and returned to the MCD Planner.
     *
     * @return array
     */
    public static function imfFinalHold()
    {
        return [self::HOLD_SUPERVISOR, self::HOLD_APPROVER];
    }

    /**
     * Rejected at the final stage.
     *
     * @return array
     */
    public static function imfFinalRejected()
    {
        return [self::REJECTED_SUPERVISOR, self::REJECTED_APPROVER];
    }
}