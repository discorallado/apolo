@php
    use Filament\Support\Enums\IconPosition;
    use Filament\Support\Facades\FilamentView;

    $chartColor = $getChartColor() ?? 'gray';
    $descriptionColor = $getDescriptionColor() ?? 'gray';
    $descriptionIcon = $getDescriptionIcon();
    $descriptionIconPosition = $getDescriptionIconPosition();
    $url = $getUrl();
    $tag = $url ? 'a' : 'div';
    $dataChecksum = $generateDataChecksum();

    $descriptionIconClasses = \Illuminate\Support\Arr::toCssClasses([
        'fi-wi-stats-overview-stat-description-icon inline h-5 w-5',
        match ($descriptionColor) {
            'gray' => 'text-gray-400 dark:text-gray-500',
            default => 'text-custom-500',
        },
    ]);

    $descriptionIconStyles = \Illuminate\Support\Arr::toCssStyles([
        \Filament\Support\get_color_css_variables(
            $descriptionColor,
            shades: [500],
            alias: 'widgets::stats-overview-widget.stat.description.icon',
        ) => $descriptionColor !== 'gray',
    ]);
@endphp

<{!! $tag !!}
    @if ($url) {{ \Filament\Support\generate_href_html($url, $shouldOpenUrlInNewTab()) }} @endif
    {{ $getExtraAttributeBag()->class([
        'fi-wi-stats-overview-stat relative rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10',
    ]) }}>

    <div class="grid grid-cols-3 grid-rows-2">

        <div class="col-span-3 row-span-1 -mt-4 flex items-center">
            @if ($icon = $getIcon())
                <x-filament::icon :icon="$icon"
                    class="fi-wi-stats-overview-stat-icon h-5 w-5 -mt-1 text-gray-400 dark:text-gray-500" />
            @endif

            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">
                {{ $getLabel() }}
            </span>
        </div>

        <div
            class="col-span-1 row-span-1 text-center justify-center -mt-1 text-4xl font-semibold text-gray-950 dark:text-white">
            {{ $getValue() }}
        </div>

        @if ($description = $getDescription())
            <div class="col-span-2 row-span-1 -mt-1 flex items-center">


                <span @class([
                    'fi-wi-stats-overview-stat-description text-sm',
                    match ($descriptionColor) {
                        'gray' => 'fi-color-gray text-gray-500 dark:text-gray-400',
                        default => 'fi-color-custom text-custom-600 dark:text-custom-400',
                    },
                ]) @style([
                    \Filament\Support\get_color_css_variables($descriptionColor, shades: [400, 600], alias: 'widgets::stats-overview-widget.stat.description') => $descriptionColor !== 'gray',
                ])>
                    {{ $description }}
                    @if ($descriptionIcon)
                        <x-filament::icon :icon="$descriptionIcon" :class="$descriptionIconClasses" :style="$descriptionIconStyles" />
                    @endif
                </span>

            </div>
        @endif

    </div>

    @if ($chart = $getChart())
        {{-- An empty function to initialize the Alpine component with until it's loaded with `ax-load`. This removes the
    need for `x-ignore`, allowing the chart to be updated via Livewire polling. --}}
        <div x-data="{ statsOverviewStatChart: function() {} }">
            <div @if (FilamentView::hasSpaMode()) ax-load="visible" @else ax-load @endif
                ax-load-src="{{ \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc('stats-overview/stat/chart', 'filament/widgets') }}"
                x-data="statsOverviewStatChart({
                    dataChecksum: @js($dataChecksum),
                    labels: @js(array_keys($chart)),
                    values: @js(array_values($chart)),
                })" @class([
                    'fi-wi-stats-overview-stat-chart absolute inset-x-0 bottom-0 overflow-hidden rounded-b-xl',
                    match ($chartColor) {
                        'gray' => 'fi-color-gray',
                        default => 'fi-color-custom',
                    },
                ]) @style([
                    \Filament\Support\get_color_css_variables($chartColor, shades: [50, 400, 500], alias: 'widgets::stats-overview-widget.stat.chart') => $chartColor !== 'gray',
                ])>
                <canvas x-ref="canvas" class="h-6"></canvas>

                <span x-ref="backgroundColorElement" @class([
                    match ($chartColor) {
                        'gray' => 'text-gray-100 dark:text-gray-800',
                        default => 'text-custom-50 dark:text-custom-400/10',
                    },
                ])></span>

                <span x-ref="borderColorElement" @class([
                    match ($chartColor) {
                        'gray' => 'text-gray-400',
                        default => 'text-custom-500 dark:text-custom-400',
                    },
                ])></span>
            </div>
        </div>
    @endif
    </{!! $tag !!}>
