<?php

return [
    /*
    |--------------------------------------------------------------------------
    | LendIT default asset loan duration
    |--------------------------------------------------------------------------
    |
    | Assets are the only LendIT item type with an explicit due date for now.
    | Accessories are returnable but do not have a defined deadline yet, and
    | consumables are issued without return.
    |
    */
    'default_asset_loan_days' => env('LENDIT_DEFAULT_ASSET_LOAN_DAYS', 14),

    /*
    |--------------------------------------------------------------------------
    | LendIT return reminders
    |--------------------------------------------------------------------------
    |
    | Snipe-IT already provides the snipeit:expected-checkin command for due
    | and overdue asset reminders. LendIT schedules that command even when the
    | broader Snipe-IT alert bundle is disabled, because reminder emails are a
    | project requirement.
    |
    */
    'return_reminders_enabled' => env('LENDIT_RETURN_REMINDERS_ENABLED', true),
];
