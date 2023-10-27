<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Payment\TransactionProcessorInterface;
use Illuminate\Http\Request;

class CallbackController extends Controller
{
    public function __invoke(Request $request, TransactionProcessorInterface $processor)
    {
        return $processor->handleCallback($request);
    }
}
