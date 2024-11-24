<?php

namespace App\Filament\Resources\Management;

use App\Filament\Resources\Management\ProyectResource\Pages;
use App\Filament\Resources\Management\ProyectResource\Pages\CreateProyect;
use App\Filament\Resources\Management\ProyectResource\RelationManagers\MovementsRelationManager;
use App\Filament\Resources\Management\ProyectResource\RelationManagers\PurchasesRelationManager;
use App\Filament\Resources\Management\ProyectResource\RelationManagers\SalesRelationManager;
use App\Models\Management\Customer;
use App\Models\Management\Movement;
use App\Models\Management\Proyect;
use App\Tables\Columns\ProyectStatusColumn;
use Filament\Forms;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\SpatieTagsInput;
use Filament\Forms\Form;
use Filament\Infolists\Components\Grid;
use Filament\Resources\Resource;
use Filament\Support\Enums\ActionSize;
use Filament\Support\Enums\IconPosition;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;
use Pelmered\FilamentMoneyField\Forms\Components\MoneyInput;
use Pelmered\FilamentMoneyField\Tables\Columns\MoneyColumn;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\SpatieTagsEntry;
use Filament\Infolists\Components\Split;
use Filament\Infolists\Components\TextEntry;
use Filament\Support\Enums\FontWeight;
use Illuminate\Contracts\Support\Htmlable;
use Pelmered\FilamentMoneyField\Infolists\Components\MoneyEntry;
use Filament\Infolists\Components\SpatieMediaLibraryImageEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Resources\Pages\EditRecord;

class ProyectResource extends Resource
{
	protected static ?string $model = Proyect::class;

	protected static ?string $slug = 'proyectos';

	protected static ?string $modelLabel = 'Proyecto';

	protected static ?string $pluralModelLabel = 'Proyectos';

	protected static ?string $navigationGroup = 'Gestión';

	protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

	protected static ?int $navigationSort = 1;

	public static function getRecordTitle(?Model $record): string|Htmlable|null
	{
		return 'proyecto ' . $record?->titulo;
	}

	public static function infolist(Infolist $infolist): Infolist
	{
		return $infolist
			->columns(3)
			->schema([
				// Split::make([
				Grid::make(1)
					->columnSpan(2)
					->schema([
						Section::make('Detalles')
							->icon('heroicon-o-document-magnifying-glass')
							->description('Datos del proyeco.')
							->schema([
								TextEntry::make('titulo')
									->columnSpan(2),
								TextEntry::make('customer.nombre')
									->label('Cliente')
									->columnSpan(1),
								MoneyEntry::make('monto_proyectado')
									->placeholder('Sin monto.')
									->columnSpan(1),
								TextEntry::make('detalle')
									->placeholder('Sin detalles.')
									->columnSpan(2),
								SpatieTagsEntry::make('tags')
									->placeholder('Sin tags.')
									->columnSpan(2),
							])
							->columns(2)
							->columnSpan(2),
						Section::make('Contable')
							->icon('heroicon-o-banknotes')
							->description('Resumen de ventas y pagos.')
							->schema([
								Grid::make(3)
									->schema([
										MoneyEntry::make('cargos')
											->state(function (Model $record): float {
												return $record->movements->sum('cargo');
											})
											->columnSpan(1),
										MoneyEntry::make('ingresos')
											->state(function (Model $record): float {
												return $record->movements->sum('ingreso');
											})
											->columnSpan(1),
										MoneyEntry::make('deuda')
											->state(function (Model $record): float {
												return $record->movements->sum('cargo') - $record->movements->sum('ingreso');
											})
											->columnSpan(1),
									]),
								Grid::make(2)
									->schema([
										TextEntry::make('sales')
											->label('Facturas emitidas')
											->state(function (Model $record): float {
												return $record->sales->count();
											})
											->suffix(fn($state): string => ($state == 1) ? ' factura' : ' facturas'),
										TextEntry::make('purchases')
											->label('Facturas de compras')
											->state(function (Model $record): float {
												return $record->purchases->count();
											})
											->suffix(fn($state): string => ($state == 1) ? ' factura' : ' facturas'),
									])
							])
							->columns(3)
							->columnSpan(2),
						Section::make('Archivos')
							->icon('heroicon-s-paper-clip')
							->description('Documentos adjuntos.')
							->schema([
								ViewEntry::make('proyect_files')
									->label(false)
									->view('infolists.components.files-entry')
									->state(fn(Model $record) => $record->getMedia('proyectos'))
									->columnSpanFull(),
							])
							->columns(3)
							->columnSpan(2),

					]),
				Grid::make(1)
					->columnSpan(1)
					->schema([
						Section::make('Información del registro')
							->icon('heroicon-s-information-circle')
							->schema([
								TextEntry::make('created_at')
									->label('Creado')
									->since(),
								TextEntry::make('updated_at')
									->label('Última modificación')
									->since()
									->placeholder('sin modificaciones.'),
								TextEntry::make('user.name')
									->formatStateUsing(fn(string $state): string => strtoupper($state))
									->icon('heroicon-s-user'),
							]),
						Section::make('Estado del proyecto')
							->icon('heroicon-o-presentation-chart-bar')
							->schema([
								TextEntry::make('estado')
									->label(false)
									->size('medium')
									->extraAttributes(['class' => 'items-center'])
									->color(fn(bool $state) => $state ? 'success' : 'warning')
									->formatStateUsing(fn(bool $state) => $state ? 'Proyecto finalizado' : 'Proyecto activo'),
							]),
					]),
			]);
	}

