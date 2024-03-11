<?php

namespace App\Services\Smobilpay;

use Illuminate\Http\Client\PendingRequest;
use Http;

/**
 * @property string $baseUrl
 */
trait Client
{
    protected function client(?string $token = null): PendingRequest
    {
        $pendingRequest = Http::withUrlParameters([
            'baseUrl' => $this->baseUrl,
        ])
            ->withOptions([
                'allow_redirects' => false,
            ]);
        if (!is_null($token)) {
            $pendingRequest = $pendingRequest->withCookies(
                [SmobilpayScrapingService::AUTH_COOKIE_NAME => $token],
                ".smobilpay.com"
            );
        }
        return $pendingRequest;
    }
}