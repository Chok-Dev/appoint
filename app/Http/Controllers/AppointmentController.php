<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\TimeSlot;
use App\Models\Clinic;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        // For admin users, show all appointments
        if (Auth::user()->isAdmin()) {
            $appointments = Appointment::with(['user', 'timeSlot', 'doctor', 'clinic'])
                                      ->orderBy('created_at', 'desc')
                                      ->get();
        } else {
            // For regular users, show only their appointments
            $appointments = Auth::user()->appointments()
                                       ->with(['timeSlot', 'doctor', 'clinic'])
                                       ->orderBy('created_at', 'desc')
                                       ->get();
        }
        
        return view('appointments.index', compact('appointments'));
    }

    public function create()
    {
        $clinics = Clinic::all();
        $doctors = collect(); // Empty collection by default, will be populated via AJAX
        $timeSlots = collect(); // Empty collection by default, will be populated via AJAX
        
        return view('appointments.create', compact('clinics', 'doctors', 'timeSlots'));
    }

    // AJAX endpoint to get doctors for a clinic
    public function getDoctors(Request $request)
    {
        $request->validate([
            'clinic_id' => 'required|exists:clinics,id',
        ]);

        $doctors = Clinic::findOrFail($request->clinic_id)->doctors;
        
        return response()->json($doctors);
    }

    // AJAX endpoint to get available time slots
    public function getTimeSlots(Request $request)
    {
        $request->validate([
            'clinic_id' => 'required|exists:clinics,id',
            'doctor_id' => 'required|exists:doctors,id',
            'date' => 'required|date',
        ]);

        $timeSlots = TimeSlot::where('clinic_id', $request->clinic_id)
                            ->where('doctor_id', $request->doctor_id)
                            ->where('date', $request->date)
                            ->where('is_active', true)
                            ->whereRaw('booked_appointments < max_appointments')
                            ->orderBy('start_time')
                            ->get();
        
        return response()->json($timeSlots);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'time_slot_id' => 'required|exists:time_slots,id',
            'notes' => 'nullable|string',
        ]);

        $timeSlot = TimeSlot::findOrFail($validated['time_slot_id']);
        
        // Check if the time slot is available
        if (!$timeSlot->isAvailable()) {
            return back()->withErrors(['time_slot_id' => 'This time slot is no longer available.'])
                         ->withInput();
        }

        // Create appointment
        $appointment = Appointment::create([
            'user_id' => Auth::id(),
            'time_slot_id' => $timeSlot->id,
            'doctor_id' => $timeSlot->doctor_id,
            'clinic_id' => $timeSlot->clinic_id,
            'notes' => $validated['notes'] ?? null,
            'status' => 'pending',
        ]);

        // Increment booked_appointments count
        $timeSlot->increment('booked_appointments');

        return redirect()->route('appointments.index')
            ->with('success', 'Appointment created successfully.');
    }

    public function show(Appointment $appointment)
    {
        // Check if the user is admin or the appointment belongs to the user
        if (!Auth::user()->isAdmin() && $appointment->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $appointment->load(['user', 'timeSlot', 'doctor', 'clinic']);
        
        return view('appointments.show', compact('appointment'));
    }

    public function edit(Appointment $appointment)
    {
        // Check if the user is admin or the appointment belongs to the user
        if (!Auth::user()->isAdmin() && $appointment->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $appointment->load(['timeSlot', 'doctor', 'clinic']);
        
        // Only allow editing for pending appointments
        if ($appointment->status !== 'pending') {
            return redirect()->route('appointments.show', $appointment)
                ->with('error', 'Only pending appointments can be edited.');
        }

        $clinics = Clinic::all();
        $doctors = $appointment->clinic->doctors;
        $availableTimeSlots = TimeSlot::where('clinic_id', $appointment->clinic_id)
                                     ->where('doctor_id', $appointment->doctor_id)
                                     ->where('date', $appointment->timeSlot->date)
                                     ->where('is_active', true)
                                     ->whereRaw('booked_appointments < max_appointments')
                                     ->orWhere('id', $appointment->time_slot_id) // Include current time slot
                                     ->orderBy('start_time')
                                     ->get();
        
        return view('appointments.edit', compact('appointment', 'clinics', 'doctors', 'availableTimeSlots'));
    }

    public function update(Request $request, Appointment $appointment)
    {
        // Check if the user is admin or the appointment belongs to the user
        if (!Auth::user()->isAdmin() && $appointment->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
        
        // Only allow updating pending appointments
        if ($appointment->status !== 'pending') {
            return redirect()->route('appointments.show', $appointment)
                ->with('error', 'Only pending appointments can be updated.');
        }

        $validated = $request->validate([
            'time_slot_id' => 'required|exists:time_slots,id',
            'notes' => 'nullable|string',
        ]);

        // If time slot is being changed
        if ($appointment->time_slot_id != $validated['time_slot_id']) {
            $oldTimeSlot = $appointment->timeSlot;
            $newTimeSlot = TimeSlot::findOrFail($validated['time_slot_id']);
            
            // Check if the new time slot is available
            if (!$newTimeSlot->isAvailable()) {
                return back()->withErrors(['time_slot_id' => 'This time slot is no longer available.'])
                             ->withInput();
            }

            // Decrement old time slot booked_appointments count
            $oldTimeSlot->decrement('booked_appointments');
            
            // Increment new time slot booked_appointments count
            $newTimeSlot->increment('booked_appointments');
            
            // Update appointment with new data
            $appointment->update([
                'time_slot_id' => $newTimeSlot->id,
                'doctor_id' => $newTimeSlot->doctor_id,
                'clinic_id' => $newTimeSlot->clinic_id,
                'notes' => $validated['notes'] ?? null,
            ]);
        } else {
            // Just update notes
            $appointment->update([
                'notes' => $validated['notes'] ?? null,
            ]);
        }

        return redirect()->route('appointments.show', $appointment)
            ->with('success', 'Appointment updated successfully.');
    }

    public function cancel(Appointment $appointment)
    {
        // Check if the user is admin or the appointment belongs to the user
        if (!Auth::user()->isAdmin() && $appointment->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
        
        // Only allow cancelling pending appointments
        if ($appointment->status !== 'pending') {
            return redirect()->route('appointments.show', $appointment)
                ->with('error', 'Only pending appointments can be cancelled.');
        }

        // Update status to cancelled
        $appointment->update(['status' => 'cancelled']);
        
        // Decrement time slot booked_appointments count
        $appointment->timeSlot->decrement('booked_appointments');

        return redirect()->route('appointments.index')
            ->with('success', 'Appointment cancelled successfully.');
    }

    // Admin-only methods
    public function updateStatus(Request $request, Appointment $appointment)
    {
        // Check if the user is admin
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,cancelled,completed',
        ]);

        // If changing to cancelled and was previously not cancelled, decrement booked count
        if ($validated['status'] === 'cancelled' && $appointment->status !== 'cancelled') {
            $appointment->timeSlot->decrement('booked_appointments');
        } 
        // If changing from cancelled to something else, increment booked count
        else if ($appointment->status === 'cancelled' && $validated['status'] !== 'cancelled') {
            $appointment->timeSlot->increment('booked_appointments');
        }

        $appointment->update(['status' => $validated['status']]);

        return redirect()->route('appointments.show', $appointment)
            ->with('success', 'Appointment status updated successfully.');
    }
}