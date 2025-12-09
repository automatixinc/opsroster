<x-form-section submit="">
    <x-slot name="title">
        Payment method
    </x-slot>

    <x-slot name="description">
        Change your payment method.
    </x-slot>

    <x-slot name="form">
        <div class="overflow-hidden col-span-6 rounded-md">
            <div class="py-5 bg-white dark:bg-gray-800">
                <div>
                    <h4 class="font-medium text-gray-900 dark:text-gray-300">Current payment method</h4>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        {{ $paymentMethodMessage }}
                    </p>
                </div>
                @if ($tenantCanUseStripe)
                <div class="hidden sm:block">
                    <div class="py-4">
                        <div class="border-t border-gray-200 dark:border-gray-600"></div>
                    </div>
                </div>
                <x-label for="card-holder-name" class="mt-4 sm:mt-0" value="Card holder name"/>

                <div class="mt-1 relative rounded-md shadow-sm">
                    <x-input id="card-holder-name" class="w-full" type="text" placeholder="Taylor Otwell"/>
                </div>

                <!-- Stripe Elements Placeholder -->
                <div class="mt-2 relative rounded-md shadow-sm" wire:ignore>
                    <div id="card-element" class="form-input py-3 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm mt-1 block w-full"></div>
                </div>
                <p id="payment-method-error-message" class="text-sm"></p>
                @endif
            </div>
        </div>
    </x-slot>

    <x-slot name="actions">
        @if ($tenantCanUseStripe)
            <x-action-message on="saved" class="me-3">
                Saved.
            </x-action-message>

            <x-button type="button" id="card-button" data-secret="{{ $intent->client_secret }}">Update payment method</x-button>
        @endif
    </x-slot>
</x-form-section>

@assets
<script src="https://js.stripe.com/v3/"></script>
@endassets

@script
<script>
    const tenantCanUseStripe = {{ $tenantCanUseStripe ? 1 : 0 }};

    if (tenantCanUseStripe) {
        const stripe = Stripe('{{ $stripeKey }}');

        const isDark = (window.matchMedia('(prefers-color-scheme: dark)').matches && localStorage.theme != 'light')
        || localStorage.theme == 'dark';

        const options = isDark ? {
            style: {
                base: {
                    color: '#d1d5db',
                    '::placeholder': {
                        color: '#6b7280'
                    }
                }
            }
        } : {}

        const elements = stripe.elements();
        const cardElement = elements.create('card', options);

        cardElement.mount('#card-element');

        const cardHolderName = document.getElementById('card-holder-name');
        const cardButton = document.getElementById('card-button');
        const clientSecret = cardButton.dataset.secret;

        cardButton.addEventListener('click', async (e) => {
            const { setupIntent, error } = await stripe.confirmCardSetup(
            clientSecret, {
                payment_method: {
                    card: cardElement,
                    billing_details: { name: cardHolderName.value }
                }
            }
            );

            if (error) {
                document.getElementById('payment-method-error-message').innerHTML = error.message;
                document.getElementById('payment-method-error-message').classList = 'text-sm mt-4 text-red-500';
            } else {
                @this.set('paymentMethod', setupIntent.payment_method);
                @this.call('save');
            }
        });
    }
</script>
@endscript
