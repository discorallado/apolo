<?php

namespace App\Forms\Components;

use App\Filament\Resources\Management\CustomerResource;
use App\Filament\Resources\Management\ProyectResource;
use App\Models\Management\Customer;
use App\Models\Management\Movement;
use App\Models\Management\Proyect;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Illuminate\Database\Eloquent\Model;

use Filament\Forms\Components\Field;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieTagsInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;
use Pelmered\FilamentMoneyField\Forms\Components\MoneyInput;
use PhpParser\Node\Expr\Cast\Array_;

class CustomerProyectField extends Field
{
    protected string $view = 'forms.components.customer-proyect-field';

    public $relationship = 'proyect';

    public function relationship(string | callable $relationship): static
    {
        $this->relationship = $relationship;

        return $this;
    }

    public function readOnly(): bool
    {
        return false;
    }

    public function getRelationship(): string
    {
        // return $this->evaluate($this->relationship) ?? $this->getName();
        return $this->evaluate($this->relationship) ?? false;
    }
    protected function setUp(): void
    {
        parent::setUp();
        $this->afterStateHydrated(function (CustomerProyectField $component, ?Model $record) {
            // dd($this->getRelationship());
            // $proyect = $record?->getRelationValue($this->getRelationship())->toArray();
            $proyect = filled($record->proyect) ? $record->proyect->id : null;
            $customer = filled($record->customer) ? $record->customer->id : (filled($record->proyect) ? $record->proyect->customer->id : null);

            // dd($proyect);
            // $array_proyecto = $proyect?->toArray();
            // dd($component->getState());
            $component->state([
                'id' => $proyect,
                'id_cliente' => $customer
            ]);
            // dd($component);
        });

        // $this->dehydrated(false);
    }
    // public function saveRelationships(): void
    // {
    //     $state = $this->getState();
    //     dd($state);
    //     $record = $this->getRecord();
    //     $relationship = $record?->{$this->getRelationship()}();

    //     if ($relationship === null) {
    //         return;
    //     } elseif ($data = $relationship->first()) {
    //         $data->update($state);
    //     } else {
    //         $relationship->updateOrCreate($state);
    //     }

    //     $record->touch();
    // }

    public function getChildComponents(): array
    {
        return [
            Forms\Components\Section::make('Proyecto')
                ->icon('heroicon-s-tag')
                // ->description('Datos de cliente y proyecto.')
                ->columns(2)
                ->schema([
                    // Forms\Components\Grid::make(2)
                    //     ->schema([
                    Forms\Components\Select::make('id_cliente')
                        ->label('Cliente')
                        ->prefixIcon('heroicon-s-user')
                        ->required($this->isRequired())
                        ->options(Customer::query()->pluck('nombre', 'id'))
                        ->live()
                        ->searchable()
                        ->hintAction(
                            Action::make('Ver cliente')
                                ->url(fn($state) => filled($state) ? CustomerResource::getUrl('view', ['record' => $state]) : null)
                                ->openUrlInNewTab()
                                ->icon('heroicon-s-link')
                        )
                        ->createOptionForm([
                            Forms\Components\TextInput::make('nombre')
                                ->required()
                                ->columnSpan(2),
                        ])
                        ->createOptionUsing(function (array $data, Get $get) {
                            $data['nombre'] = $get('nombre');
                            $data['user_id'] = auth()->user()->id;
                            if ($customer = Customer::create($data)) {
                                return $customer->id;
                            }
                        })
                        ->createOptionAction(function (Forms\Components\Actions\Action $action) {
                            return $action
                                ->modalHeading('Crear cliente')
                                ->modalWidth('xl');
                        }),

                    Forms\Components\Select::make('id')
                        ->label('Proyecto')
                        ->prefixIcon('heroicon-s-rectangle-stack')
                        ->live()
                        ->hidden(fn(Get $get): Bool => blank($get('id_cliente')))
                        ->options(fn(Get $get): Collection => Proyect::query()
                            ->where('id_cliente', $get('id_cliente'))
                            ->pluck('titulo', 'id'))
                        ->searchable()
                        ->hintAction(
                            Action::make('Ver proyecto')
                                ->url(fn($state) =>  filled($state) ? ProyectResource::getUrl('view', ['record' => $state]) : null)
                                ->openUrlInNewTab()
                                ->icon('heroicon-s-link')
                        )
                        ->createOptionForm([
                            Forms\Components\TextInput::make('titulo')
                                ->required()
                                ->columnSpan(2),

                            MoneyInput::make('monto_proyectado')
                                ->required()
                                ->columnSpan(1),

                            SpatieTagsInput::make('tags')
                                ->prefixIcon('heroicon-s-tag')
                                ->type('proyectos')
                                ->columnSpan(2),
                            Forms\Components\RichEditor::make('detalle')
                                ->columnSpan(2),
                        ])
                        ->createOptionUsing(function (array $data, Get $get) {
                            $data['id_cliente'] = $get('id_cliente');
                            $data['user_id'] = auth()->user()->id;
                            if ($proyect = Proyect::create($data)) {
                                return $proyect->id;
                            }
                        })
                        ->createOptionAction(function (Forms\Components\Actions\Action $action) {
                            return $action
                                ->modalHeading('Crear proyecto')
                                ->modalWidth('xl');
                        }),

                    // ]),
                ])
        ];
    }
}
