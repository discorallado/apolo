<?php

namespace App\Filament\Resources\HR\BinnacleResource\Widgets;

use App\Models\HR\Binnacle;
use App\Models\HR\Worker;

use App\Models\Management\Proyect;
use Carbon\Carbon;
use Closure;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Guava\Calendar\Actions\CreateAction;
use Guava\Calendar\Widgets\CalendarWidget;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;
use Pelmered\FilamentMoneyField\Forms\Components\MoneyInput;

class CalendarWidget2 extends CalendarWidget
{
    protected bool $eventClickEnabled = true;

    protected bool $eventDragEnabled = true;

    protected bool $eventResizeEnabled = true;

    protected bool $dateClickEnabled = true;

    protected bool $dateSelectEnabled = true;

    protected string | Closure | HtmlString | null $heading = 'Calendario';

    protected string $calendarView = 'dayGridMonth';

    public function getEvents(array $fetchInfo = []): Collection | array
    {
        return collect()
            ->push(...Binnacle::query()->get());
    }

    public function getResources(): Collection | array
    {
        return collect()
            ->push(...Worker::query()->get())
            ->push(...Proyect::query()->get());
    }

    public function getSchema(?string $model = null): ?array
    {
        return [
            Forms\Components\Select::make('id_trabajador')
                ->relationship('worker', 'nombre')
                ->options(Worker::query()->pluck('nombre', 'id'))
                ->live()
                ->searchable()
                ->label('Trabajador')
                ->required()
                ->afterStateUpdated(function (int $state, Set $set) {
                    $set('valor_dia', Worker::where('id', '=', $state)->first()->valor_dia);
                }),
            MoneyInput::make('valor_dia'),
            Forms\Components\Select::make('id_proyecto')
                ->options(Proyect::query()->pluck('titulo', 'id'))
                ->live()
                ->searchable()
                ->label('Proyecto')
                ->columnSpanFull(),
            Forms\Components\RichEditor::make('detalles')
                ->columnSpanFull(),
            Forms\Components\Grid::make()
                ->schema([
                    Forms\Components\DatePicker::make('starts_at')
                        ->default(now())
                        ->label('Inicio'),
                    Forms\Components\DatePicker::make('ends_at')
                        // ->default(now())
                        ->label('Fin'),
                ]),
        ];
    }

    public function getHeaderActions(): array
    {
        return [
            CreateAction::make('createBinnacle')
                ->label('Nuevo registro')
                ->model(Binnacle::class)
                ->mutateFormDataUsing(function (array $data): array {
                    $data['starts_at'] = Carbon::make($data['starts_at'])->startOfDay();
                    $data['ends_at'] = Carbon::make($data['ends_at'])->startOfDay();
                    $data['user_id'] = Auth()->id();
                    // dd($data);
                    return $data;
                }),
        ];
    }

    public function getEventClickContextMenuActions(): array
    {
        return [
            $this->editAction()
                ->mutateFormDataUsing(function (array $data): array {
                    $data['starts_at'] = Carbon::parse($data['starts_at'])->startOfDay();
                    $data['ends_at'] = Carbon::parse($data['ends_at'])->startOfDay();
                    $data['user_id'] = Auth()->id();
                    // dd($data);
                    return $data;
                }),
            $this->deleteAction()
                ->requiresConfirmation(),
        ];
    }

    // Este es un método personalizado, solo para evitar duplicar el código
    private function getDateContextMenuActions(): array
    {
        return [
            CreateAction::make('ctxCreateBinnacle')
                ->label('Crear registro')
                ->model(Binnacle::class)
                ->mountUsing(function (Form $form, array $arguments) {
                    // dd($arguments);
                    // $projectId = data_get($arguments, 'resource.id');
                    $date = data_get($arguments, 'dateStr');
                    // dd($date);
                    $startsAt = Carbon::make(data_get($arguments, 'startStr', $date));
                    $endsAt = Carbon::make(data_get($arguments, 'endStr', $date))->subDay(1);
                    // dd([$startsAt, $endsAt]);
                    if ($endsAt->diffInMinutes($startsAt) == 0) {
                        $endsAt->addMinutes(30);
                    }
                    if ($startsAt && $endsAt) {
                        $form->fill([
                            // 'id_trabajador' => $projectId,
                            'starts_at' => $startsAt,
                            'ends_at' => $endsAt,
                        ]);
                    }
                })
                ->mutateFormDataUsing(function (array $data): array {
                    $data['starts_at'] = Carbon::make($data['starts_at'])->startOfDay();
                    $data['ends_at'] = Carbon::make($data['ends_at'])->startOfDay();
                    $data['user_id'] = Auth()->id();
                    // dd($data);
                    return $data;
                }),
        ];
    }

    public function getDateClickContextMenuActions(): array
    {
        return $this->getDateContextMenuActions();
    }

    public function getDateSelectContextMenuActions(): array
    {
        return $this->getDateContextMenuActions();
    }

    public function onEventDrop(array $info = []): bool
    {
        parent::onEventDrop($info);
        if (in_array($this->getModel(), [Binnacle::class])) {
            $record = $this->getRecord();
            if ($delta = data_get($info, 'delta')) {
                $startsAt = $record->starts_at;
                $endsAt = $record->ends_at;
                $startsAt->addSeconds(data_get($delta, 'seconds'))->startOfDay();
                $endsAt->addSeconds(data_get($delta, 'seconds'))->startOfDay();
                $record->update([
                    'starts_at' => $startsAt,
                    'ends_at' => $endsAt,
                ]);

                Notification::make()
                    ->title('Bitacora modificada')
                    ->success()
                    ->send();
            }
            return true;
        }
        return false;
    }

    public function onEventResize(array $info = []): bool
    {
        parent::onEventResize($info);
        if ($this->getModel() === Binnacle::class) {
            $record = $this->getRecord();
            if ($delta = data_get($info, 'endDelta')) {
                $endsAt = $record->ends_at;
                $endsAt->addSeconds(data_get($delta, 'seconds'));
                $record->update([
                    'ends_at' => $endsAt->startOfDay(),
                ]);
            }
            Notification::make()
                ->title('Bitacora modificada')
                ->success()
                ->send();
            return true;
        }
        Notification::make()
            ->title('El rango de tiempo no puede ser modificado')
            ->danger()
            ->send();
        return false;
    }

    // public function getOptions(): array
    // {
    //     return [
    //         'slotMinTime' => '08:00:00',
    //         'slotMaxTime' => '16:00:00',
    //     ];
    // }

    public function authorize($ability, $arguments = [])
    {
        return true;
    }
}
