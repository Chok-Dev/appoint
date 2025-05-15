<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    /**
     * Display a listing of the users.
     */
    public function index()
    {
        $users = User::orderBy('name')->paginate(10);
        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        return view('users.create');
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'in:user,admin'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return redirect()->route('users.index')
            ->with('success', 'ผู้ใช้งานถูกสร้างเรียบร้อยแล้ว');
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        $appointmentsCount = $user->appointments()->count();
        $pendingAppointments = $user->appointments()->where('status', 'pending')->count();
        $completedAppointments = $user->appointments()->where('status', 'completed')->count();
        $cancelledAppointments = $user->appointments()->where('status', 'cancelled')->count();
        
        return view('users.show', compact('user', 'appointmentsCount', 'pendingAppointments', 'completedAppointments', 'cancelledAppointments'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user)
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'role' => ['required', 'in:user,admin'],
        ];

        // Only validate password if it's provided
        if ($request->filled('password')) {
            $rules['password'] = ['confirmed', Rules\Password::defaults()];
        }

        $validated = $request->validate($rules);

        // Remove password from validated data if it's not provided
        if (!$request->filled('password')) {
            unset($validated['password']);
        } else {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('users.index')
            ->with('success', 'ผู้ใช้งานถูกอัปเดตเรียบร้อยแล้ว');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user)
    {
        // Prevent self-deletion
        if ($user->id === Auth::id()) {
            return back()->with('error', 'ไม่สามารถลบบัญชีของตัวเองได้');
        }

        // Check if user has appointments
        if ($user->appointments()->exists()) {
            return back()->with('error', 'ไม่สามารถลบผู้ใช้งานนี้ได้เนื่องจากมีประวัติการนัดหมาย');
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'ผู้ใช้งานถูกลบเรียบร้อยแล้ว');
    }
}