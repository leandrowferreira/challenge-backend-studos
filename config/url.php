<?php

return [
    'check_before'       => env('URL_CHECK_BEFORE', 0),
    'allow_multiple'     => env('URL_ALLOW_MULTIPLE', 0),
    'renovate_on_access' => env('RENOVATE_ON_ACCESS', 1),
    'valid_days'         => env('URL_VALID_DAYS', 30),
];
