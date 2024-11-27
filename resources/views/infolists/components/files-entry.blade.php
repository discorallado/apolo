<x-dynamic-component :component="$getEntryWrapperView()" :entry="$entry">
	@if (blank($getState()))
		<x-filament-infolists::entries.placeholder>
			Sin archivos.
		</x-filament-infolists::entries.placeholder>
	@else
		<ul class="max-w-full list-none">
			@foreach ($getState() as $attachment)
				<li class="">
					<a href="{{ $attachment->getUrl() }}" target="_blank" class="inline-flex items-baseline text-sm">
						@if (explode('/', $attachment->mime_type)[0] === 'image')
							<x-filament::icon icon="heroicon-o-photo" class="h-5 w-5 text-gray-500 dark:text-gray-400" />
						@else
							<x-filament::icon icon="heroicon-o-document-duplicate" class="h-5 w-5 text-gray-500 dark:text-gray-400" />
						@endif
						<span>{{ $attachment->name . '.' . explode('.', $attachment->file_name)[1] }}</span>
					</a>
				</li>
			@endforeach
		</ul>
	@endif
</x-dynamic-component>
