<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Domain;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RemoveDomainFromPloi implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        protected Domain $domain,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->domain->certificate_status === 'issued') {
            ploi()->revokeCertificate($this->domain);
        }

        ploi()->removeDomain($this->domain);
    }
}
