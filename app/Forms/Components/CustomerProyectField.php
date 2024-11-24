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
use Filament\Forms\Form;
use Filament\Forms\Get;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;

class CustomerProyectField extends Field
{
    protected string $view = 'forms.components.customer-proyect-field';

    public $relationship = null;

    public function relationship(string | callable $relationship): static
    {
        $this->relationship = $relationship;

        return $this;
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
                ->description('Datos de cliente y proyecto.')
                ->columns(2)
                ->schema([
                    // Forms\Components\Grid::make(2)
                    //     ->schema([
                    Forms\Components\Select::make('id_cliente')
                        ->label('Cliente')
                        ->required($this->isRequired())
                        ->options(Customer::query()->pluck('nombre', 'id'))
                        ->live()
                        ->hintAction(
                            Action::make('Ver cliente')
                                ->url(fn($state) => $state > 0 ? CustomerResource::getUrl('view', ['record' => $state]) : null)
                                ->openUrlInNewTab()
                                ->icon('heroicon-s-user')
                        )
                        ->searchable(),

                    Forms\Components\Select::make('id')
                        ->label('Proyecto')
                        ->required($this->isRequired())
                        ->live()
                        ->hidden(fn(Get $get): Bool => $get('id_cliente') === null)
                        ->options(fn(Get $get): Collection => Proyect::query()
                            ->where('id_cliente', $get('id_cliente'))
                            ->pluck('titulo', 'id'))
                        ->searchable()
                        ->hintAction(
                            Action::make('Ver proyecto')
                                ->url(fn($state) =>  $state ? ProyectResource::getUrl('view', ['record' => $state]) : null)
                                ->openUrlInNewTab()
                                ->icon('heroicon-s-rectangle-stack')
                        )
                        ->createOptionForm(
                            function (Form $form) {

                                ProyectResource::form($form);
                            }
                        )
                        //     [
                        //     Forms\Components\TextInput::make('titulo')
                        //         ->required(),
                        //     Forms\Components\TextInput::make('monto_proyectado')
                        //         ->numeric()
                        //         ->prefix('$')
                        //         ->required(),
                        // ])
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
                                ->modalWidth('md');
                        }),

                    // ]),
                ])
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->afterStateHydrated(function (CustomerProyectField $component, ?Model $record) {
            $proyect = $record?->getRelationValue($this->getRelationship());
            $array_proyecto = $proyect?->toArray();
            $component->state($array_proyecto ? [
                'id' => $array_proyecto['id'],
                'id_cliente' => $array_proyecto['id_cliente'],
            ] : [
                'id' => null,
                'id_cliente' => null,
            ]);
            // dd($component);
        });

        // $this->dehydrated(false);
    }


    public function readOnly(): bool
    {
        return false;
    }
    public function getRelationship(): string
    {
        return $this->evaluate($this->relationship) ?? $this->getName();
    }
}