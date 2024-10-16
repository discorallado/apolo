<?php

namespace App\Filament\Pages;

use App\Settings\GeneralSettings;
use Icetalker\FilamentTableRepeater\Forms\Components\TableRepeater;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;
use Filament\Forms\Components\Tabs;
// use App\Filament\Clusters\Settings as SettingsCluster;

class Settings extends SettingsPage
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?int $navigationSort = 1;

    // protected static ?string $cluster = SettingsCluster::class;

    protected static ?string $title = 'Ajustes';

    protected static ?string $navigationGroup = '';
    protected static string $settings = GeneralSettings::class;

    public static function getNavigationGroup(): ?string
    {
        return 'Configuración';
    }

    public function form(Form $form): Form
    {
        return $form
            ->columns(3)
            ->schema([

                Forms\Components\Section::make('Entorno')
                    ->description('Variables de uso general.')
                    ->icon('heroicon-o-document-text')
                    ->columnSpanFull()
                    ->schema([

                        Tabs::make('Tabs')
                            ->tabs([
                                Tabs\Tab::make('Codigos de docto. tributarios')
                                    ->icon('heroicon-o-building-library')
                                    ->schema([
                                        Forms\Components\Repeater::make('codigos_dt')
                                            ->label(false)
                                            ->collapsible()
                                            ->collapsed()
                                            ->cloneable()
                                            ->itemLabel(function (array $state) {
                                                return $state['code'] . ': ' . $state['label'];
                                            })
                                            ->schema([
                                                Forms\Components\TextInput::make('code')
                                                    ->required()
                                                    ->label('Cod')
                                                    ->numeric()
                                                    ->unique()
                                                    ->columnSpan(1),
                                                Forms\Components\TextInput::make('label')
                                                    ->required()
                                                    ->label('Etiqueta')
                                                    ->columnSpan(2),
                                                Forms\Components\TextInput::make('min')
                                                    ->label('Min')
                                                    ->columnSpan(2),

                                            ])
                                            ->columns(5)
                                            ->columnSpan('full'),
                                    ]),

                                Tabs\Tab::make('Comunas')
                                    ->icon('heroicon-o-map-pin')
                                    ->schema([
                                        Forms\Components\Repeater::make('comunas')
                                            ->label(false)
                                            ->grid(3)
                                            ->cloneable()
                                            ->simple(
                                                Forms\Components\TextInput::make('comuna')
                                            )
                                    ]),
                                Tabs\Tab::make('Mes tributario')
                                    ->icon('heroicon-o-calendar-days')
                                    ->schema([
                                        Forms\Components\Repeater::make('periodo_mes')
                                            ->label(false)
                                            ->grid(3)
                                            ->cloneable()
                                            ->simple(
                                                Forms\Components\TextInput::make('periodo_mes')
                                            )
                                    ]),
                                Tabs\Tab::make('Años tributario')
                                    ->icon('heroicon-o-calendar')
                                    ->schema([
                                        Forms\Components\Repeater::make('periodo_ano')
                                            ->label(false)
                                            ->grid(3)
                                            ->cloneable()
                                            ->simple(
                                                Forms\Components\TextInput::make('periodo_ano')
                                            )
                                    ]),
                                Tabs\Tab::make('Variables')
                                    ->icon('heroicon-o-building-library')
                                    ->schema([
                                        Forms\Components\Repeater::make('variables')
                                            ->label(false)
                                            ->collapsible()
                                            ->collapsed()
                                            ->cloneable()
                                            ->itemLabel(function (array $state) {
                                                return $state['key'] . ' = ' . $state['value'];
                                            })
                                            ->schema([
                                                Forms\Components\TextInput::make('key')
                                                    ->required()
                                                    ->label('Clave')
                                                    ->unique()
                                                    ->columnSpan(1),
                                                Forms\Components\TextInput::make('value')
                                                    ->required()
                                                    ->label('Valor')
                                                    ->columnSpan(2),
                                            ])
                                            ->columns(5)
                                            ->columnSpan('full'),
                                    ]),
                            ])
                    ])
            ]);
    }
}