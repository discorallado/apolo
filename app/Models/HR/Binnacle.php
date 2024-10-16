<?php

namespace App\Models\HR;

use App\Models\Management\Proyect;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Binnacle extends Model
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
