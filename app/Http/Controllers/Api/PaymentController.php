<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentResource;
use App\Models\ServicePayment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function get(Request $request, string $code): PaymentResource
    {
        return new PaymentResource(ServicePayment::findByCodeOrFail($code));
    }
}