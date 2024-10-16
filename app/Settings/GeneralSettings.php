<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{

    //   public string $site_name;

    //   public bool $site_active;

    public string|array $codigos_dt;

    public string|array $comunas;

    public string|array $periodo_mes;

    public string|array $periodo_ano;

    public string|array $variables;

    public static function group(): string
    {
        return 'general';
    }
}