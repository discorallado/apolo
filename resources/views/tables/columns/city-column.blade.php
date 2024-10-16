@php
	use Filament\Support\Enums\Alignment;
	use Filament\Support\Enums\FontFamily;
	use Filament\Support\Enums\FontWeight;
	use Filament\Support\Enums\IconPosition;
	use Filament\Tables\Columns\TextColumn\TextColumnSize;

	$alignment = $getAlignment();
	$url = $getUrl();

	if (!$alignment instanceof Alignment) {
	    $alignment = filled($alignment) ? Alignment::tryFrom($alignment) ?? $alignment : null;
	}

	$arrayState = $getState();

	if ($arrayState instanceof \Illuminate\Support\Collection) {
	    $arrayState = $arrayState->all();
	}

	$listLimit = 1;

	if (is_array($arrayState)) {
	    if ($listLimit = $getListLimit()) {
	        $limitedArrayStateCount = count($arrayState) > $listLimit ? count($arrayState) - $listLimit : 0;
	    }

	    $listLimit ??= count($arrayState);
	}

	$arrayState = \Illuminate\Support\Arr::wrap($arrayState);
@endphp

<div
	{{ $attributes->merge($getExtraAttributes(), escape: false)->class(['fi-ta-text grid w-full gap-y-1', 'px-3 py-4' => !$isInline()]) }}>
	@if (count($arrayState))
		<div @class([
			match ($alignment) {
				Alignment::Start => 'text-start',
				Alignment::Center => 'text-center',
				Alignment::End => 'text-end',
				Alignment::Left => 'text-left',
				Alignment::Right => 'text-right',
				Alignment::Justify, Alignment::Between => 'text-justify',
				default => $alignment,
			},
		])>
			@foreach ($arrayState as $state)
				@if (!($loop->iteration > $listLimit))
					<div
						@if ($loop->iteration > $listLimit) x-cloak
                            x-show="! isLimited"
                            x-transition @endif>
						<div>
							<span @class([
								'fi-ta-text-item-label',
								'group-hover/item:underline group-focus-visible/item:underline' => $url,
							])>
								{{ $generalSettings['comunas'][$state] }}
							</span>
						</div>
				@endif
		</div>
	@endforeach

	@if ($limitedArrayStateCount ?? 0)
		<div>
			<span class="text-sm text-gray-500 dark:text-gray-400">
				{{ trans_choice('filament-tables::table.columns.text.more_list_items', $limitedArrayStateCount) }}
			</span>
		</div>
	@endif
</div>
@elseif (($placeholder = $getPlaceholder()) !== null)
<x-filament-tables::columns.placeholder>
	{{ $placeholder }}
</x-filament-tables::columns.placeholder>
@endif
</div>
