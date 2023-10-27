<?php

namespace App\Http\Controllers;

use App\Models\Service;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::publicEnabled();
        return view("service.index", compact("services"));
    }

    public function show(string $slug)
    {
        $service = Service::findPubliclyUsableBySlugOrFail($slug);
        $this->authorize("viewProducts", $service);
        $products = $service->enabledProducts();
        return view("service.show", compact("products", "service"));
    }
}