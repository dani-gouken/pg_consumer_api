<?php
namespace App\Services\Payment;
use App\Models\Service;

interface HandlesSearch
{
    public function search(Service $service, string $query): ?SearchResult;
}