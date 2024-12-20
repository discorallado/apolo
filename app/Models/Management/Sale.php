<?php

namespace App\Models\Management;

use App\Models\User;
use App\Settings\GeneralSettings;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Sale extends Model implements HasMedia
{

    use InteractsWithMedia;
    use HasFactory;
    use SoftDeletes;

    protected $table = 'man_ventas';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'folio',
        'fecha_dcto',
        'tipo_doc',
        'id_proyecto',
        'id_cliente',
        'id_movimiento',
        'exento',
        'neto',
        'iva',
        'total',
        'user_id',
        // 'periodo',
        // 'ano',
    ];

    protected $casts = [
        'sale_files' => 'array',
    ];

    public function getDTOAttribute()
    {
        // return strtoupper(app(GeneralSettings::class)->comunas[$this->id_ciudad]);
        // return  strtoupper(GeneralSettings->comunas[(int)$this->ciudad]);
        $arreglo = collect(app(GeneralSettings::class)->codigos_dt)->pluck('label', 'code');

        return  strtoupper($arreglo[$this->tipo_doc]);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'id_cliente');
    }

    public function proyect(): BelongsTo
    {
        return $this->belongsTo(Proyect::class, 'id_proyecto');
    }

    public function movement(): BelongsTo
    {
        return $this->belongsTo(Movement::class, 'id_movimiento');
    }
}
