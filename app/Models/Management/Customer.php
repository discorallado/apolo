<?php

namespace App\Models\Management;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Customer extends Model implements HasMedia
{

    use InteractsWithMedia;
    use HasFactory;
    use SoftDeletes;

    protected $table = 'man_clientes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'rut',
        'nombre',
        'direccion',
        'ciudad',
        'telefono',
        'giro'
    ];

    public function User(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function Sales(): HasMany
    {
        return $this->hasMany(Sale::class, 'id_cliente');
    }

    public function Proyects(): HasMany
    {
        return $this->hasMany(Proyect::class, 'id_cliente');
    }
}
