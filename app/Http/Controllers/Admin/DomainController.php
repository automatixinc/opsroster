<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Models\Tenant;
use Illuminate\Http\Request;
use App\Models\Domain;
use App\DomainManager;

class DomainController
{
    public function store(Request $request, Tenant $tenant)
    {
        $data = $request->validate(DomainManager::domainValidationRules($tenant));

        DomainManager::createDomain($data['domain'], $tenant);
    }

    public function destroy(Domain $domain)
    {
        abort_if($domain->is_primary || $domain->is_fallback, 403);

        DomainManager::delete($domain);
    }

    public function makePrimary(Domain $domain)
    {
        DomainManager::makePrimary($domain);
    }

    public function storeFallback(Request $request, Tenant $tenant)
    {
        $data = $request->validate(DomainManager::fallbackValidationRules($tenant->fallback_domain));

        DomainManager::storeFallback($data['domain'], $tenant);
    }

    public function requestCertificate(Tenant $tenant, Domain $domain)
    {
        abort_unless($domain->tenant->is($tenant), 403);

        DomainManager::requestCertificate($domain);
    }

    public function revokeCertificate(Tenant $tenant, Domain $domain)
    {
        abort_unless($domain->tenant->is($tenant), 403);

        DomainManager::revokeCertificate($domain);
    }
}
