<?php

namespace App\Models\HR;

use App\Models\Management\Proyect;
use Guava\Calendar\Contracts\Eventable;
use Guava\Calendar\ValueObjects\Event;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Binnacle extends Model implements Eventable
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'hr_bitacoras';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'starts_at',
        'ends_at',
        'detalles',
        'id_trabajador',
        'id_proyecto',
        'valor_dia',
        'user_id'
    ];
    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function toEvent(): Event|array
    {
        if (!isset($this->relations['Worker'])) {
            $this->load('worker');
        }
        $initials = implode('.', array_map(fn($item) => strtoupper(substr($item, 0, 1)), explode(' ', $this->Worker()->first()->nombre))) . '.';
        // dd($initials);
        $titulo = $initials . ' — ' . $this->Proyect()->first()->titulo;
        return Event::make($this)
            ->title($titulo)
            ->start($this->starts_at)
            ->backgroundColor($this->Worker()->first()->color)
            ->end($this->ends_at);
    }

    public function getCustomAttribute($value)
    {
        if (!isset($this->relations['Worker'])) {
            $this->load('worker');
        }
        if (!isset($this->relations['Proyect'])) {
            $this->load('proyect');
        }
        return $this->Worker()->first()->initials . ' — ' . $this->Proyect()->first()->titulo;
    }

    public function Worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class, 'id_trabajador');
    }

    public function Proyect(): BelongsTo
    {
        return $this->belongsTo(Proyect::class, 'id_proyecto');
    }

    public function Payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'id_bitacora');
    }
}
