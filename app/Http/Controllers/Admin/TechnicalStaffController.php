<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTechnicalStaffRequest;
use App\Models\TechnicalStaff;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class TechnicalStaffController extends Controller
{
    public function index(): View
    {
        $technicalStaff = TechnicalStaff::with('user')->latest()->get();

        return view('admin.technical-staff.index', ['technicalStaff' => $technicalStaff]);
    }

    public function create(): View
    {
        return view('admin.technical-staff.create');
    }

    public function store(StoreTechnicalStaffRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            $user->assignRole('technical_staff');

            TechnicalStaff::create([
                'user_id' => $user->id,
                'phone' => $validated['phone'] ?? null,
                'position' => $validated['position'] ?? null,
            ]);
        });

        return redirect()
            ->route('admin.technical-staff.index')
            ->with('status', 'technical-staff-created');
    }
}
