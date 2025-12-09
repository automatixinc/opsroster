<?php

declare(strict_types=1);

namespace App\Livewire\Tenant;

use App\BillingManager;
use App\Models\SubscriptionCancelation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Illuminate\Contracts\View\View;

class SubscriptionPlan extends Component
{
    public $plan = '';

    public $success = '';

    public $error = '';

    public $cancelModalOpen = false;

    public $cancelationReason = '';

    public $otherReason = '';

    protected $listeners = ['saved' => '$refresh'];

    public function mount(): void
    {
        $this->refreshPlan();
    }

    public function canCancel(): bool
    {
        if ($this->cancelationReason === 'Other') {
            return $this->otherReason !== '';
        }

        return $this->cancelationReason !== '';
    }

    public function updatePlan(): void
    {
        $this->validate([
            'plan' => ['required', Rule::in(array_keys(config('saas.plans')))],
        ]);

        if (! tenant()->hasDefaultPaymentMethod()) {
            $this->error = 'No payment method set. Please add one below.';

            return;
        }

        if (tenant()->subscribed()) {
            tenant()->subscription()->swap($this->plan);

            $this->success = 'Updated.';
            $this->error = '';
        } else {
            $subscription = tenant()->newSubscription('default', $this->plan);

            /** @var Carbon $trial_end */
            $trial_end = tenant()->trial_ends_at;

            if (config('saas.trial_days') && $trial_end->isFuture()) {
                $subscription->trialUntil($trial_end);
            }

            $subscription->create();

            $this->success = 'Created.';
            $this->error = '';
        }

        $this->dispatch('saved');
    }

    public function cancel(): void
    {
        $cancelationReason = $this->cancelationReason === 'Other' ? $this->otherReason : $this->cancelationReason;

        $this->cancelModalOpen = false;

        DB::transaction(function () use ($cancelationReason) {
            tenant()->subscription()->cancel();

            SubscriptionCancelation::create([
                'tenant_id' => tenant()->id,
                'reason' => $cancelationReason,
            ]);
        });

        $this->success = 'Canceled.';
        $this->plan = '';

        $this->dispatch('saved');
    }

    public function resume(): void
    {
        tenant()->subscription()->resume();

        $this->refreshPlan();
        $this->success = 'Resumed.';

        $this->dispatch('saved');
    }

    protected function refreshPlan(): void
    {
        if (tenant()->on_active_subscription) {
            $this->plan = tenant()->subscription()->stripe_price;
        }
    }

    public function render(): View
    {
        return view('livewire.tenant.subscription-plan', BillingManager::getSubscriptionPlanProps(tenant()));
    }
}
