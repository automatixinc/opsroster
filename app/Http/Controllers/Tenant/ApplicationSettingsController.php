<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

class ApplicationSettingsController
{
    public function show()
    {
        return view('tenant.settings.application');
    }
}
