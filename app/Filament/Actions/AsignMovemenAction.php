<?php

namespace App\Filament\Actions;

use Filament\Forms\Components\Actions\Action;
use Filament\Support\Enums\ActionSize;
use Illuminate\Database\Eloquent\Model;

class AsignMovementAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'asignMovement';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->iconButton()->icon('heroicon-o-arrow-path-rounded-square')->color('gray');

        $this->label('Asignar a movimiento');

        $this->livewireClickHandlerEnabled(false);

        $this->size(ActionSize::Small);

        // $this->form();

        $this->action(function (Model $record) {
            return $record;
        });
    }
}
