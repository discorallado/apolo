<?php

namespace App\Filament\Resources\HR\BinnacleResource\Widgets;

use App\Filament\Resources\HR\BinnacleResource;
use App\Models\HR\Binnacle;
use App\Models\HR\Worker;
use App\Models\Management\Proyect;
use App\Settings\GeneralSettings;
use Filament\Actions\Action;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Model;
use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;
use Saade\FilamentFullCalendar\Actions;
use Saade\FilamentFullCalendar\Actions\CreateAction;
use Filament\Support\Enums\ActionSize;

use Filament\Forms;
use Filament\Forms\Form;

class CalendarWidget extends FullCalendarWidget
{
    public Model|string|null $model = Binnacle::class;

    public function config(): array
    {
        return [
            'firstDay' => 1,
            // 'editable' => true,
            // 'selectable' => true,
            // 'defaultAllDay' => true,
            // 'allDaySlot' => true,
            'headerToolbar' => [
                'left' => 'dayGridMonth,dayGridDay',
                'center' => 'title',
                'right' => 'prev,next today',

            ],
        ];
    }

    public function getFormSchema(): array
    {
        return [
            Forms\Components\Select::make('id_trabajador')
                ->options(Worker::query()->pluck('nombre', 'id'))
                ->live()
                ->searchable()
                ->label('Trabajador')
                ->required(),
            Forms\Components\Select::make('id_proyecto')
                ->options(Proyect::query()->pluck('titulo', 'id'))
                ->live()
                ->searchable()
                ->label('Proyecto')
                ->columnSpanFull(),
            Forms\Components\TextInput::make('valor_dia'),
            // Forms\Components\Toggle::make('allDay')->default(true),
            Forms\Components\MarkdownEditor::make('detalles')
                ->columnSpanFull(),
            Forms\Components\Grid::make()
                ->schema([
                    Forms\Components\DateTimePicker::make('starts_at')
                        ->label('Inicio'),
                    Forms\Components\DateTimePicker::make('ends_at')
                        ->label('Fin'),
                ]),
            Forms\Components\Hidden::make('user_id')
        ];
    }

    protected function viewAction(): Action
    {
        return Actions\ViewAction::make();
    }

    public function fetchEvents(array $fetchInfo): array
    {
        return Binnacle::query()
            ->where('starts_at', '>=', $fetchInfo['start'])
            ->where('ends_at', '<=', $fetchInfo['end'])
            ->get()
            ->map(function (Binnacle $binnacle) {
                $name = explode(" ", $binnacle->worker->nombre);
                $initials = null;
                foreach ($name as $i) {
                    $initials .= $i[0] . '.';
                }
                return [
                    'id' => $binnacle->id,
                    'title' => $initials . " - " . ($binnacle->proyect ? $binnacle->proyect->titulo : $binnacle->detalles),
                    'start' => $binnacle->starts_at,
                    'end' => $binnacle->ends_at,
                    'color' => 'danger',
                    'time' => '23:59:59',
                    // 'allDay' => true,
                    // 'url' => BinnacleResource::getUrl(name: 'view', parameters: ['record' => $binnacle]),
                    // 'shouldOpenUrlInNewTab' => true
                ];
            })
            ->all();
    }
    protected function headerActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nuevo registro')
                ->mountUsing(
                    function (Forms\Form $form, array $arguments) {
                        $form->fill([
                            'time' => '23:59:59',
                            'starts_at' => $arguments['start'] ?? null,
                            'ends_at' => $arguments['end'] ?? null,
                            // 'allDay' => true,
                            'user_id' => auth()->user()->id,
                            'valor_dia' => collect(app(GeneralSettings::class)->variables)->pluck('value', 'key')['valor_dia'],
                        ]);
                    }
                )
                ->successRedirectUrl(route('filament.apolo.resources.bitacora.index'))
                ->button()

        ];
    }

    protected function modalActions(): array
    {
        return [
            Actions\EditAction::make()
                ->mountUsing(
                    function (Binnacle $record, Forms\Form $form, array $arguments) {
                        $form->fill([
                            'id_trabajador' => $record->id_trabajador,
                            'id_proyecto' => $record->id_proyecto,
                            'detalles' => $record->detalles,
                            // 'allDay' => true,
                            'user_id' => auth()->user()->id,
                            'starts_at' => $arguments['event']['start'] ?? $record->starts_at,
                            'ends_at' => $arguments['event']['end'] ?? $record->ends_at,
                            'valor_dia' => $arguments['event']['valor_dia'] ?? $record->valor_dia
                        ]);
                    }
                )
                ->successRedirectUrl(route('filament.apolo.resources.bitacora.index')),
            Actions\DeleteAction::make()
                ->successRedirectUrl(route('filament.apolo.resources.bitacora.index')),
        ];
    }

    public function eventDidMount(): string
    {
        return <<<JS
        function({ event, timeText, isStart, isEnd, isMirror, isPast, isFuture, isToday, el, view }){
            el.setAttribute("x-tooltip", "tooltip");
            el.setAttribute("x-data", "{ tooltip: '"+event.title+"' }");
        }
    JS;
    }
}
