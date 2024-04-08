<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ServiceResource;
use App\Models\Service;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ServiceController extends Controller
{

    public function index(): AnonymousResourceCollection
    {
        $services = Service::publicEnabledQuery()
            ->with("products", function (HasMany $builder) {
                return $builder->where("enabled", "=", true);
            })
            ->get();
        return ServiceResource::collection($services);
    }

}