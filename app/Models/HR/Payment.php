<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Payment extends Model implements HasMedia
{
    use HasFactory;
    use SoftDeletes;
    use InteractsWithMedia;

    protected $table = 'hr_pagos';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_bitacora',
        'fecha',
        'id_proyecto',
        'tipo',
        'monto',
        'user_id',
        'attachments',
        'attachment_file_names',
    ];

    protected $casts = [
        'attachments' => 'array',
        'attachment_file_names' => 'array',
    ];

    // protected $casts = [
    //     'payments_sum_monto' => 'int',
    // ];

    public function Binnacle(): BelongsTo
    {
        return $this->belongsTo(Binnacle::class, 'id_bitacora');
    }
}