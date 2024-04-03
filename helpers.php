<?php

if (!function_exists('format_amount')) {
    function format_amount(int $amount): string
    {
        return number_format($amount, 0, ",", " ") . " FCFA";
    }
}