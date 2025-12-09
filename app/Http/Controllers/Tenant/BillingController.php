<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;


class BillingController
{
    public function show()
    {
        return view('tenant.settings.billing');
    }

    public function downloadInvoice($id)
    {
        // One approach is to generate invoices ourselves.
        // The disadvantage is that this doesn't show the Stripe client's billing address.

        // return tenant()->downloadInvoice($id, [
        //     'vendor' => 'Foo Inc.',
        //     'product' => 'An Amazing SaaS',
        // ]);

        // For that reason, we use Stripe invoices instead.
        // Feel free to customize this *completely*. This is just an example implementation.
        return redirect(tenant()->findInvoice($id)->asStripeInvoice()->invoice_pdf);
    }
}
