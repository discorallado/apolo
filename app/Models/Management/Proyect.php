<?php

namespace App\Models\Management;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Tags\HasTags;

class Proyect extends Model
{
    use HasFactory;
    use SoftDeletes;
    use HasTags;

    protected $table = 'man_proyectos';
    protected $fillable = [
        'titulo',
        'detalle',
        'id_cliente',
        // 'id_proyecto',
        'monto_proyectado',
        'monto_final',
        'user_id',
        'estado',
    ];

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
