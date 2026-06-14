@php
    // Inject class and color into SVG
    $svg = preg_replace('/<svg\s/', '<svg ', $svgContent, 1);
    $colorStyle = $color ? "color:{$color};" : '';
    $svg = preg_replace(
        '/<svg/',
        "<svg class=\"{$class} inline-block flex-shrink-0\" style=\"{$colorStyle}\" aria-hidden=\"true\"",
        $svg, 1
    );
    // For lucide: already uses currentColor, for simple-icons: fill currentColor
    if ($iconSet === 'simple-icons' || $iconSet === 'custom') {
        $svg = preg_replace('/fill="[^"]*"/', 'fill="currentColor"', $svg);
    }
@endphp
{!! $svg !!}
