<?php

declare(strict_types=1);

namespace App;

use App\Models\Domain;
use App\Models\Tenant;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class DomainManager
{
    public static function getDomains(Tenant $tenant): Collection
    {
        return $tenant->domains->sortBy('domain')->sortByDesc('is_fallback');
    }

    public static function domainValidationRules(Tenant $tenant): array
    {
        return [
            'domain' => [
                'required',
                'string',
                'unique:central.domains',
                'regex:/^[A-Za-z0-9]+[A-Za-z0-9.-]+[A-Za-z0-9]+$/',
                'regex:/\\./', // Must contain a dot
                function ($attribute, $value, $fail) use ($tenant) {
                    $centralDomain = config('tenancy.identification.central_domains')[0];

                    if ($centralDomain !== 'localhost' && Str::endsWith($value, $centralDomain)) {
                        $fail($attribute.' must be a custom domain.');
                    }

                    // This is only an issue when using localhost as central domain. Other cases are handled above.
                    if (Str::endsWith($value, '.localhost') && $tenant->fallback_domain->domain === Str::before($value, '.localhost')) {
                        $fail('Localhost domain conflicts with the current fallback domain.');
                    }
                },
            ],
        ];
    }

    public static function fallbackValidationRules(Domain $oldFallback): array
    {
        return [
            'domain' => [
                'required',
                'string',
                Rule::unique('central.domains')->ignoreModel($oldFallback),
                'regex:/^[A-Za-z0-9-]+$/',
                Rule::notIn(config('saas.reserved_subdomains')),
            ],
        ];
    }

    public static function createDomain(string $domain, Tenant $tenant): Domain
    {
        return $tenant->createDomain($domain);
    }

    public static function storeFallback(string $domain, Tenant $tenant)
    {
        DB::transaction(function () use ($domain, $tenant) {
             if ($domain === $tenant->fallback_domain->domain) {
                return; // No action
            }

            $oldFallback = $tenant->fallback_domain;

            $newFallback = static::createDomain($domain, $tenant)->makeFallback();

            // We'll be deleting the old fallback domain. So if it was
            // the primary domain, we'll make the one one primary
            if ($oldFallback->is_primary) {
                $newFallback->makePrimary();
            }

            // In our setup, we only allow tenants to have one subdomain.
            // We don't want them squatting multiple subdomains.
            $oldFallback->delete();
        });
    }

    public static function makePrimary(Domain $domain)
    {
        $domain->makePrimary();
    }

    public static function delete(Domain $domain)
    {
        $domain->delete();
    }

    public static function requestCertificate(Domain $domain)
    {
        ploi()->requestCertificate($domain);
    }

    public static function revokeCertificate(Domain $domain)
    {
        ploi()->revokeCertificate($domain);
    }
}
