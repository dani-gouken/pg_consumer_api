<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Input extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public string $label = "",
        public string $placeholder = "",
        public string $value = "",
        public string $name = "",
        public string $max = "",
        public string $min = "",
        public string $type = "text",
        public bool $required = false,
        public bool $disabled = false,
        public bool $readonly = false,
        public bool $center = false,
        public string $pattern = "",
    )
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.input');
    }
}
