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
];
