<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\TimeSlot;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function searchPatient(Request $request)
    {
        $request->validate([
            'cid' => 'required|string',
        ]);

        try {
            $cid = $request->cid;

            // ค้นหาข้อมูลผู้ป่วยจากฐานข้อมูล PostgreSQL
            $patients = DB::connection('pgsql')->table('person')
                ->selectRaw('cid, pname, fname, lname, birthdate, patient_hn')
                ->where('cid', '=', $cid)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $patients
            ]);
        } catch (\Exception $e) {
            Log::error('Error searching for patient: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการค้นหาข้อมูลผู้ป่วย: ' . $e->getMessage()
            ], 500);
        }
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
      /*   $his = DB::connection('pgsql')->table('person')
            ->selectRaw('cid,pname,fname,lname,birthdate,patient_hn')
            ->where('cid', '=', '1418000003320')
            ->get(); */
        /* dd($his); */
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
            ->get()
            ->map(function ($timeSlot) {
                // แปลงรูปแบบเวลาให้เป็น string ในรูปแบบ HH:mm
                return [
                    'id' => $timeSlot->id,
                    'start_time' => \Carbon\Carbon::parse($timeSlot->start_time)->format('H:i'),
                    'end_time' => \Carbon\Carbon::parse($timeSlot->end_time)->format('H:i'),
                    'max_appointments' => $timeSlot->max_appointments,
                    'booked_appointments' => $timeSlot->booked_appointments,
                ];
            });

        return response()->json($timeSlots);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'time_slot_id' => 'required|exists:time_slots,id',
            'notes' => 'nullable|string',
            'patient_cid' => 'required|string',
            'patient_hn' => 'nullable|string',
            'patient_pname' => 'nullable|string',
            'patient_fname' => 'nullable|string',
            'patient_lname' => 'nullable|string',
            'patient_birthdate' => 'nullable|date',
            'manual_pname' => 'required_if:patient_pname,null',
            'manual_fname' => 'required_if:patient_fname,null',
            'manual_lname' => 'required_if:patient_lname,null',
            'manual_birthdate' => 'required_if:patient_birthdate,null',
        ]);

        $timeSlot = TimeSlot::findOrFail($validated['time_slot_id']);

        // ตรวจสอบว่า time slot ยังว่างหรือไม่
        if (!$timeSlot->isAvailable()) {
            return back()->withErrors(['time_slot_id' => 'ช่วงเวลานี้ไม่ว่างแล้ว โปรดเลือกช่วงเวลาอื่น'])
                ->withInput();
        }

        // ตรวจสอบข้อมูลผู้ป่วย
        $patientData = [
            'cid' => $validated['patient_cid'],
            'hn' => $validated['patient_hn'] ?? null,
            'pname' => $validated['patient_pname'] ?? $validated['manual_pname'] ?? null,
            'fname' => $validated['patient_fname'] ?? $validated['manual_fname'] ?? null,
            'lname' => $validated['patient_lname'] ?? $validated['manual_lname'] ?? null,
            'birthdate' => $validated['patient_birthdate'] ?? $validated['manual_birthdate'] ?? null,
        ];

        // เริ่ม transaction
        DB::beginTransaction();

        try {
            // บันทึกหรืออัปเดตข้อมูลผู้ป่วยในฐานข้อมูล (ถ้ามีการเก็บข้อมูลผู้ป่วย)
            // ตัวอย่าง:
            // $patient = Patient::updateOrCreate(
            //    ['cid' => $patientData['cid']],
            //    $patientData
            // );

            // สร้าง appointment
            $appointment = Appointment::create([
                'user_id' => Auth::id(),
                'time_slot_id' => $timeSlot->id,
                'doctor_id' => $timeSlot->doctor_id,
                'clinic_id' => $timeSlot->clinic_id,
                'notes' => $validated['notes'] ?? null,
                'status' => 'pending',
                'patient_cid' => $patientData['cid'],
                'patient_hn' => $patientData['hn'],
                'patient_pname' => $patientData['pname'],
                'patient_fname' => $patientData['fname'],
                'patient_lname' => $patientData['lname'],
                'patient_birthdate' => $patientData['birthdate'],
            ]);

            // เพิ่มจำนวนการนัดหมายใน time slot
            $timeSlot->increment('booked_appointments');

            DB::commit();

            return redirect()->route('appointments.index')
                ->with('success', 'นัดหมายสำเร็จ');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating appointment: ' . $e->getMessage());
            return back()->withErrors(['error' => 'เกิดข้อผิดพลาดในการบันทึกการนัดหมาย: ' . $e->getMessage()])
                ->withInput();
        }
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
