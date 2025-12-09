<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use Illuminate\Http\Request;
use App\Models\Tenant;

class LoginTenantController
{
    public function show()
    {
        return view('central.login');
    }

    public function submit(Request $request)
    {
        $request->validate([
            'email' => 'exists:tenants',
        ]);

        /** @var Tenant $tenant */
        $tenant = Tenant::where('email', $request->post('email'))->firstOrFail();

        return redirect($tenant->route('tenant.login'));
    }
}
