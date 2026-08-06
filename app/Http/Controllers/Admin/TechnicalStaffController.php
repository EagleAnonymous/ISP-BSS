<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTechnicalStaffRequest;
use App\Models\TechnicalStaff;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
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
        $nextEmployeeId = $this->nextEmployeeId();

        return view('admin.technical-staff.create', ['nextEmployeeId' => $nextEmployeeId]);
    }

    public function store(StoreTechnicalStaffRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'employee_id' => $validated['employee_id'] ?? $this->nextEmployeeId(),
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

    public function edit(TechnicalStaff $technicalStaff): View
    {
        $technicalStaff->load('user');

        return view('admin.technical-staff.edit', ['technicalStaff' => $technicalStaff]);
    }

    public function update(Request $request, TechnicalStaff $technicalStaff): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,'.$technicalStaff->user_id],
            'phone' => ['nullable', 'string', 'max:30'],
            'position' => ['nullable', 'string', 'max:100'],
            'department' => ['nullable', 'string', 'max:100'],
            'supervisor' => ['nullable', 'string', 'max:100'],
            'location' => ['nullable', 'string', 'max:255'],
        ]);

        $user = $technicalStaff->user;
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->save();

        $technicalStaff->phone = $validated['phone'] ?? null;
        $technicalStaff->position = $validated['position'] ?? null;
        $technicalStaff->department = $validated['department'] ?? null;
        $technicalStaff->supervisor = $validated['supervisor'] ?? null;
        $technicalStaff->location = $validated['location'] ?? null;
        $technicalStaff->save();

        return redirect()
            ->route('admin.technical-staff.edit', $technicalStaff)
            ->with('status', 'technical-staff-updated');
    }

    public function updateAvatar(Request $request, TechnicalStaff $technicalStaff): RedirectResponse
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:10240'],
        ]);

        $user = $technicalStaff->user;

        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        $path = $request->file('avatar')->store('avatars', 'public');
        $user->avatar_path = $path;
        $user->save();

        return redirect()
            ->route('admin.technical-staff.edit', $technicalStaff)
            ->with('status', 'avatar-updated');
    }

    private function nextEmployeeId(): string
    {
        $lastUser = User::whereNotNull('employee_id')->orderBy('id', 'desc')->lockForUpdate()->first();

        $next = $lastUser ? ($lastUser->id + 1) : 1;

        return 'EMP-'.str_pad((string) $next, 5, '0', STR_PAD_LEFT);
    }
}
