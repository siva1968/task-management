<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantContext
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = $this->resolveTenant($request);

        if ($tenant) {
            // Set tenant in app container for easy access
            app()->instance('tenant', $tenant);

            // Share tenant with views
            view()->share('currentTenant', $tenant);

            // If user is authenticated, verify they belong to this tenant
            if (auth()->check() && auth()->user()->tenant_id !== $tenant->id) {
                auth()->logout();
                return redirect()->route('login')
                    ->withErrors(['error' => 'Unauthorized access to this tenant.']);
            }
        }

        return $next($request);
    }

    /**
     * Resolve tenant from request.
     */
    protected function resolveTenant(Request $request): ?Tenant
    {
        // Try to get tenant from header (for API requests)
        if ($request->header('X-Tenant-ID')) {
            $tenant = Tenant::find($request->header('X-Tenant-ID'));
            if ($tenant && $tenant->isActive()) {
                return $tenant;
            }
        }

        // Try to get tenant from subdomain
        $host = $request->getHost();
        $subdomain = $this->getSubdomain($host);

        if ($subdomain && $subdomain !== 'www') {
            $tenant = Tenant::where('subdomain', $subdomain)
                ->where('status', 'active')
                ->first();

            if ($tenant) {
                return $tenant;
            }
        }

        // Try to get tenant from custom domain
        $tenant = Tenant::where('domain', $host)
            ->where('status', 'active')
            ->first();

        if ($tenant) {
            return $tenant;
        }

        // If user is authenticated, use their tenant
        if (auth()->check() && auth()->user()->tenant) {
            return auth()->user()->tenant;
        }

        return null;
    }

    /**
     * Extract subdomain from host.
     */
    protected function getSubdomain(string $host): ?string
    {
        $parts = explode('.', $host);

        // If localhost or IP address, no subdomain
        if (count($parts) < 3 || filter_var($host, FILTER_VALIDATE_IP)) {
            return null;
        }

        return $parts[0];
    }
}
