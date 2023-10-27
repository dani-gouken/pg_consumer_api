<?php

namespace App\Models;

enum ServiceKindEnum: string {
    case payment = "payment";
    case bill = "bill";
}