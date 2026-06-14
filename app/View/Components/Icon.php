<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Icon extends Component
{
    public string $svgContent;

    public function __construct(
        public string $icon = 'tag',
        public string $iconSet = 'lucide',
        public ?string $color = null,
        public string $class = 'w-5 h-5',
        public ?string $fallbackIcon = null,
    ) {
        $this->svgContent = $this->loadSvg();
    }

    private function loadSvg(): string
    {
        $path = match ($this->iconSet) {
            'simple-icons', 'custom' => public_path("icons/brands/{$this->icon}.svg"),
            default => public_path("icons/lucide/{$this->icon}.svg"),
        };

        if (file_exists($path)) {
            return file_get_contents($path);
        }

        // fallback to lucide
        if ($this->fallbackIcon) {
            $fallback = public_path("icons/lucide/{$this->fallbackIcon}.svg");
            if (file_exists($fallback)) {
                return file_get_contents($fallback);
            }
        }

        $tagPath = public_path('icons/lucide/tag.svg');
        return file_exists($tagPath) ? file_get_contents($tagPath) : '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/></svg>';
    }

    public function render()
    {
        return view('components.icon');
    }
}
