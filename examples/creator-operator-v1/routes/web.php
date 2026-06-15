<?php

use App\Http\Controllers\Creator\ApprovalController as CreatorApprovalController;
use App\Http\Controllers\Creator\ReportsController as CreatorReportsController;
use App\Http\Controllers\Creator\SettlementController as CreatorSettlementController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Operator\BillingController as OperatorBillingController;
use App\Http\Controllers\Operator\CreatorController as OperatorCreatorController;
use App\Http\Controllers\Operator\DashboardController as OperatorDashboardController;
use App\Http\Controllers\Operator\IntegrationWebhookController as OperatorIntegrationWebhookController;
use App\Http\Controllers\Operator\MonthlySettlementController as OperatorMonthlySettlementController;
use App\Http\Controllers\Operator\PublishLogController as OperatorPublishLogController;
use App\Http\Controllers\Operator\TikTokImportController as OperatorTikTokImportController;
use App\Http\Controllers\Operator\WeeklyMetricController as OperatorWeeklyMetricController;
use App\Http\Controllers\Operator\CsvExportController as OperatorCsvExportController;
use App\Http\Controllers\Settings\SubscriptionController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Laravel\Cashier\Http\Controllers\WebhookController as CashierWebhookController;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return view('welcome');
});

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'verified', 'role:operator'])->prefix('operator')->name('operator.')->group(function () {
    Route::get('/', OperatorDashboardController::class)->name('dashboard');
    Route::resource('creators', OperatorCreatorController::class)->except(['destroy']);
    Route::get('creators/{creator}/publish-log/create', [OperatorPublishLogController::class, 'create'])->name('creators.publish-log.create');
    Route::post('creators/{creator}/publish-log', [OperatorPublishLogController::class, 'store'])->name('creators.publish-log.store');
    Route::get('creators/{creator}/publish-log/{entry}/edit', [OperatorPublishLogController::class, 'edit'])->name('creators.publish-log.edit');
    Route::put('creators/{creator}/publish-log/{entry}', [OperatorPublishLogController::class, 'update'])->name('creators.publish-log.update');
    Route::post('creators/{creator}/publish-log/{entry}/publish', [OperatorPublishLogController::class, 'publish'])->name('creators.publish-log.publish');

    Route::get('creators/{creator}/metrics', [OperatorWeeklyMetricController::class, 'index'])->name('creators.metrics.index');
    Route::get('creators/{creator}/metrics/create', [OperatorWeeklyMetricController::class, 'create'])->name('creators.metrics.create');
    Route::post('creators/{creator}/metrics', [OperatorWeeklyMetricController::class, 'store'])->name('creators.metrics.store');

    Route::get('creators/{creator}/settlement', [OperatorMonthlySettlementController::class, 'index'])->name('creators.settlement.index');
    Route::get('creators/{creator}/settlement/create', [OperatorMonthlySettlementController::class, 'create'])->name('creators.settlement.create');
    Route::post('creators/{creator}/settlement', [OperatorMonthlySettlementController::class, 'store'])->name('creators.settlement.store');

    Route::get('creators/{creator}/import', [OperatorTikTokImportController::class, 'index'])->name('creators.import.index');
    Route::post('creators/{creator}/import/preview', [OperatorTikTokImportController::class, 'preview'])->name('creators.import.preview');
    Route::post('creators/{creator}/import/cli', [OperatorTikTokImportController::class, 'fetchCli'])->name('creators.import.cli');
    Route::post('creators/{creator}/import', [OperatorTikTokImportController::class, 'store'])->name('creators.import.store');

    Route::get('creators/{creator}/publish-log/export', [OperatorCsvExportController::class, 'publishLog'])->name('creators.publish-log.export');
    Route::get('creators/{creator}/settlement/export', [OperatorCsvExportController::class, 'settlement'])->name('creators.settlement.export');

    Route::get('billing', [OperatorBillingController::class, 'index'])->name('billing.index');
    Route::post('billing/plan', [OperatorBillingController::class, 'updatePlan'])->name('billing.plan');

    Route::get('integrations', [OperatorIntegrationWebhookController::class, 'index'])->name('integrations.index');
    Route::post('integrations', [OperatorIntegrationWebhookController::class, 'store'])->name('integrations.store');
    Route::delete('integrations/{webhook}', [OperatorIntegrationWebhookController::class, 'destroy'])->name('integrations.destroy');
    Route::post('integrations/{webhook}/test', [OperatorIntegrationWebhookController::class, 'test'])->name('integrations.test');
});

Route::middleware(['auth', 'verified', 'role:creator'])->prefix('creator')->name('creator.')->group(function () {
    Route::get('/approvals', [CreatorApprovalController::class, 'index'])->name('approvals.index');
    Route::post('/approvals/{entry}/approve', [CreatorApprovalController::class, 'approve'])->name('approvals.approve');
    Route::post('/approvals/{entry}/reject', [CreatorApprovalController::class, 'reject'])->name('approvals.reject');
    Route::get('/reports', [CreatorReportsController::class, 'index'])->name('reports.index');
    Route::get('/settlement', [CreatorSettlementController::class, 'index'])->name('settlement.index');
});

Route::middleware(['auth', 'verified', 'role:operator'])->group(function () {
    Route::get('/settings/subscription', [SubscriptionController::class, 'show'])->name('settings.subscription');
    Route::post('/settings/subscription/checkout', [SubscriptionController::class, 'checkout'])->name('settings.subscription.checkout');
    Route::post('/settings/subscription/portal', [SubscriptionController::class, 'portal'])->name('settings.subscription.portal');
});

Route::post('/stripe/webhook', [CashierWebhookController::class, 'handleWebhook'])->name('cashier.webhook');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
