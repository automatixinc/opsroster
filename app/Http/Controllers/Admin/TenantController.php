<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Actions\CreateTenantAction;

class TenantController
{
    public function index()
    {
        $tenants = Tenant::orderByDesc('created_at')->paginate(5);

        return view('admin.tenants.index', [
            'tenants' => $tenants,
        ]);
    }

    public function update(Request $request, Tenant $tenant)
    {
        abort_if($tenant->pending(), 403);

        $data = $request->validate([
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('tenants')->ignore($tenant->id),
            ],
            'company' => 'required|string|max:255',
        ]);

        $tenant->update($data);

        return redirect()->route('admin.tenants.edit', $tenant)
            ->with('flash.banner', 'Tenant updated successfully');
    }

    public function create()
    {
        return view('admin.tenants.create', [
            'centralDomain' => config('tenancy.identification.central_domains')[0],
        ]);
    }

    public function store(Request $request)
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

        (new CreateTenantAction)($data, $domain);

        return redirect()->route('admin.tenants.index')
            ->with('flash.banner', 'Tenant created successfully');
    }

    public function destroy(Tenant $tenant)
    {
        $tenant->delete();

        return redirect()->route('admin.tenants.index')
            ->with('flash.banner', 'Tenant deleted successfully');
    }
}
