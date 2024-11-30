<?php

namespace App\Models\HR;

use App\Models\User;
use Guava\Calendar\Contracts\Resourceable;
use Guava\Calendar\ValueObjects\Resource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Worker extends Model implements Resourceable
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'hr_trabajadores';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre',
        'detalles',
        'color',
        'user_id',
    ];

    public function toResource(): array | Resource
    {
        return Resource::make($this->id)
            ->title($this->nombre)
        ;
    }

    public function getInitialsAttribute($value)
    {
        return implode('.', array_map(fn($item) => strtoupper(substr($item, 0, 1)), explode(' ', $this->nombre))) . '';
    }

    public function Binnacles(): HasMany
    {
        return $this->hasMany(Binnacle::class, 'id_trabajador');
    }
    public function User(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
