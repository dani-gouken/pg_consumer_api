<?php

return [
    "status_check" => [
        "max" => env("PAYMENT_STATUS_CHECK_DELAY"),
        "delay" => env("PAYMENT_STATUS_CHECK_MAX_ATTEMPT"),
    ]
];