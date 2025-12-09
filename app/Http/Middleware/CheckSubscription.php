<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckSubscription
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if (! tenant()->can_use_app) {
            if ($request->user()->isOwner()) {
                return redirect(route('tenant.settings.billing'));
            } else {
                return response()->view('tenant.errors.expired-subscription');
            }
        }

        return $next($request);
    }
}
