<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Contracts\View\View;

class ServiceController extends Controller
{
    public function index(): View
    {
        $services = Service::publicEnabled();
        return view("service.index", compact("services"));
    }

    public function show(Service $service): View
    {
        $this->authorize("viewProducts", $service);
        $products = $service->enabledProducts();
        return view("service.show", compact("products", "service"));
    }
}