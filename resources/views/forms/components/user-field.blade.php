<x-dynamic-component :component="$getFieldWrapperView()" :has-inline-label="$hasInlineLabel()" :id="$getId()" :label="$getLabel()" :label-sr-only="$isLabelHidden()"
	:helper-text="$getHelperText()" :hint="$getHint()" :hint-actions="$getHintActions()" :hint-color="$getHintColor()" :hint-icon="$getHintIcon()" :hint-icon-tooltip="$getHintIconTooltip()"
	:state-path="$getStatePath()">
	<div
		{{ $attributes->merge($getExtraAttributes(), escape: false)->class(['fi-fo-placeholder inline-flex text-sm leading-6 gap-x-1 max-w-fit']) }}>
		<x-filament::icon icon="heroicon-m-user" class="w-5 h-5 mt-1 text-gray-500 dark:text-gray-400" />
		{{ $getRecord()->user->name }}
	</div>
</x-dynamic-component>
