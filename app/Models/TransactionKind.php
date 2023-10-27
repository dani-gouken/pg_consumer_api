<?php

namespace App\Models;

enum TransactionKind: string {
    case debit = "debit";
    case credit = "credit";
}