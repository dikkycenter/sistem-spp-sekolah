<?php

namespace App\Providers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Policies\InvoicePolicy;
use App\Policies\PaymentPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Invoice::class => InvoicePolicy::class,
        Payment::class => PaymentPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
