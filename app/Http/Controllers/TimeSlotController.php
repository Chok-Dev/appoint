<?php

namespace App\Http\Controllers;

use App\Models\TimeSlot;
use App\Models\Doctor;
use App\Models\Clinic;
use Illuminate\Http\Request;

class TimeSlotController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin')->except(['index', 'show']);
    }

    public function index(Request $request)
    {
        $query = TimeSlot::with(['doctor', 'clinic']);

        // Filter by clinic if specified
        if ($request->has('clinic_id')) {
            $query->where('clinic_id', $request->clinic_id);
        }

        // Filter by doctor if specified
        if ($request->has('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }

        // Filter by date if specified
        if ($request->has('date')) {
            $query->whereDate('date', $request->date);
        }

        $timeSlots = $query->orderBy('date')->orderBy('start_time')->get();
        
        $clinics = Clinic::all();
        $doctors = Doctor::all();
        
        return view('timeslots.index', compact('timeSlots', 'clinics', 'doctors'));
    }

    public function create()
    {
        $clinics = Clinic::all();
        $doctors = Doctor::all();
        return view('timeslots.create', compact('clinics', 'doctors'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'clinic_id' => 'required|exists:clinics,id',
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'max_appointments' => 'required|integer|min:1',
            'is_active' => 'boolean',
        ]);

        // Ensure the doctor is associated with the clinic
        $doctor = Doctor::findOrFail($validated['doctor_id']);
        $clinic = Clinic::findOrFail($validated['clinic_id']);
        
        if (!$doctor->clinics->contains($clinic->id)) {
            return back()->withErrors(['doctor_id' => 'This doctor is not associated with the selected clinic.'])
                         ->withInput();
        }

        TimeSlot::create($validated);

        return redirect()->route('timeslots.index')
            ->with('success', 'Time slot created successfully.');
    }

    public function show(TimeSlot $timeSlot)
    {
        $timeSlot->load('doctor', 'clinic', 'appointments.user');
        return view('timeslots.show', compact('timeSlot'));
    }

    public function edit(TimeSlot $timeSlot)
    {
        $clinics = Clinic::all();
        $doctors = Doctor::all();
        return view('timeslots.edit', compact('timeSlot', 'clinics', 'doctors'));
    }

    public function update(Request $request, TimeSlot $timeSlot)
    {
        $validated = $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'clinic_id' => 'required|exists:clinics,id',
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'max_appointments' => 'required|integer|min:' . $timeSlot->booked_appointments,
            'is_active' => 'boolean',
        ]);

        // Ensure the doctor is associated with the clinic
        $doctor = Doctor::findOrFail($validated['doctor_id']);
        $clinic = Clinic::findOrFail($validated['clinic_id']);
        
        if (!$doctor->clinics->contains($clinic->id)) {
            return back()->withErrors(['doctor_id' => 'This doctor is not associated with the selected clinic.'])
                         ->withInput();
        }

        $timeSlot->update($validated);

        return redirect()->route('timeslots.index')
            ->with('success', 'Time slot updated successfully.');
    }

    public function destroy(TimeSlot $timeSlot)
    {
        // Check if there are any appointments for this time slot
        if ($timeSlot->appointments()->count() > 0) {
            return back()->withErrors(['delete' => 'Cannot delete time slot with existing appointments.']);
        }

        $timeSlot->delete();

        return redirect()->route('timeslots.index')
            ->with('success', 'Time slot deleted successfully.');
    }
}