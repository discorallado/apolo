<x-dynamic-component :component="$getFieldWrapperView()" :has-inline-label="$hasInlineLabel()" :id="$getId()" :label="$getLabel()" :label-sr-only="$isLabelHidden()"
	:helper-text="$getHelperText()" :hint="$getHint()" :hint-actions="$getHintActions()" :hint-color="$getHintColor()" :hint-icon="$getHintIcon()"
	:hint-icon-tooltip="$getHintIconTooltip()" :state-path="$getStatePath()">
	<div {{ $attributes->merge($getExtraAttributes(), escape: false)->class(['fi-fo-placeholder text-sm leading-6 max-w-fit']) }}>
		<x-filament::badge icon="heroicon-s-user">
			{{ $getRecord()->user->name }}
		</x-filament::badge>
	</div>
</x-dynamic-component>