	public static function form(Form $form): Form
	{
		return $form
			->schema([
				Forms\Components\Section::make('Detalles')
					->icon('heroicon-o-document-magnifying-glass')
					->description('Indique datos de identificación')
					->columns(2)
					->schema([
						Forms\Components\TextInput::make('titulo')
							->required()
							->maxLength(255)
							->columnSpan(2),
						Forms\Components\Select::make('id_cliente')
							->label('Cliente')
							->options(Customer::all()->pluck('nombre', 'id'))
							->relationship(name: 'customer', titleAttribute: 'nombre')
							->createOptionForm([
								Forms\Components\TextInput::make('nombre')
									->required(),
							])
							->createOptionUsing(function (array $data) {
								$data['user_id'] = auth()->id();
								$cliente = new Customer();
								$cliente->fill($data);
								$cliente->save();
								return $cliente->getKey();
							})
							->searchable()
							->preload()
							->required()
							->columnSpan(1),

						MoneyInput::make('monto_proyectado')
							->columnSpan(1),
						Forms\Components\ToggleButtons::make('estado')
							->required()
							->inline()
							->columnSpan(2)
							->default(0)
							->hiddenOn(['create', 'view.create'])
							->options([
								0 => 'Activo',
								1 => 'Pagado',
							])
							->icons([
								0 => 'heroicon-o-pencil',
								1 => 'heroicon-o-clock',
							])
							->colors([
								0 => 'info',
								1 => 'success',
							]),
						Forms\Components\Textarea::make('detalle')
							->maxLength(65535)
							->columnSpanFull()->columnSpan(2),
						SpatieTagsInput::make('tags')
							->type('proyectos')
							->columnSpanFull()
							->columnSpan(2),
					]),

				Forms\Components\Section::make('Archivos')
					->icon('heroicon-s-paper-clip')
					->description('Archivos adjuntos.')
					->schema([
						SpatieMediaLibraryFileUpload::make('proyect_files')
							->label(false)
							->collection('proyectos')
							->multiple()
							->openable()
							->downloadable()
							->deletable()
							->previewable()
							->columnSpanFull(),


					])
				// ->hidden(fn(?Proyect $record) => $record === null),
			])
			->columns(3);
	}

