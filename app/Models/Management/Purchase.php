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

class Purchase extends Model implements HasMedia
{

    use InteractsWithMedia;
    use HasFactory;
    use SoftDeletes;

    protected $table = 'man_compras';

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
        'id_proveedor',
        'neto',
        'iva',
        'total',
        'user_id',
    ];

    protected $casts = [
        'purchase_files' => 'array',
    ];

    public function getDTOAttribute()
    {
        $arreglo = collect(app(GeneralSettings::class)->codigos_dt)->pluck('label', 'code');

        return  strtoupper($arreglo[$this->tipo_doc]);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function proyect(): BelongsTo
    {
        return $this->belongsTo(Proyect::class, 'id_proyecto');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'id_cliente');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'id_proveedor');
    }
}
