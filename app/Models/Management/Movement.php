<?php

namespace App\Models\Management;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Movement extends Model implements HasMedia
{

    use InteractsWithMedia;
    use HasFactory;
    use SoftDeletes;

    protected $table = 'man_movimientos';

    protected $casts = [
        'movement_files' => 'array',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'tipo',
        'fecha',
        'cot',
        'monto_proyecto',
        'factura',
        'id_proyecto',
        'detalle',
        'cargo',
        'ingreso',
        'observaciones',
        'estado',
        'mes',
        'ano',
        'user_id',
        // 'uuid',
        // 'published_at',
        // 'is_published',
        // 'is_current',
        // 'publisher_type',
        // 'publisher_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function proyect(): BelongsTo
    {
        return $this->belongsTo(Proyect::class, 'id_proyecto');
    }

    public function Sale(): HasOne
    {
        return $this->hasOne(Sale::class, 'id_movimiento');
    }
}
