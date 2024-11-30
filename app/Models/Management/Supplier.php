<?php

namespace App\Models\Management;

use App\Models\User;
use App\Settings\GeneralSettings;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Supplier extends Model implements HasMedia
{

	use InteractsWithMedia;
	use HasFactory;
	use SoftDeletes;

	protected $table = 'man_proveedores';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array<int, string>
	 */
	protected $fillable = [
		'rut',
		'nombre',
		'direccion',
		'id_ciudad',
		'telefono',
		'email',
		'giro'
	];

	protected $casts = [
		'supplier_files' => 'array',
	];
	public function getCiudadAttribute()
	{
		// dd(app(GeneralSettings::class));
		return  strtoupper(app(GeneralSettings::class)->comunas[$this->id_ciudad]);
	}

	public function getNombreAttribute($value)
	{
		return strtoupper($value);
	}

	public function getDireccionAttribute($value)
	{
		return strtoupper($value);
	}

	public function getGiroAttribute($value)
	{
		return strtoupper($value);
	}

	public function user(): BelongsTo
	{
		return $this->belongsTo(User::class, 'user_id');
	}

	public function Purchases(): HasMany
	{
		return $this->hasMany(Purchase::class, 'id_proveedor');
	}
}
