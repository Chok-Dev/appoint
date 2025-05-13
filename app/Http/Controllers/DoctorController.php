<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\Clinic;
use Illuminate\Http\Request;

class DoctorController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin')->except(['index', 'show']);
    }

    public function index()
    {
        $doctors = Doctor::with('clinics')->get();
        return view('doctors.index', compact('doctors'));
    }

    public function create()
    {
        $clinics = Clinic::all();
        return view('doctors.create', compact('clinics'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'specialty' => 'nullable|string|max:255',
            'clinics' => 'nullable|array',
            'clinics.*' => 'exists:clinics,id',
        ]);

        $doctor = Doctor::create([
            'name' => $validated['name'],
            'specialty' => $validated['specialty'] ?? null,
        ]);

        if (!empty($validated['clinics'])) {
            $doctor->clinics()->attach($validated['clinics']);
        }

        return redirect()->route('doctors.index')
            ->with('success', 'Doctor created successfully.');
    }

    public function show(Doctor $doctor)
    {
        $doctor->load('clinics');
        return view('doctors.show', compact('doctor'));
    }

    public function edit(Doctor $doctor)
    {
        $clinics = Clinic::all();
        $doctor->load('clinics');
        return view('doctors.edit', compact('doctor', 'clinics'));
    }

    public function update(Request $request, Doctor $doctor)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'specialty' => 'nullable|string|max:255',
            'clinics' => 'nullable|array',
            'clinics.*' => 'exists:clinics,id',
        ]);

        $doctor->update([
            'name' => $validated['name'],
            'specialty' => $validated['specialty'] ?? null,
        ]);

        if (isset($validated['clinics'])) {
            $doctor->clinics()->sync($validated['clinics']);
        } else {
            $doctor->clinics()->detach();
        }

        return redirect()->route('doctors.index')
            ->with('success', 'Doctor updated successfully.');
    }

    public function destroy(Doctor $doctor)
    {
        $doctor->delete();

        return redirect()->route('doctors.index')
            ->with('success', 'Doctor deleted successfully.');
    }
}