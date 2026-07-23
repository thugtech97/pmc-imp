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
}