	public static function table(Table $table): Table
	{
		return $table
			->paginationPageOptions([50, 100, 200])
			->defaultPaginationPageOption(50)
			->groups([
				Group::make('customer.nombre')
					->label('Cliente')
					->collapsible()
					->titlePrefixedWithLabel(false)
					->orderQueryUsing(fn(Builder $query, string $direction) => $query->orderBy('created_at', $direction)),
			])
			->defaultSort('created_at', 'desc')
			->columns([
				Tables\Columns\TextColumn::make('created_at')
					->date(),

				// Tables\Columns\TextColumn::make('title')
				// 	->limit(30)
				// 	->tooltip(fn(TextColumn $column): ?string => strlen($column->getState()) <= 30 ? null : $column->getState())
				// 	->label('Título')
				// 	->columnSpan(3)
				// 	// ->toggleable()
				// 	->sortable()
				// 	->searchable(),
				// Tables\Columns\TextColumn::make('customer.nombre')
				// 	->limit(15)
				// 	->tooltip(fn(TextColumn $column): ?string => strlen($column->getState()) <= 15 ? null : $column->getState())
				// 	->label('Cliente')
				// 	->columnSpan(2)
				// 	// ->toggleable()
				// 	->sortable()
				// 	->searchable(),
				Tables\Columns\TextColumn::make('titulo_compuesto')
					->label('Titulo')
					->columnSpan(2)
					// ->toggleable()
					->sortable()
					->searchable(),
				MoneyColumn::make('monto_proyectado')
					->label('Monto')
					->placeholder('Sin ingresos')
					// ->currency('clp')
					->summarize(Sum::make()->money('clp', 1, 'es_CL')->label('Monto'))
					// ->toggleable()
					->sortable(),
				MoneyColumn::make('movements_sum_cargo')->sum('movements', 'cargo')
					->label('Cargos')
					// ->currency('clp')
					->summarize(Sum::make()->money('clp', 1, 'es_CL')->label('Cargos'))
					->placeholder('Sin cargos')
					// ->toggleable()
					->sortable(),
				MoneyColumn::make('movements_sum_ingreso')->sum('movements', 'ingreso')
					->label('Ingresos')
					->placeholder('Sin ingresos')
					->currency('clp')
					->summarize(Sum::make()->money('clp', 1, 'es_CL')->label('Ingresos'))
					// ->toggleable()
					->sortable(),
				// Tables\Columns\TextColumn::make('estatus')
				// 	// Tables\Columns\TextColumn::make('ciudad')
				// 	// ->view('tables.columns.')
				// 	->placeholder('Sin registro.'),
				// ProyectStatusColumn::make('estatus')
				// 	// Tables\Columns\TextColumn::make('ciudad')
				// 	// ->view('tables.columns.')
				// 	->placeholder('Sin registro.'),
				Tables\Columns\TextColumn::make('created_at')
					->label('Creado')
					->date()
					->sortable()
					->searchable()
					->toggleable(isToggledHiddenByDefault: true),
				Tables\Columns\TextColumn::make('updated_at')
					->label('Modificado')
					->placeholder('Sin modificaciones.')
					->date()
					->sortable()
					->searchable()
					->toggleable(isToggledHiddenByDefault: true),
				Tables\Columns\TextColumn::make('deleted_at')
					->label('Eliminado')
					->placeholder('Sin eliminar.')
					->date()
					->sortable()
					->searchable()
					->toggleable(isToggledHiddenByDefault: true),
			])
			->filters([
				DateRangeFilter::make('created_at')
					->placeholder('desde - hasta')
					->label('Filtrar por fecha'),
				Tables\Filters\TrashedFilter::make(),
			])
			->actions([
				Tables\Actions\Action::make('pagar_proyect')
					->disabled(fn(?Model $record) => match ($record->estatus) {
						'inactivo' => true,
						'activo' => false,
						'finalizar' => false,
						'finalizado' => true
					})
					->label(fn(?Model $record) => match ($record->estatus) {
						'inactivo' => 'Inactivo',
						'activo' => 'Activo',
						'finalizar' => 'Finalizar',
						'finalizado' => 'Finalizado'
					})
					->icon(fn(?Model $record) => match ($record->estatus) {
						'inactivo' => 'heroicon-o-banknotes',
						'activo' => 'heroicon-o-banknotes',
						'finalizar' => 'heroicon-o-check-circle',
						'finalizado' => 'heroicon-o-check-badge'
					})
					->iconPosition(IconPosition::After)
					->color(fn(?Model $record) => match ($record->estatus) {
						'inactivo' => 'danger',
						'activo' => 'warning',
						'finalizar' => 'success',
						'finalizado' => 'primary'
					})
					// ->hidden(CustomerResource\RelationManagers\ProyectsRelationManager::class)
					->button()
					// ->outlined()
					->modalHeading(fn(Model $record): string => 'Finalizar proyecto "' . $record->titulo . '" de ' .  $record->customer->nombre)
					->modalSubmitActionLabel('Guardar')
					->form(function (Model $record) {
						$relatedMovements = $record->movements;
						if ($relatedMovements->count() > 0) {
							$relatedCargos = $relatedMovements->sum('cargo');
							$relatedIngresos = $relatedMovements->sum('ingreso');
							$diff = $relatedCargos - $relatedIngresos;
							$color = $diff > 0 ? 'warning' : (($relatedIngresos > 0) ? 'success' : 'danger');
							$mensaje = $diff > 0 ? 'Verifica que no exista deuda antes de cambiar el estado del proyecto.' : (($relatedIngresos > 0) ? 'Deuda ok.' : '¡El proyecto no registra pagos! Verifica que no exista deuda antes de cambiar el estado del proyecto.');
							return [
								Forms\Components\Grid::make(3)
									->schema([
										Forms\Components\Placeholder::make('cargos')
											->content('$' . number_format($relatedCargos, 0, 0, '.')),
										Forms\Components\Placeholder::make('ingresos')
											->content('$' . number_format($relatedIngresos, 0, 0, '.')),
										Forms\Components\Placeholder::make('deuda')
											->content('$' . number_format($diff, 0, 0, '.')),
									]),
								Forms\Components\ViewField::make('mensaje')
									->view('forms.components.aviso')
									->viewData([
										'color' => $color,
										'aviso' => $mensaje,
									])
							];
						}
					})
					->action(function (array $data, Model $record): void {
						$proyect = Proyect::find($record->id);
						$proyect->estado = 1;
						$proyect->save();
					})
					->requiresConfirmation(),
				Tables\Actions\ActionGroup::make([
					Tables\Actions\EditAction::make(),
					Tables\Actions\DeleteAction::make(),
					Tables\Actions\ForceDeleteAction::make(),
					Tables\Actions\RestoreAction::make(),
				])
			])
			->bulkActions([
				Tables\Actions\BulkActionGroup::make([
					Tables\Actions\DeleteBulkAction::make(),
					Tables\Actions\ForceDeleteBulkAction::make(),
					Tables\Actions\RestoreBulkAction::make(),
				]),
			])
			->emptyStateActions([
				Tables\Actions\CreateAction::make(),
			]);
	}

