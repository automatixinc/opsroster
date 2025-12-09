<?php

namespace App;

use App\Models\Domain;
use Illuminate\Config\Repository;
use Illuminate\Support\Facades\Http;

class PloiManager
{
    protected string $site;
    protected string $token;
    protected string $server;

    public function __construct(Repository $config)
    {
        $this->site = $config->get('services.ploi.site');
        $this->token = $config->get('services.ploi.token');
        $this->server = $config->get('services.ploi.server');
    }

    /** Add a tenant :80 vhost. */
    public function addDomain(Domain $domain): bool
    {
        if ($domain->isSubdomain() || ! $this->token) {
            return false;
        }

        // Make sure the domain points to our app
        if (gethostbyname($domain->domain) !== gethostbyname(Domain::domainFromSubdomain(config('tenancy.identification.central_domains')[0]))) {
            return false;
        }

        Http::withToken($this->token)->asJson()->acceptJson()
            ->post("https://ploi.io/api/servers/{$this->server}/sites/{$this->site}/tenants", [
                'tenants' => [$domain->domain],
            ]
        );

        return true;
    }

    /** Remove a tenant :80 host. */
    public function removeDomain(Domain $domain): bool
    {
        if ($domain->isSubdomain() || ! $this->token) {
            return false;
        }

        Http::withToken($this->token)->asJson()->acceptJson()
            ->delete("https://ploi.io/api/servers/{$this->server}/sites/{$this->site}/tenants/$domain->domain");

        return true;
    }

    /** Request a certificate for a tenant host. */
    public function requestCertificate(Domain $domain): bool
    {
        if ($domain->isSubdomain() || ! $this->token) {
            return false;
        }

        Http::withToken($this->token)->asJson()->acceptJson()
            ->post("https://ploi.io/api/servers/{$this->server}/sites/{$this->site}/tenants/{$domain->domain}/request-certificate", [
                'webhook' => $domain->tenant->route('tenant.ploi.certificate.issued'),
            ]
        );

        $domain->update(['certificate_status' => 'pending']);

        return true;
    }

    /** Revoke a certificate for a tenant host. */
    public function revokeCertificate(Domain $domain): bool
    {
        if ($domain->isSubdomain() || ! $this->token) {
            return false;
        }

        Http::withToken($this->token)->asJson()->acceptJson()
            ->post("https://ploi.io/api/servers/{$this->server}/sites/{$this->site}/tenants/{$domain->domain}/revoke-certificate", [
                'webhook' => $domain->tenant->route('tenant.ploi.certificate.revoked'),
            ]
        );

        $domain->update(['certificate_status' => 'pending']);

        return true;
    }

    /** Let Ploi know about a tenant's database. */
    public function acknowledgeDatabase(string $databaseName): bool
    {
        if (! $this->token) {
            return false;
        }

        Http::withToken($this->token)->asJson()->acceptJson()
            ->post("https://ploi.io/api/servers/{$this->server}/databases/acknowledge", [
                'name' => $databaseName,
                'description' => 'Tenant database',
            ]
        );

        // Create a backup if you want: https://developers.ploi.io/database-backups/create-backup

        return true;
    }

    /** Make Ploi forget a tenant database. */
    public function forgetDatabase(string $databaseName): bool
    {
        if (! $this->token) {
            return false;
        }

        Http::withToken($this->token)->asJson()->acceptJson()
            ->delete("https://ploi.io/api/servers/{$this->server}/databases/$databaseName/forget");

        return true;
    }
}
