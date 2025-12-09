<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Actions\CreateTenantAction;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RegisterTenantController
{
    use ValidatesRequests;

    public function show()
    {
        return view('central.register', [
            'centralDomain' => config('tenancy.identification.central_domains')[0],
        ]);
    }

    public function submit(Request $request)
    {
        $data = $request->validate([
            'domain' => [
                'required',
                'string',
                'unique:domains',
                Rule::notIn(config('saas.reserved_subdomains')),
                'regex:/^[A-Za-z0-9-]+$/',
            ],
            'company' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:tenants',
            'password' => 'required|string|confirmed|max:255',
        ]);

        $data['password'] = bcrypt($data['password']);

        $domain = $data['domain'] ?? null;
        unset($data['domain']);

        $tenant = (new CreateTenantAction)($data, $domain);

        // Impersonate the admin user created by the CreateTenantAdmin job
        return redirect($tenant->impersonationUrl($tenant->getAdmin()->id));
    }
}
