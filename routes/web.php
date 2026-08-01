<?php

use App\Http\Controllers\Admin\BillingController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\Admin\SubscriberController as AdminSubscriberController;
use App\Http\Controllers\Admin\TechnicalStaffController;
use App\Http\Controllers\Admin\TicketController as AdminTicketController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Staff\DashboardController as StaffDashboardController;
use App\Http\Controllers\Staff\TicketController as StaffTicketController;
use App\Http\Controllers\Subscriber\DashboardController as SubscriberDashboardController;
use Illuminate\Support\Facades\Route;

// eto bro yung salarin FIX 1 
Route::get('/', function () {
    return redirect()->route('login');
});

// Default fallback dashboard (redirects or shows general view if needed)
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware('auth')->name('dashboard');

/*
|--------------------------------------------------------------------------
| Account Settings (All Logged-in Users)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Admin Area (Role: Admin)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', AdminDashboardController::class)->name('dashboard');

    // Manage Technical Staff Accounts
    Route::prefix('technical-staff')->name('technical-staff.')->group(function () {
        Route::get('/', [TechnicalStaffController::class, 'index'])->name('index');
        Route::get('/create', [TechnicalStaffController::class, 'create'])->name('create');
        Route::post('/', [TechnicalStaffController::class, 'store'])->name('store');
    });

    // Manage Subscriber Accounts
    Route::prefix('subscribers')->name('subscribers.')->group(function () {
        Route::get('/', [AdminSubscriberController::class, 'index'])->name('index');
        Route::get('/create', [AdminSubscriberController::class, 'create'])->name('create');
        Route::post('/', [AdminSubscriberController::class, 'store'])->name('store');
    });

    // Manage Internet Plans
    Route::prefix('plans')->name('plans.')->group(function () {
        Route::get('/', [PlanController::class, 'index'])->name('index');
        Route::get('/create', [PlanController::class, 'create'])->name('create');
        Route::post('/', [PlanController::class, 'store'])->name('store');
        Route::delete('/{plan}', [PlanController::class, 'destroy'])->name('destroy');
    });

    // Invoices, Payments, & Billing Management
    Route::prefix('billing')->name('billing.')->group(function () {
        Route::get('/', [BillingController::class, 'index'])->name('index');
        Route::get('/overdue', [BillingController::class, 'overdue'])->name('overdue');
        Route::post('/generate', [BillingController::class, 'generate'])->name('generate');
        Route::post('/remind-all', [BillingController::class, 'remindAll'])->name('remind-all');
        Route::post('/{invoice}/mark-paid', [BillingController::class, 'markPaid'])->name('mark-paid');
        Route::post('/{invoice}/adjustments', [BillingController::class, 'storeAdjustment'])->name('adjustments.store');
        Route::get('/{invoice}/adjustments', [BillingController::class, 'adjustmentHistory'])->name('adjustments.history');
        Route::post('/{invoice}/remind', [BillingController::class, 'remind'])->name('remind');
    });

    // Trouble Tickets Management
    Route::prefix('tickets')->name('tickets.')->group(function () {
        Route::get('/', [AdminTicketController::class, 'index'])->name('index');
        Route::get('/create', [AdminTicketController::class, 'create'])->name('create');
        Route::post('/', [AdminTicketController::class, 'store'])->name('store');
        Route::get('/{ticket}', [AdminTicketController::class, 'show'])->name('show');
        Route::get('/{ticket}/print', [AdminTicketController::class, 'print'])->name('print');
        Route::post('/{ticket}/close', [AdminTicketController::class, 'close'])->name('close');
    });
});

/*
|--------------------------------------------------------------------------
| Technical Staff Area (Role: Technical Staff)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:technical_staff'])->prefix('staff')->name('staff.')->group(function () {
    Route::get('/dashboard', StaffDashboardController::class)->name('dashboard');

    // Shared & Claimed Trouble Tickets
    Route::prefix('tickets')->name('tickets.')->group(function () {
        Route::get('/', [StaffTicketController::class, 'index'])->name('index');
        Route::get('/create', [StaffTicketController::class, 'create'])->name('create');
        Route::post('/', [StaffTicketController::class, 'store'])->name('store');
        Route::get('/{ticket}', [StaffTicketController::class, 'show'])->name('show');
        Route::get('/{ticket}/print', [StaffTicketController::class, 'print'])->name('print');
        Route::post('/{ticket}/claim', [StaffTicketController::class, 'claim'])->name('claim');
        Route::patch('/{ticket}/start', [StaffTicketController::class, 'start'])->name('start');
        Route::patch('/{ticket}/resolve', [StaffTicketController::class, 'resolve'])->name('resolve');
    });
});

/*
|--------------------------------------------------------------------------
| Subscriber Area (Role: Subscriber)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:subscriber'])->prefix('subscriber')->name('subscriber.')->group(function () {
    Route::get('/dashboard', [SubscriberDashboardController::class, 'dashboard'])->name('dashboard');
    Route::get('/account', [SubscriberDashboardController::class, 'account'])->name('account');
    Route::get('/billing', [SubscriberDashboardController::class, 'billing'])->name('billing');
    Route::get('/chatbot', [SubscriberDashboardController::class, 'chatbot'])->name('chatbot');
    
    // Chatbot AI chat + ticket escalation (handled in Subscriber DashboardController)
    Route::post('/chatbot/chat', [SubscriberDashboardController::class, 'chat'])->name('chatbot.chat');
    Route::post('/chatbot/ticket', [SubscriberDashboardController::class, 'storeTicket'])->name('chatbot.ticket');
});

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';