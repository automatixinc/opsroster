<x-form-section submit="">
    <x-slot name="title">
        Subscription plan
    </x-slot>

    <x-slot name="description">
        Update your subscription plan.
    </x-slot>

    <x-slot name="form">
        <div class="col-span-6">
            <x-dialog-modal wire:model.live="cancelModalOpen">
                <x-slot name="title">
                    Cancel Subscription
                </x-slot>

                <x-slot name="content">
                    <p class="text-sm text-gray-600 dark:text-gray-500">
                        We are sad to see you go. In order to improve our services, we would appreciate you taking a few moments to tell us why this product wasn't suited for you.
                    </p>
                    <select wire:model.live="cancelationReason" class="mt-3 form-select py-1 w-full dark:bg-gray-800 rounded-md">
                        <option value="" disabled>Select a cancelation reason</option>
                        @foreach (config('saas.cancelation_reasons') as $reason)
                            <option>{{ $reason }}</option>
                        @endforeach
                        <option>Other</option>
                    </select>

                    @if ($cancelationReason == 'Other')
                    <x-input
                        wire:model.live="otherReason"
                        type="text"
                        class="mt-2 form-input w-full"
                        placeholder="I'm canceling my subscription because ..."
                    />
                    @endif
                </x-slot>

                <x-slot name="footer">
                    <div class="flex justify-end space-x-2">
                        <!-- Confirm Cancelation Button -->
                        <x-danger-button :disabled="! $this->canCancel()" wire:click="cancel" :class="! $this->canCancel() ? 'opacity-50 cursor-not-allowed' : ''">
                            Cancel subscription
                        </x-danger-button>
                        <!-- Close Modal Button -->
                        <x-secondary-button wire:click="$set('cancelModalOpen', false)">
                            Close
                        </x-secondary-button>
                    </div>
                </x-slot>
            </x-dialog-modal>

            <div class="w-full overflow-hidden">
                <div class="pt-5 sm:rounded-md bg-white dark:bg-gray-800">
                    @foreach($plans as $code => $name)
                        <div class="
                        @if(! $loop->first)
                        mt-4
                        @endif
                        flex items-center p-1">
                            <input wire:model="plan" id="opt_{{ $code }}" name="subscription-plan" value="{{ $code }}" type="radio" class="form-radio h-4 w-4 text-indigo-600 transition duration-150 ease-in-out" />
                            <x-label for="opt_{{ $code }}" class="ml-3" value="{{ $name . ($currentPlan['name'] === $name && $currentPlan['canceled'] ? ' (most recently used)' : '') }}"/>
                        </div>
                    @endforeach
                </div>

                @error('plan')
                <p class="text-sm mt-4 text-red-500">
                    {{ $message }}
                </p>
                @enderror

                @if($error)
                <p class="text-sm mt-4 text-red-500">
                    {{ $error }}
                </p>
                @endif
            </div>
        </div>
    </x-slot>

    <x-slot name="actions">
        <div class="flex items-center justify-between">
            <div class="flex justify-end me-3 text-start text-ellipsis">
                <x-action-message on="saved">
                    {{ $success }}
                </x-action-message>
            </div>
            <div class="flex space-x-2">
                @if($onActiveSubscription)
                    <x-danger-button id="cancelSub" name="cancelSub" wire:click="$set('cancelModalOpen', true)">
                        Cancel
                    </x-danger-button>
                @elseif($subscribed && $currentPlan['canceled'])
                    <x-secondary-button id="resumeSub" name="resumeSub" wire:click="resume">
                        Resume
                    </x-secondary-button>
                @endif
                <x-button :disabled="! $tenantCanUseStripe" type="button" wire:click="updatePlan">Change</x-button>
            </div>
        </div>
    </x-slot>
</x-form-section>
