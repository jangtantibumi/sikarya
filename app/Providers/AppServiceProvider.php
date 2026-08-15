<?php

namespace App\Providers;

use App\Models\ApprovalRequest;
use App\Models\Company;
use App\Models\CrmCustomer;
use App\Models\Goal;
use App\Models\KpiPlan;
use App\Models\LeaveRequest;
use App\Models\Task;
use App\Observers\CrmCustomerObserver;
use App\Policies\ApprovalPolicy;
use App\Policies\CompanyPolicy;
use App\Policies\GoalPolicy;
use App\Policies\KpiPolicy;
use App\Policies\LeavePolicy;
use App\Policies\TaskPolicy;
use App\Services\TenantContext;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiting\Limit;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->scoped(TenantContext::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(ApprovalRequest::class, ApprovalPolicy::class);
        Gate::policy(Company::class, CompanyPolicy::class);
        Gate::policy(Goal::class, GoalPolicy::class);
        Gate::policy(KpiPlan::class, KpiPolicy::class);
        Gate::policy(LeaveRequest::class, LeavePolicy::class);
        Gate::policy(Task::class, TaskPolicy::class);

        // CRM Observers
        CrmCustomer::observe(CrmCustomerObserver::class);

        // Security: Strict Mode (Prevents lazy loading, mass assignment vulnerabilities)
        Model::shouldBeStrict(! $this->app->isProduction());

        // Security: API Rate Limiter
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Security: Login Bruteforce Protection (Max 5 attempts per username+IP)
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->input('email', $request->ip()) . '|' . $request->ip());
        });

        // UI/UX: Global Decimal & Currency Formatter
        \Illuminate\Support\Facades\Blade::directive('decimal', function ($expression) {
            return "<?php echo number_format((float)($expression), 2, ',', '.'); ?>";
        });

        \Illuminate\Support\Facades\Blade::directive('currency', function ($expression) {
            return "<?php echo 'Rp ' . number_format((float)($expression), 0, ',', '.'); ?>";
        });
    }
}
