<?php

namespace App\Models\Management;

use App\Models\User;
use Guava\Calendar\Contracts\Resourceable;
use Guava\Calendar\ValueObjects\Resource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Tags\HasTags;

class Proyect extends Model implements HasMedia, Resourceable
{

    use InteractsWithMedia;
    use HasFactory;
    use SoftDeletes;
    use HasTags;

    protected $table = 'man_proyectos';

    protected $fillable = [
        'titulo',
        'detalle',
        'id_cliente',
        'monto_proyectado',
        'monto_final',
        'user_id',
        'estado',
    ];

    protected $casts = [
        'proyect_files' => 'array',
    ];

    public function toResource(): array | Resource
    {
        return Resource::make($this->id)
            ->title($this->titulo_compuesto);
    }

    public function getTituloAttribute($value)
    {
        return strtoupper($value);
    }
    public function getDetalleAttribute($value)
    {
        return strtoupper($value);
    }
    public function getTituloCompuestoAttribute($value)
    {
        if (!isset($this->relations['customer'])) {
            $this->load('customer');
        }
        $cliente = $this->customer()->get();
        // dd($cliente->first()->nombre);
        return strtoupper($this->titulo) . ' - ' . strtoupper($cliente->first()->nombre);
    }
    public function getEstatusAttribute($value)
    {
        if (!isset($this->relations['movements'])) {
            $this->load('movements');
        }
        if ($this->estado) {
            return 'finalizado';
        } else {
            $relatedMovements = $this->movements()->get();
            if ($relatedMovements->count() > 0) {
                $relatedCargos = $relatedMovements->sum('cargo');
                $relatedIngresos = $relatedMovements->sum('ingreso');
                $diff = $relatedCargos - $relatedIngresos;
                if ($diff > 0) {
                    return 'activo';
                } else {
                    if ($relatedIngresos > 0) {
                        return 'finalizar';
                    } else {
                        return 'activo';
                    }
                }
            } else {
                return 'inactivo';
            }
        }
    }

    public function movements(): HasMany
    {
        return $this->hasMany(Movement::class, 'id_proyecto');
    }

    public function Sales(): HasMany
    {
        return $this->hasMany(Sale::class, 'id_proyecto');
    }

    public function Purchases(): HasMany
    {
        return $this->hasMany(Purchase::class, 'id_proyecto');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'id_cliente');
    }
}
