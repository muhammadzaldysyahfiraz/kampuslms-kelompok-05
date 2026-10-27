@props([
    'name',
    'size' => 200,
    'flip' => false,
    'class' => '',
])

<img src="{{ asset("images/illustrations/opendoodles-{$name}.svg") }}"
     alt=""
     aria-hidden="true"
     loading="lazy"
     decoding="async"
     width="{{ $size }}"
     height="{{ $size }}"
     {{ $attributes->merge(['class' => 'diorama-doodle select-none pointer-events-none transition-transform duration-300 ' . ($flip ? '-scale-x-100 ' : '') . $class]) }} />
