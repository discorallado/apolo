<x-dynamic-component :component="$getEntryWrapperView()" :entry="$entry">
	@if(blank($getState()))
	<span class="text-gray-500 dark:text-gray-400">Sin archivos.</span>
	@else
	<ul class="max-w-full list-none">
		@foreach ($getState() as $attachment)
			<li class="">
				<a href="{{ $attachment->getUrl() }}" target="_blank" class="inline-flex items-baseline">
                    @if(explode('/', $attachment->mime_type)[0] === 'image')
					<x-filament::icon icon="heroicon-o-photo" class="w-5 h-5 mt-1 text-gray-500 dark:text-gray-400" />
                    @else
					<x-filament::icon icon="heroicon-o-document-duplicate" class="w-5 h-5 mt-1 text-gray-500 dark:text-gray-400" />
                    @endif
					<span>{{ $attachment->name . '.' . explode('.', $attachment->file_name)[1] }}</span>
				</a>
			</li>
		@endforeach
	</ul>
	@endif
</x-dynamic-component>
