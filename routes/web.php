<?php

use App\Http\Controllers\Admin\AssignmentController as AdminAssignmentController;
use App\Http\Controllers\Admin\BillingController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\Admin\StaffScheduleController;
use App\Http\Controllers\Admin\SubscriberController as AdminSubscriberController;
use App\Http\Controllers\Admin\TechnicalStaffController;
use App\Http\Controllers\Admin\TicketController as AdminTicketController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Staff\AssignmentController as StaffAssignmentController;
use App\Http\Controllers\Staff\DashboardController as StaffDashboardController;
use App\Http\Controllers\Staff\TicketController as StaffTicketController;
use App\Http\Controllers\Subscriber\DashboardController as SubscriberDashboardController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Landing page shows login form.
Route::get('/', function () {
    return redirect()->route('login');
});

// Fallback dashboard redirects by user role.
Route::get('/dashboard', function () {
    $user = Auth::user();
    /** @var User $user */

    return match (true) {
        $user->hasRole('admin') => redirect()->route('admin.dashboard'),
        $user->hasRole('technical_staff') => redirect()->route('staff.dashboard'),
        $user->hasRole('subscriber') => redirect()->route('subscriber.dashboard'),
        default => redirect()->route('login'),
    };
})->middleware('auth')->name('dashboard');

/*
|--------------------------------------------------------------------------
| Account Settings (any logged-in user)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/field', [ProfileController::class, 'updateField'])->name('profile.update-field');
    Route::patch('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/notifications/read', [ProfileController::class, 'markNotificationsRead'])->name('notifications.read');
    Route::get('/notifications/check', [ProfileController::class, 'checkNewNotifications'])->name('notifications.check');
});

/*
|--------------------------------------------------------------------------
| Admin Area (admin only)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', AdminDashboardController::class)->name('dashboard');

    // Technical staff accounts
    Route::prefix('technical-staff')->name('technical-staff.')->group(function () {
        Route::get('/', [TechnicalStaffController::class, 'index'])->name('index');
        Route::get('/create', [TechnicalStaffController::class, 'create'])->name('create');
        Route::post('/', [TechnicalStaffController::class, 'store'])->name('store');
        Route::get('/{technicalStaff}/edit', [TechnicalStaffController::class, 'edit'])->name('edit');
        Route::patch('/{technicalStaff}', [TechnicalStaffController::class, 'update'])->name('update');
        Route::post('/{technicalStaff}/avatar', [TechnicalStaffController::class, 'updateAvatar'])->name('avatar');

        Route::get('/schedules', [StaffScheduleController::class, 'index'])->name('schedules.index');
        Route::post('/schedules', [StaffScheduleController::class, 'store'])->name('schedules.store');
    });

    // Subscriber accounts
    Route::prefix('subscribers')->name('subscribers.')->group(function () {
        Route::get('/', [AdminSubscriberController::class, 'index'])->name('index');
        Route::get('/create', [AdminSubscriberController::class, 'create'])->name('create');
        Route::post('/', [AdminSubscriberController::class, 'store'])->name('store');
    });

    // Internet plans
    Route::prefix('plans')->name('plans.')->group(function () {
        Route::get('/', [PlanController::class, 'index'])->name('index');
        Route::get('/create', [PlanController::class, 'create'])->name('create');
        Route::post('/', [PlanController::class, 'store'])->name('store');
        Route::delete('/{plan}', [PlanController::class, 'destroy'])->name('destroy');
    });

    // Invoices, payments, billing
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

    // Trouble tickets
    Route::prefix('tickets')->name('tickets.')->group(function () {
        Route::get('/', [AdminTicketController::class, 'index'])->name('index');
        Route::get('/create', [AdminTicketController::class, 'create'])->name('create');
        Route::post('/', [AdminTicketController::class, 'store'])->name('store');
        Route::get('/{ticket}', [AdminTicketController::class, 'show'])->name('show');
        Route::get('/{ticket}/print', [AdminTicketController::class, 'print'])->name('print');
        Route::post('/{ticket}/close', [AdminTicketController::class, 'close'])->name('close');
    });

    // Ticket assignments
    Route::prefix('assignments')->name('assignments.')->group(function () {
        Route::get('/', [AdminAssignmentController::class, 'index'])->name('index');
        Route::get('/create', [AdminAssignmentController::class, 'create'])->name('create');
        Route::post('/', [AdminAssignmentController::class, 'store'])->name('store');
        Route::post('/{assignment}/complete', [AdminAssignmentController::class, 'complete'])->name('complete');
        Route::delete('/{assignment}', [AdminAssignmentController::class, 'destroy'])->name('destroy');
    });
});

/*
|--------------------------------------------------------------------------
| Staff Area (technical staff only)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:technical_staff'])->prefix('staff')->name('staff.')->group(function () {
    Route::get('/dashboard', StaffDashboardController::class)->name('dashboard');
    Route::get('/my-account', [StaffDashboardController::class, 'myAccount'])->name('my-account');
    Route::get('/assignments', [StaffAssignmentController::class, 'index'])->name('assignments');

    // Shared & claimed tickets
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
| Subscriber Area (subscribers only)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:subscriber'])->prefix('subscriber')->name('subscriber.')->group(function () {
    Route::get('/dashboard', [SubscriberDashboardController::class, 'dashboard'])->name('dashboard');
    Route::get('/account', [SubscriberDashboardController::class, 'account'])->name('account');
    Route::patch('/account', [SubscriberDashboardController::class, 'updateAccount'])->name('account.update');
    Route::get('/billing', [SubscriberDashboardController::class, 'billing'])->name('billing');
    Route::get('/chatbot', [SubscriberDashboardController::class, 'chatbot'])->name('chatbot');

    // Chatbot chat + ticket escalation
    Route::post('/chatbot/chat', [SubscriberDashboardController::class, 'chat'])->name('chatbot.chat');
    Route::post('/chatbot/ticket', [SubscriberDashboardController::class, 'storeTicket'])->name('chatbot.ticket');
});

/*
|--------------------------------------------------------------------------
| Auth Routes
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';
