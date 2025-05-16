<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Group;
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
    /**
     * Generate a printable version of the appointment.
     */
    public function print(Appointment $appointment)
    {
        // Check if the user is admin or the appointment belongs to the user
        if (!Auth::user()->isAdmin() && $appointment->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $appointment->load(['user', 'timeSlot', 'doctor', 'clinic']);

        return view('appointments.print', compact('appointment'));
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
    public function index(Request $request)
    {
        // Set the number of items per page
        $perPage = 10; // You can adjust this value as needed

        // Query builder
        $query = Appointment::with(['user', 'timeSlot', 'doctor', 'clinic']);

        // Filter by user_id if provided
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        } else {
            // Otherwise, apply standard permission logic
            if (!Auth::user()->isAdmin()) {
                // For regular users, show only their appointments
                $query->where('user_id', Auth::id());
            }
        }

        // Sort by created_at descending
        $query->orderBy('created_at', 'desc');

        // Paginate the results
        $appointments = $query->paginate($perPage);

        return view('appointments.index', compact('appointments'));
    }

    public function create()
    {
        $groups = Group::all(); // เพิ่มการดึงข้อมูลกลุ่มงาน
        $clinics = Clinic::all();
        $doctors = collect(); // Empty collection by default, will be populated via AJAX
        $timeSlots = collect(); // Empty collection by default, will be populated via AJAX

        return view('appointments.create', compact('groups', 'clinics', 'doctors', 'timeSlots'));
    }

    public function getClinicsByGroup(Request $request)
    {
        $request->validate([
            'group_id' => 'required|exists:groups,id',
        ]);

        $clinics = Clinic::where('group_id', $request->group_id)
            ->orderBy('name')
            ->get();

        return response()->json($clinics);
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
    public function getAvailableDates(Request $request)
    {
        $request->validate([
            'clinic_id' => 'required|exists:clinics,id',
            'doctor_id' => 'required|exists:doctors,id',
        ]);

        try {
            // Get all available dates for the doctor and clinic
            $availableDates = TimeSlot::where('clinic_id', $request->clinic_id)
                ->where('doctor_id', $request->doctor_id)
                ->where('date', '>=', now()->format('Y-m-d')) // Only future dates
                ->where('is_active', true)
                ->whereRaw('booked_appointments < max_appointments')
                ->orderBy('date')
                ->pluck('date')
                ->unique()
                ->values()
                ->toArray();

            // Format dates for consistency
            $formattedDates = [];
            foreach ($availableDates as $date) {
                $formattedDates[] = Carbon::parse($date)->format('Y-m-d');
            }

            // If no available dates, include next 7 days as a fallback
            if (empty($formattedDates)) {
                $startDate = now();
                for ($i = 0; $i < 7; $i++) {
                    $formattedDates[] = $startDate->copy()->addDays($i)->format('Y-m-d');
                }
            }

            // Get holidays from the HIS database
            $holidaysQuery = DB::connection('pgsql')
                ->table('holiday')
                ->whereIn('holiday_date', $formattedDates)
                ->select('holiday_date', 'day_name');

            $holidays = [];
            foreach ($holidaysQuery->get() as $holiday) {
                $holidays[$holiday->holiday_date] = [
                    'day_name' => $holiday->day_name
                ];
            }

            return response()->json([
                'success' => true,
                'dates' => $formattedDates,
                'holidays' => $holidays,
                'message' => empty($availableDates) ? 'ไม่พบวันที่ที่มีช่วงเวลาว่าง กำลังแสดง 7 วันข้างหน้าแทน' : 'พบวันที่ที่มีช่วงเวลาว่าง'
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting available dates: ' . $e->getMessage());

            // Return next 7 days as a fallback
            $fallbackDates = [];
            $startDate = now();
            for ($i = 0; $i < 7; $i++) {
                $fallbackDates[] = $startDate->copy()->addDays($i)->format('Y-m-d');
            }

            return response()->json([
                'success' => false,
                'dates' => $fallbackDates,
                'holidays' => [],
                'message' => 'เกิดข้อผิดพลาดในการดึงข้อมูลวันที่: ' . $e->getMessage() . ' กำลังแสดง 7 วันข้างหน้าแทน'
            ]);
        }
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
            'patient_age' => 'nullable|integer|min:0|max:120',
            'manual_pname' => 'required_if:patient_pname,null',
            'manual_fname' => 'required_if:patient_fname,null',
            'manual_lname' => 'required_if:patient_lname,null',
            'manual_age' => 'required_if:patient_birthdate,null',
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
            'birthdate' => $validated['patient_birthdate'] ?? null,
            'age' => $validated['patient_age'] ?? $validated['manual_age'] ?? null,
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
                'patient_age' => $patientData['age'],
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
        // ตรวจสอบสิทธิ์การเข้าถึง
        if (!Auth::user()->isAdmin() && $appointment->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // ตรวจสอบว่าการนัดหมายยังอยู่ในสถานะ pending หรือไม่
        if ($appointment->status !== 'pending') {
            return redirect()->route('appointments.show', $appointment)
                ->with('error', 'สามารถแก้ไขได้เฉพาะการนัดหมายที่มีสถานะรอดำเนินการเท่านั้น');
        }

        // โหลดข้อมูลที่เกี่ยวข้อง
        $appointment->load(['timeSlot', 'doctor', 'clinic']);

        // ดึงข้อมูลสำหรับการแสดงผล
        $groups = Group::all(); // เพิ่มการดึงข้อมูลกลุ่มงาน
        $clinics = Clinic::all();

        return view('appointments.edit', compact('appointment', 'groups', 'clinics'));
    }

    public function update(Request $request, Appointment $appointment)
    {
        // ตรวจสอบสิทธิ์การเข้าถึง
        if (!Auth::user()->isAdmin() && $appointment->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // ตรวจสอบว่าการนัดหมายยังอยู่ในสถานะ pending หรือไม่
        if ($appointment->status !== 'pending') {
            return redirect()->route('appointments.show', $appointment)
                ->with('error', 'สามารถแก้ไขได้เฉพาะการนัดหมายที่มีสถานะรอดำเนินการเท่านั้น');
        }

        $validated = $request->validate([
            'time_slot_id' => 'required|exists:time_slots,id',
            'notes' => 'nullable|string',
            'patient_cid' => 'required|string',
            'patient_hn' => 'nullable|string',
            'patient_pname' => 'required|string',
            'patient_fname' => 'required|string',
            'patient_lname' => 'required|string',
            'patient_birthdate' => 'nullable|date',
            'patient_age' => 'nullable|integer|min:0|max:120',
        ]);

        // ถ้ามีการเปลี่ยนช่วงเวลา
        if ($appointment->time_slot_id != $validated['time_slot_id']) {
            $oldTimeSlot = $appointment->timeSlot;
            $newTimeSlot = TimeSlot::findOrFail($validated['time_slot_id']);

            // ตรวจสอบว่าช่วงเวลาใหม่ยังว่างหรือไม่
            if (!$newTimeSlot->isAvailable()) {
                return back()->withErrors(['time_slot_id' => 'ช่วงเวลานี้ไม่ว่างแล้ว กรุณาเลือกช่วงเวลาอื่น'])
                    ->withInput();
            }

            // เริ่ม transaction
            DB::beginTransaction();

            try {
                // ลดจำนวนการนัดในช่วงเวลาเดิม
                $oldTimeSlot->decrement('booked_appointments');

                // เพิ่มจำนวนการนัดในช่วงเวลาใหม่
                $newTimeSlot->increment('booked_appointments');

                // อัพเดทข้อมูลการนัดหมาย
                $appointment->update([
                    'time_slot_id' => $newTimeSlot->id,
                    'doctor_id' => $newTimeSlot->doctor_id,
                    'clinic_id' => $newTimeSlot->clinic_id,
                    'notes' => $validated['notes'] ?? null,
                    'patient_cid' => $validated['patient_cid'],
                    'patient_hn' => $validated['patient_hn'],
                    'patient_pname' => $validated['patient_pname'],
                    'patient_fname' => $validated['patient_fname'],
                    'patient_lname' => $validated['patient_lname'],
                    'patient_birthdate' => $validated['patient_birthdate'],
                    'patient_age' => $validated['patient_age'],
                ]);

                DB::commit();

                return redirect()->route('appointments.show', $appointment)
                    ->with('success', 'แก้ไขการนัดหมายเรียบร้อยแล้ว');
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error updating appointment: ' . $e->getMessage());
                return back()->withErrors(['error' => 'เกิดข้อผิดพลาดในการแก้ไขการนัดหมาย: ' . $e->getMessage()])
                    ->withInput();
            }
        } else {
            // ถ้าไม่มีการเปลี่ยนช่วงเวลา ให้อัพเดทเฉพาะหมายเหตุและข้อมูลผู้ป่วย
            $appointment->update([
                'notes' => $validated['notes'] ?? null,
                'patient_cid' => $validated['patient_cid'],
                'patient_hn' => $validated['patient_hn'],
                'patient_pname' => $validated['patient_pname'],
                'patient_fname' => $validated['patient_fname'],
                'patient_lname' => $validated['patient_lname'],
                'patient_birthdate' => $validated['patient_birthdate'],
                'patient_age' => $validated['patient_age'],
            ]);

            return redirect()->route('appointments.show', $appointment)
                ->with('success', 'แก้ไขการนัดหมายเรียบร้อยแล้ว');
        }
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