	public static function getRelations(): array
	{
		return [
			MovementsRelationManager::class,
			SalesRelationManager::class,
			PurchasesRelationManager::class,
		];
	}

	public static function getPages(): array
	{
		return [
			'index' => Pages\ManageProyects::route('/'),
			// 'create' => Pages\CreateProyect::route('/create'),
			// 'index' => Pages\ManageProyects::route('/'),
			'view' => Pages\ViewProyect::route('/{record}'),
			// 'edit' => Pages\EditProyect::route('/{record}/edit'),
		];
	}


	public static function getWidgets(): array
	{
		return [
			ProyectResource\Widgets\ProyectStatsWidget::class,
		];
	}

	public static function getEloquentQuery(): Builder
	{
		return parent::getEloquentQuery()
			->withoutGlobalScopes([
				SoftDeletingScope::class,
			]);
	}
	public static function getNavigationBadge(): ?string
	{
		$modelClass = static::$model;
		return (string) $modelClass::all()->count();
	}

	public static function estadoPagos(Model $record): ?array
	{
		$relatedMovements = Movement::where('id_proyecto', '=', $record->id)->get();
		if ($relatedMovements->count() > 0) {
			$relatedCargos = $relatedMovements->sum('cargo');
			$relatedIngresos = $relatedMovements->sum('ingreso');
			$diff = $relatedCargos - $relatedIngresos;
			return ['diff' => $diff, 'cargos' => $relatedCargos, 'ingresos' => $relatedIngresos];
		}
		return [];
	}
}