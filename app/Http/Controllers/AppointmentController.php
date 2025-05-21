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
use App\Services\TelegramNotificationService;

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
        // กำหนดข้อความความผิดพลาดเป็นภาษาไทย
        $messages = [
            'search_term.required' => 'กรุณาระบุคำค้นหา',
            'search_term.string' => 'คำค้นหาต้องเป็นข้อความ',
            'search_term.min' => 'คำค้นหาต้องมีอย่างน้อย :min ตัวอักษร',
            'search_type.required' => 'กรุณาเลือกประเภทการค้นหา',
            'search_type.in' => 'ประเภทการค้นหาไม่ถูกต้อง',
        ];

        $request->validate([
            'search_term' => 'required|string|min:2',
            'search_type' => 'required|in:cid,hn,name',
        ], $messages);

        try {
            $searchTerm = $request->search_term;
            $searchType = $request->search_type;

            // สร้าง query builder เริ่มต้น
            $query = DB::connection('pgsql')->table('person')
                ->selectRaw('cid, pname, fname, lname, birthdate, patient_hn, mobile_phone');

            // ปรับ query ตามประเภทการค้นหา
            switch ($searchType) {
                case 'cid':
                    // ค้นหาจากเลขบัตรประชาชน
                    // ทำการค้นหาแบบตรงตัวก่อน แล้วค่อยค้นหาแบบบางส่วน ถ้าไม่พบ
                    $cidQuery = clone $query;
                    $exactMatches = $cidQuery->where('cid', '=', $searchTerm)->get();

                    // ถ้าพบข้อมูลจากการค้นหาแบบตรงตัว ให้ใช้ผลลัพธ์นั้น
                    if ($exactMatches->count() > 0) {
                        return response()->json([
                            'success' => true,
                            'data' => $exactMatches,
                            'search_type' => 'cid',
                            'exact_match' => true
                        ]);
                    }

                    // ถ้าไม่พบจากการค้นหาแบบตรงตัว ให้ลองค้นหาแบบคล้ายคลึง
                    $partialMatches = $query->where('cid', 'like', "%{$searchTerm}%")->get();
                    return response()->json([
                        'success' => true,
                        'data' => $partialMatches,
                        'search_type' => 'cid',
                        'exact_match' => false
                    ]);

                case 'hn':
                    // ค้นหาจาก HN
                    // ทำการค้นหาแบบตรงตัวก่อน แล้วค่อยค้นหาแบบบางส่วน ถ้าไม่พบ
                    $hnQuery = clone $query;
                    $exactMatches = $hnQuery->where('patient_hn', '=', $searchTerm)->get();

                    // ถ้าพบข้อมูลจากการค้นหาแบบตรงตัว ให้ใช้ผลลัพธ์นั้น
                    if ($exactMatches->count() > 0) {
                        return response()->json([
                            'success' => true,
                            'data' => $exactMatches,
                            'search_type' => 'hn',
                            'exact_match' => true
                        ]);
                    }

                    // ถ้าไม่พบจากการค้นหาแบบตรงตัว ให้ลองค้นหาแบบคล้ายคลึง
                    $partialMatches = $query->where('patient_hn', 'like', "%{$searchTerm}%")->get();
                    return response()->json([
                        'success' => true,
                        'data' => $partialMatches,
                        'search_type' => 'hn',
                        'exact_match' => false
                    ]);

                case 'name':
                    // ค้นหาจากชื่อ-นามสกุล
                    // แยกคำค้นหาออกเป็นส่วนๆ
                    $nameParts = explode(' ', trim($searchTerm));

                    // สร้าง query ค้นหาจากชื่อหรือนามสกุล
                    $nameQuery = clone $query;

                    if (count($nameParts) == 1) {
                        // ถ้าค้นหาด้วยคำเดียว ค้นหาทั้งจากชื่อและนามสกุล
                        $nameQuery->where(function ($q) use ($nameParts) {
                            $q->where('fname', 'like', "%{$nameParts[0]}%")
                                ->orWhere('lname', 'like', "%{$nameParts[0]}%");
                        });
                    } else if (count($nameParts) >= 2) {
                        // ถ้าค้นหาด้วยหลายคำ สมมติว่าคำแรกเป็นชื่อ คำที่สองเป็นนามสกุล
                        $nameQuery->where(function ($q) use ($nameParts) {
                            $q->where('fname', 'like', "%{$nameParts[0]}%")
                                ->where('lname', 'like', "%{$nameParts[1]}%");
                        });
                    }

                    $nameMatches = $nameQuery->get();
                    return response()->json([
                        'success' => true,
                        'data' => $nameMatches,
                        'search_type' => 'name'
                    ]);

                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'รูปแบบการค้นหาไม่ถูกต้อง'
                    ], 400);
            }
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
        // กำหนดข้อความความผิดพลาดเป็นภาษาไทย
        $messages = [
            'time_slot_id.required' => 'กรุณาเลือกช่วงเวลา',
            'time_slot_id.exists' => 'ช่วงเวลาที่เลือกไม่ถูกต้อง',
            'notes.string' => 'หมายเหตุต้องเป็นข้อความ',
            'patient_cid.required' => 'กรุณาระบุเลขบัตรประชาชน',
            'patient_cid.string' => 'เลขบัตรประชาชนต้องเป็นตัวอักษร',
            'patient_hn.string' => 'HN ต้องเป็นตัวอักษร',
            'patient_pname.required' => 'กรุณาเลือกคำนำหน้า',
            'patient_pname.string' => 'คำนำหน้าต้องเป็นข้อความ',
            'patient_fname.required' => 'กรุณากรอกชื่อ',
            'patient_fname.string' => 'ชื่อต้องเป็นข้อความ',
            'patient_lname.required' => 'กรุณากรอกนามสกุล',
            'patient_lname.string' => 'นามสกุลต้องเป็นข้อความ',
            'patient_birthdate.date' => 'วันเกิดต้องเป็นวันที่ที่ถูกต้อง',
            'patient_age.required' => 'กรุณาระบุอายุ',
            'patient_age.integer' => 'อายุต้องเป็นตัวเลข',
            'patient_age.min' => 'อายุต้องไม่น้อยกว่า 0 ปี',
            'patient_age.max' => 'อายุต้องไม่มากกว่า 120 ปี',
            'patient_phone.string' => 'เบอร์โทรศัพท์ต้องเป็นข้อความ',
            'patient_phone.max' => 'เบอร์โทรศัพท์ต้องไม่เกิน 20 ตัวอักษร',
        ];

        $validated = $request->validate([
            'time_slot_id' => 'required|exists:time_slots,id',
            'notes' => 'nullable|string',
            'patient_cid' => 'required|string',
            'patient_hn' => 'nullable|string',
            'patient_pname' => 'required|string',
            'patient_fname' => 'required|string',
            'patient_lname' => 'required|string',
            'patient_birthdate' => 'nullable|date',
            'patient_age' => 'required|integer|min:0|max:120',
            'patient_phone' => 'nullable|string|max:20',
        ], $messages);

        // ดึงข้อมูล time slot ที่เลือก
        $timeSlot = TimeSlot::findOrFail($validated['time_slot_id']);

        // ตรวจสอบว่าวันที่เลือกเป็นวันหยุดหรือไม่
        $selectedDate = $timeSlot->date->format('Y-m-d');
        $isHoliday = DB::connection('pgsql')
            ->table('holiday')
            ->where('holiday_date', $selectedDate)
            ->exists();

        if ($isHoliday) {
            // ดึงข้อมูลวันหยุด
            $holiday = DB::connection('pgsql')
                ->table('holiday')
                ->where('holiday_date', $selectedDate)
                ->first();

            $holidayName = $holiday ? $holiday->day_name : 'วันหยุด';

            return back()->withErrors(['time_slot_id' => "ไม่สามารถนัดหมายในวันหยุด ({$holidayName}) กรุณาเลือกวันอื่น"])
                ->withInput();
        }

        // ตรวจสอบว่า time slot ยังว่างหรือไม่
        if (!$timeSlot->isAvailable()) {
            return back()->withErrors(['time_slot_id' => 'ช่วงเวลานี้ไม่ว่างแล้ว โปรดเลือกช่วงเวลาอื่น'])
                ->withInput();
        }

        // ตรวจสอบว่าผู้ป่วยคนนี้มีการนัดในช่วงเวลาเดียวกันแล้วหรือไม่
        $existingAppointment = Appointment::where('patient_cid', $validated['patient_cid'])
            ->where('time_slot_id', $validated['time_slot_id'])
            ->where('status', '!=', 'cancelled')
            ->first();

        if ($existingAppointment) {
            return back()->withErrors(['time_slot_id' => 'ผู้ป่วยนี้มีการนัดหมายในช่วงเวลานี้แล้ว'])
                ->withInput();
        }

        // ตรวจสอบว่าผู้ป่วยคนนี้มีการนัดในวันเดียวกันกับคลินิกเดียวกันแล้วหรือไม่
        $sameDay = Carbon::parse($timeSlot->date)->format('Y-m-d');
        $sameDayAppointment = Appointment::whereHas('timeSlot', function ($query) use ($sameDay, $timeSlot) {
            $query->whereDate('date', $sameDay);
        })
            ->where('patient_cid', $validated['patient_cid'])
            ->where('clinic_id', $timeSlot->clinic_id)
            ->where('status', '!=', 'cancelled')
            ->first();

        if ($sameDayAppointment) {
            return back()->withErrors(['time_slot_id' => 'ผู้ป่วยนี้มีการนัดหมายในคลินิกนี้ในวันเดียวกันแล้ว'])
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
            'phone' => $validated['patient_phone'] ?? null, // Add this line
        ];

        // เริ่ม transaction
        DB::beginTransaction();

        try {
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
                'patient_phone' => $patientData['phone'], // Add this line
            ]);
            // เพิ่มจำนวนการนัดหมายใน time slot
            $timeSlot->increment('booked_appointments');
            TelegramNotificationService::notifyAdminNewAppointment($appointment);
            if ($appointment->user->telegram_chat_id) {
                TelegramNotificationService::notifyUserAppointmentCreated($appointment);
            }
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

        // กำหนดข้อความความผิดพลาดเป็นภาษาไทย
        $messages = [
            'time_slot_id.required' => 'กรุณาเลือกช่วงเวลา',
            'time_slot_id.exists' => 'ช่วงเวลาที่เลือกไม่ถูกต้อง',
            'notes.string' => 'หมายเหตุต้องเป็นข้อความ',
            'patient_cid.required' => 'กรุณาระบุเลขบัตรประชาชน',
            'patient_cid.string' => 'เลขบัตรประชาชนต้องเป็นตัวอักษร',
            'patient_hn.string' => 'HN ต้องเป็นตัวอักษร',
            'patient_pname.required' => 'กรุณาเลือกคำนำหน้า',
            'patient_pname.string' => 'คำนำหน้าต้องเป็นข้อความ',
            'patient_fname.required' => 'กรุณากรอกชื่อ',
            'patient_fname.string' => 'ชื่อต้องเป็นข้อความ',
            'patient_lname.required' => 'กรุณากรอกนามสกุล',
            'patient_lname.string' => 'นามสกุลต้องเป็นข้อความ',
            'patient_birthdate.date' => 'วันเกิดต้องเป็นวันที่ที่ถูกต้อง',
            'patient_age.integer' => 'อายุต้องเป็นตัวเลข',
            'patient_age.min' => 'อายุต้องไม่น้อยกว่า 0 ปี',
            'patient_age.max' => 'อายุต้องไม่มากกว่า 120 ปี',
            'patient_phone.string' => 'เบอร์โทรศัพท์ต้องเป็นข้อความ',
            'patient_phone.max' => 'เบอร์โทรศัพท์ต้องไม่เกิน 20 ตัวอักษร',
        ];

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
            'patient_phone' => 'nullable|string|max:20',
        ], $messages);

        // ถ้ามีการเปลี่ยนช่วงเวลา
        if ($appointment->time_slot_id != $validated['time_slot_id']) {
            $oldTimeSlot = $appointment->timeSlot;
            $newTimeSlot = TimeSlot::findOrFail($validated['time_slot_id']);

            // ตรวจสอบว่าวันที่ของช่วงเวลาใหม่เป็นวันหยุดหรือไม่
            $selectedDate = $newTimeSlot->date->format('Y-m-d');
            $isHoliday = DB::connection('pgsql')
                ->table('holiday')
                ->where('holiday_date', $selectedDate)
                ->exists();

            if ($isHoliday) {
                // ดึงข้อมูลวันหยุด
                $holiday = DB::connection('pgsql')
                    ->table('holiday')
                    ->where('holiday_date', $selectedDate)
                    ->first();

                $holidayName = $holiday ? $holiday->day_name : 'วันหยุด';

                return back()->withErrors(['time_slot_id' => "ไม่สามารถนัดหมายในวันหยุด ({$holidayName}) กรุณาเลือกวันอื่น"])
                    ->withInput();
            }

            // ตรวจสอบว่าช่วงเวลาใหม่ยังว่างหรือไม่
            if (!$newTimeSlot->isAvailable()) {
                return back()->withErrors(['time_slot_id' => 'ช่วงเวลานี้ไม่ว่างแล้ว กรุณาเลือกช่วงเวลาอื่น'])
                    ->withInput();
            }

            // ตรวจสอบว่าผู้ป่วยคนนี้มีการนัดในช่วงเวลาเดียวกันแล้วหรือไม่
            $existingAppointment = Appointment::where('patient_cid', $validated['patient_cid'])
                ->where('time_slot_id', $validated['time_slot_id'])
                ->where('id', '!=', $appointment->id) // ไม่นับการนัดหมายปัจจุบัน
                ->where('status', '!=', 'cancelled')
                ->first();

            if ($existingAppointment) {
                return back()->withErrors(['time_slot_id' => 'ผู้ป่วยนี้มีการนัดหมายในช่วงเวลานี้แล้ว'])
                    ->withInput();
            }

            // ตรวจสอบว่าผู้ป่วยคนนี้มีการนัดในวันเดียวกันกับคลินิกเดียวกันแล้วหรือไม่
            $sameDay = Carbon::parse($newTimeSlot->date)->format('Y-m-d');
            $sameDayAppointment = Appointment::whereHas('timeSlot', function ($query) use ($sameDay) {
                $query->whereDate('date', $sameDay);
            })
                ->where('patient_cid', $validated['patient_cid'])
                ->where('clinic_id', $newTimeSlot->clinic_id)
                ->where('id', '!=', $appointment->id) // ไม่นับการนัดหมายปัจจุบัน
                ->where('status', '!=', 'cancelled')
                ->first();

            if ($sameDayAppointment) {
                return back()->withErrors(['time_slot_id' => 'ผู้ป่วยนี้มีการนัดหมายในคลินิกนี้ในวันเดียวกันแล้ว'])
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
                    'patient_phone' => $validated['patient_phone'], // เพิ่มบรรทัดนี้
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
                'patient_phone' => $validated['patient_phone'], // เพิ่มบรรทัดนี้
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
        $oldStatus = 'pending'; // The cancel method only works on pending appointments
        $newStatus = 'cancelled';
        // Update status to cancelled
        $appointment->update(['status' => 'cancelled']);
        TelegramNotificationService::notifyUserStatusUpdate($appointment, $oldStatus, $newStatus);
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

        // กำหนดข้อความความผิดพลาดเป็นภาษาไทย
        $messages = [
            'status.required' => 'กรุณาเลือกสถานะ',
            'status.in' => 'สถานะที่เลือกไม่ถูกต้อง',
        ];

        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,cancelled,completed',
        ], $messages);

        $oldStatus = $appointment->getOriginal('status');
        $newStatus = $validated['status'];

        // ตรวจสอบการเปลี่ยนสถานะที่ไม่ถูกต้อง
        $invalidTransitions = [
            'cancelled' => ['pending'], // ไม่อนุญาตให้เปลี่ยนจาก cancelled เป็น pending
            'completed' => ['pending'], // ไม่อนุญาตให้เปลี่ยนจาก completed เป็น pending
        ];

        // ตรวจสอบว่าการเปลี่ยนสถานะนี้ถูกต้องหรือไม่
        if (isset($invalidTransitions[$oldStatus]) && in_array($newStatus, $invalidTransitions[$oldStatus])) {
            return redirect()->route('appointments.show', $appointment)
                ->with('error', "ไม่สามารถเปลี่ยนสถานะจาก '{$oldStatus}' เป็น '{$newStatus}' ได้");
        }

        // ถ้าเป็นการเปลี่ยนเป็นสถานะที่เหมือนเดิม ไม่ต้องทำอะไร
        if ($oldStatus === $newStatus) {
            return redirect()->route('appointments.show', $appointment)
                ->with('info', 'สถานะการนัดหมายยังคงเดิม');
        }

        // If changing to cancelled and was previously not cancelled, decrement booked count
        if ($newStatus === 'cancelled' && $oldStatus !== 'cancelled') {
            $appointment->timeSlot->decrement('booked_appointments');
        }
        // If changing from cancelled to something else, increment booked count
        else if ($oldStatus === 'cancelled' && $newStatus !== 'cancelled') {
            $appointment->timeSlot->increment('booked_appointments');
        }

        // ตรวจสอบเงื่อนไขเพิ่มเติมตามสถานะใหม่
        if ($newStatus === 'completed' && $oldStatus !== 'confirmed') {
            // อาจต้องตรวจสอบว่าการนัดหมายต้องผ่านสถานะ 'confirmed' ก่อนถึงจะเป็น 'completed' ได้
            if ($oldStatus !== 'pending') {
                return redirect()->route('appointments.show', $appointment)
                    ->with('error', 'การนัดหมายต้องผ่านการยืนยัน (confirmed) ก่อนที่จะเสร็จสิ้น');
            }
        }

        // ใช้ Transaction เพื่อความปลอดภัยของข้อมูล
        DB::beginTransaction();

        try {
            $appointment->update(['status' => $newStatus]);

            if ($oldStatus !== $newStatus) {
                // แจ้งเตือนผู้ใช้เมื่อมีการเปลี่ยนสถานะ
                TelegramNotificationService::notifyUserStatusUpdate($appointment, $oldStatus, $newStatus);

                // If status changed to confirmed, also notify admins
                if ($newStatus === 'confirmed') {
                    // Optional: Notify admins when an appointment is confirmed
                    $adminMessage = "<b>✅ การนัดหมายได้รับการยืนยันแล้ว</b>\n\n" .
                        "🏥 <b>คลินิก:</b> {$appointment->clinic->name}\n" .
                        "👨‍⚕️ <b>แพทย์:</b> {$appointment->doctor->name}\n" .
                        "📅 <b>วันที่:</b> " . \Carbon\Carbon::parse($appointment->timeSlot->date)->thaidate() . "\n" .
                        "⏰ <b>เวลา:</b> " . \Carbon\Carbon::parse($appointment->timeSlot->start_time)->format('H:i') . " - " .
                        \Carbon\Carbon::parse($appointment->timeSlot->end_time)->format('H:i') . " น.\n" .
                        "👤 <b>ผู้ป่วย:</b> {$appointment->patient_pname} {$appointment->patient_fname} {$appointment->patient_lname}\n" .
                        "🔗 <a href='" . route('appointments.show', $appointment) . "'>ดูรายละเอียดเพิ่มเติม</a>";

                    TelegramNotificationService::sendMessage(
                        $adminMessage,
                        null,
                        null,
                        'appointment_confirmed',
                        $appointment->id
                    );
                }
            }

            DB::commit();

            // แสดงข้อความสำเร็จที่แตกต่างกันตามสถานะ
            // แสดงข้อความสำเร็จที่แตกต่างกันตามสถานะ
            $statusMessages = [
                'pending' => 'สถานะการนัดหมายถูกเปลี่ยนเป็น "รอดำเนินการ" เรียบร้อยแล้ว',
                'confirmed' => 'การนัดหมายได้รับการยืนยันเรียบร้อยแล้ว',
                'cancelled' => 'การนัดหมายถูกยกเลิกเรียบร้อยแล้ว',
                'completed' => 'การนัดหมายเสร็จสิ้นเรียบร้อยแล้ว',
            ];

            $successMessage = $statusMessages[$newStatus] ?? 'อัพเดตสถานะการนัดหมายเรียบร้อยแล้ว';

            return redirect()->route('appointments.show', $appointment)
                ->with('success', $successMessage);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating appointment status: ' . $e->getMessage());
            return redirect()->route('appointments.show', $appointment)
                ->with('error', 'เกิดข้อผิดพลาดในการอัพเดตสถานะ: ' . $e->getMessage());
        }
    }
}
