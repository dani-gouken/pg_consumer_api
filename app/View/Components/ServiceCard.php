<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ServiceCard extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public string $description = '',
        public string $title = '',
        public string $subtitle = '',
        public string $image = "",
        public string $action = "",
        public bool $imgSmall = false,
        public bool $spacy = false,
        public bool $border = true,
    ) {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.service-card');
    }
}