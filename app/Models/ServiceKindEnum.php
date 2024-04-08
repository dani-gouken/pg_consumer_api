<?php

namespace App\Models;

enum ServiceKindEnum: string {
    case payment = "payment";
    case topup = "topup";
    case bill = "bill";
}