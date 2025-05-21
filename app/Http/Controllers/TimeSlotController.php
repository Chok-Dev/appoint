<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Group;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\TimeSlot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class TimeSlotController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin')->except(['index', 'show', 'schedule']);
    }

    public function index(Request $request)
    {
        $query = TimeSlot::with(['doctor', 'clinic']);

        // Filter by clinic if specified
        if ($request->has('clinic_id') && $request->clinic_id) {
            $query->where('clinic_id', $request->clinic_id);
        }

        // Filter by doctor if specified
        if ($request->has('doctor_id') && $request->doctor_id) {
            $query->where('doctor_id', $request->doctor_id);
        }

        // Filter by date range if specified
        if ($request->has('date_range') && $request->date_range) {
            $dateRange = explode(' - ', $request->date_range);
            if (count($dateRange) == 2) {
                try {
                    $startDate = \Carbon\Carbon::createFromFormat('d/m/Y', trim($dateRange[0]))->startOfDay();
                    $endDate = \Carbon\Carbon::createFromFormat('d/m/Y', trim($dateRange[1]))->endOfDay();
                    $query->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
                } catch (\Exception $e) {
                    // ถ้าไม่สามารถแปลงรูปแบบวันที่ได้ ให้ข้ามการกรองด้วยวันที่
                    Log::error('Error parsing date range: ' . $e->getMessage());
                }
            } else {
                // ถ้าวันที่อยู่ในรูปแบบอื่น ให้ลองใช้การค้นหาด้วยวันที่เดียว
                try {
                    $searchDate = \Carbon\Carbon::createFromFormat('d/m/Y', trim($request->date_range))->format('Y-m-d');
                    $query->whereDate('date', $searchDate);
                } catch (\Exception $e) {
                    // ถ้าไม่สามารถแปลงรูปแบบวันที่ได้ ให้ข้ามการกรองด้วยวันที่
                    Log::error('Error parsing single date: ' . $e->getMessage());
                }
            }
        } else if ($request->has('date') && $request->date) {
            // รองรับการค้นหาด้วยวันที่เดิมในรูปแบบเดิม (ถ้ามี)
            try {
                $query->whereDate('date', $request->date);
            } catch (\Exception $e) {
                Log::error('Error with legacy date filter: ' . $e->getMessage());
            }
        }

        // เรียงลำดับตามวันที่และเวลา
        $query->orderBy('date')->orderBy('start_time');

        // เพิ่ม pagination - แสดง 15 รายการต่อหน้า
        $timeSlots = $query->paginate(15)->withQueryString();

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
            'daterange' => 'required|string',
            'daycheck' => 'required|string',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'max_appointments' => 'required|integer|min:1',
            'is_active' => 'nullable',
        ]);

        // แยกค่าวันเริ่มต้นและวันสิ้นสุดจาก daterange (รูปแบบ: "YYYY/MM/DD-YYYY/MM/DD")
        $dateRange = explode('-', $validated['daterange']);
        $startDate = Carbon::createFromFormat('Y/m/d', trim($dateRange[0]));
        $endDate = Carbon::createFromFormat('Y/m/d', trim($dateRange[1]));

        // ตรวจสอบว่าหมอเชื่อมโยงกับคลินิกหรือไม่
        $doctor = Doctor::findOrFail($validated['doctor_id']);
        $clinic = Clinic::findOrFail($validated['clinic_id']);

        if (!$doctor->clinics->contains($clinic->id)) {
            return back()->withErrors(['doctor_id' => 'แพทย์ท่านนี้ไม่ได้สังกัดคลินิกที่เลือก'])
                ->withInput();
        }

        // เริ่ม transaction เพื่อสร้างหลาย timeslots
        DB::beginTransaction();

        try {
            // สร้าง Carbon instance สำหรับแต่ละวันในช่วงวันที่เลือก
            $currentDate = $startDate->copy();
            $createdCount = 0;

            // วนลูปสร้าง timeslots สำหรับทุกวันในช่วงที่เลือก
            while ($currentDate->lte($endDate)) {
                $dayOfWeek = $currentDate->dayOfWeek; // 0 = วันอาทิตย์, 6 = วันเสาร์

                $createTimeSlot = false;

                // ตรวจสอบตัวเลือกวัน
                switch ($validated['daycheck']) {
                    case 'd1': // เอาทุกวันที่เลือก
                        $createTimeSlot = true;
                        break;
                    case 'd2': // ไม่เอาวันศุกร์,เสาร์,อาทิตย์
                        $createTimeSlot = !in_array($dayOfWeek, [0, 5, 6]);
                        break;
                    case 'd3': // เอาเฉพาะวันศุกร์
                        $createTimeSlot = ($dayOfWeek == 5);
                        break;
                    case 'd4': // ไม่เอาวันเสาร์,อาทิตย์
                        $createTimeSlot = !in_array($dayOfWeek, [0, 6]);
                        break;
                    case 'd5': // เอาเฉพาะวันจันทร์
                        $createTimeSlot = ($dayOfWeek == 1);
                        break;
                    case 'd6': // ไม่เอาวันเสาร์,อาทิตย์,จันทร์
                        $createTimeSlot = !in_array($dayOfWeek, [0, 1, 6]);
                        break;
                    case 'd7': // เอาเฉพาะวันอังคาร
                        $createTimeSlot = ($dayOfWeek == 2);
                        break;
                    case 'd8': // เอาเฉพาะวันพุธ
                        $createTimeSlot = ($dayOfWeek == 3);
                        break;
                    case 'd9': // เอาเฉพาะวันพฤหัสบดี
                        $createTimeSlot = ($dayOfWeek == 4);
                        break;
                    case 'd10': // เอาเฉพาะวันเสาร์
                        $createTimeSlot = ($dayOfWeek == 6);
                        break;
                    case 'd11': // เอาเฉพาะวันอาทิตย์
                        $createTimeSlot = ($dayOfWeek == 0);
                        break;
                }

                if ($createTimeSlot) {
                    // ตรวจสอบว่ามี TimeSlot ในวันและเวลาเดียวกันหรือไม่
                    $existingTimeSlot = TimeSlot::where('date', $currentDate->format('Y-m-d'))
                        ->where('doctor_id', $validated['doctor_id'])
                        ->where('clinic_id', $validated['clinic_id'])
                        ->where('start_time', $validated['start_time'])
                        ->where('end_time', $validated['end_time'])
                        ->first();

                    if (!$existingTimeSlot) {
                        TimeSlot::create([
                            'doctor_id' => $validated['doctor_id'],
                            'clinic_id' => $validated['clinic_id'],
                            'date' => $currentDate->format('Y-m-d'),
                            'start_time' => $validated['start_time'],
                            'end_time' => $validated['end_time'],
                            'max_appointments' => $validated['max_appointments'],
                            'booked_appointments' => 0,
                            'is_active' => isset($validated['is_active']) ? true : false,
                        ]);

                        $createdCount++;
                    }
                }

                $currentDate->addDay();
            }

            DB::commit();

            if ($createdCount > 0) {
                return redirect()->route('timeslots.index')
                    ->with('success', "สร้างช่วงเวลานัดหมายสำเร็จจำนวน {$createdCount} รายการ");
            } else {
                return back()->withErrors(['daterange' => 'ไม่มีช่วงเวลาใดถูกสร้าง โปรดตรวจสอบตัวเลือกวันอีกครั้ง'])
                    ->withInput();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function show(TimeSlot $timeSlot)
    {
        // โหลดความสัมพันธ์ที่เกี่ยวข้อง
        $timeSlot->load(['doctor', 'clinic', 'appointments.user']);

        return view('timeslots.show', compact('timeSlot'));
    }

    public function edit(TimeSlot $timeSlot)
    {
        $timeSlot->load('doctor', 'clinic');

        // อาจจะเพิ่มการดึงข้อมูลเพิ่มเติมถ้าจำเป็น
        $clinics = Clinic::all();
        $doctors = Doctor::all();

        return view('timeslots.edit', compact('timeSlot', 'clinics', 'doctors'));
    }

    public function update(Request $request, TimeSlot $timeSlot)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'max_appointments' => 'required|integer|min:' . $timeSlot->booked_appointments,
            'is_active' => 'nullable',
        ]);

        // คลินิกและแพทย์ไม่สามารถเปลี่ยนแปลงได้ในหน้าแก้ไข
        // เราเก็บค่าที่มาจาก hidden inputs
        $validated['clinic_id'] = $request->input('clinic_id');
        $validated['doctor_id'] = $request->input('doctor_id');

        // สำหรับ is_active ซึ่งเป็น checkbox
        $validated['is_active'] = $request->has('is_active');

        // อัพเดท TimeSlot
        $timeSlot->update($validated);

        return redirect()->route('timeslots.show', $timeSlot)
            ->with('success', 'อัพเดทช่วงเวลานัดหมายเรียบร้อยแล้ว');
    }

    public function destroy(TimeSlot $timeSlot)
    {
        DB::beginTransaction();
        try {
            // Force delete related appointments
            $timeSlot->appointments()->delete();

            // Force delete the time slot
            $timeSlot->delete();

            DB::commit();
            return redirect()->route('timeslots.index')
                ->with('success', 'Time slot deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['delete' => 'Error deleting time slot: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the doctor's schedule in a calendar view
     */
    public function schedule(Request $request)
    {
        $query = TimeSlot::with(['doctor', 'clinic'])
            ->where('date', '>=', Carbon::today()->subWeek())
            ->where('date', '<=', Carbon::today()->addMonths(2));

        // Filter by doctor if specified
        if ($request->has('doctor_id') && $request->doctor_id) {
            $query->where('doctor_id', $request->doctor_id);
        }

        // Filter by clinic if specified
        if ($request->has('clinic_id') && $request->clinic_id) {
            $query->where('clinic_id', $request->clinic_id);
        }
            
        $timeSlots = $query->get();
        
        // Generate color mapping for clinics
        $clinics = Clinic::all();
        $doctors = Doctor::all();
        
        // Define a set of colors for clinics
        $colors = [
            '#FFB300', // Primary blue
            '#71BB4D', // Green
            '#F5A623', // Orange
            '#D84315', // Deep orange
            '#673AB7', // Deep purple
            '#00ACC1', // Cyan
            '#EC407A', // Pink
            '#5D4037', // Brown
            '#455A64', // Blue grey
            '#7986CB', // Indigo
            '#C0CA33', // Lime
            '#3788d8', // Amber
        ];
        
        // Create color mapping for clinics
        $clinicColors = [];
        foreach ($clinics as $index => $clinic) {
            $clinicColors[$clinic->id] = $colors[$index % count($colors)];
        }
        
        // Get holidays from PostgreSQL database
        try {
            $holidays = [];
            $holidaysQuery = DB::connection('pgsql')
                ->table('holiday')
                ->whereRaw("EXTRACT(YEAR FROM holiday_date) = ?", [Carbon::today()->year])
                ->select('holiday_date', 'day_name')
                ->get();
                
            foreach ($holidaysQuery as $holiday) {
                $holidayDate = Carbon::parse($holiday->holiday_date)->format('Y-m-d');
                $holidays[] = [
                    'title' => $holiday->day_name,
                    'start' => $holidayDate,
                    /* 'display' => 'background', */
                    'backgroundColor' => '#ff3333', // Light red background
                    'classNames' => ['holiday-event'],
                    'allDay' => true
                ];
            }
        } catch (\Exception $e) {
            // If cannot connect to PostgreSQL or table doesn't exist, use hardcoded holidays
            Log::error('Error getting holidays: ' . $e->getMessage());
            
            // Hardcoded holidays for Thailand 2025 (for demonstration)
           /*  $thaiHolidays = [
                ['date' => '2025-01-01', 'name' => 'วันขึ้นปีใหม่'],
                ['date' => '2025-02-10', 'name' => 'วันมาฆบูชา'],
                ['date' => '2025-04-06', 'name' => 'วันจักรี'],
                ['date' => '2025-04-13', 'name' => 'วันสงกรานต์'],
                ['date' => '2025-04-14', 'name' => 'วันสงกรานต์'],
                ['date' => '2025-04-15', 'name' => 'วันสงกรานต์'],
                ['date' => '2025-05-01', 'name' => 'วันแรงงานแห่งชาติ'],
                ['date' => '2025-05-04', 'name' => 'วันฉัตรมงคล'],
                ['date' => '2025-05-10', 'name' => 'วันวิสาขบูชา'],
                ['date' => '2025-06-03', 'name' => 'วันเฉลิมพระชนมพรรษาสมเด็จพระราชินี'],
                ['date' => '2025-07-28', 'name' => 'วันเฉลิมพระชนมพรรษา ร.10'],
                ['date' => '2025-08-12', 'name' => 'วันแม่แห่งชาติ'],
                ['date' => '2025-10-13', 'name' => 'วันคล้ายวันสวรรคต ร.9'],
                ['date' => '2025-10-23', 'name' => 'วันปิยมหาราช'],
                ['date' => '2025-12-05', 'name' => 'วันคล้ายวันเฉลิมพระชนมพรรษา ร.9'],
                ['date' => '2025-12-10', 'name' => 'วันรัฐธรรมนูญ'],
                ['date' => '2025-12-31', 'name' => 'วันสิ้นปี']
            ];
            
            $holidays = [];
            foreach ($thaiHolidays as $holiday) {
                $holidays[] = [
                    'title' => $holiday['name'],
                    'start' => $holiday['date'],
                    'display' => 'background',
                    'backgroundColor' => '#ffcccc', // Light red background
                    'classNames' => ['holiday-event'],
                    'allDay' => true
                ];
            } */
        }
        
        // Format events for the calendar
        $events = [];
        foreach ($timeSlots as $timeSlot) {
            // Skip inactive time slots if user is not an admin
            if (!$timeSlot->is_active && !Auth::user()->isAdmin()) {
                continue;
            }
            
            // Format the start and end times properly
            $date = $timeSlot->date->format('Y-m-d');
            $startTime = $timeSlot->start_time->format('H:i:s');
            $endTime = $timeSlot->end_time->format('H:i:s');
            
            // Format title - show how many slots available
            $available = $timeSlot->max_appointments - $timeSlot->booked_appointments;
            $title = $timeSlot->doctor->name . ' (' . $available . '/' . $timeSlot->max_appointments . ')';
            
            // Determine color based on clinic and availability
            $color = $clinicColors[$timeSlot->clinic_id] ?? '#3788d8';
            
            // For inactive slots, make them gray (admin only can see these)
            if (!$timeSlot->is_active) {
                $color = '#6c757d'; // Bootstrap gray
                $title .= ' [ปิดใช้งาน]';
            }
            if ($available == 0 && $timeSlot->is_active) {
                $title .= ' [เต็ม]';
            }
            // If fully booked, make the event more transparent
            $textColor = ($available == 0 && $timeSlot->is_active) ? '#333333' : '#FFFFFF';
            $backgroundColor = ($available == 0 && $timeSlot->is_active) ? $color . '80' : $color; // 80 = 50% opacity in hex
            $color = ($available == 0 && $timeSlot->is_active) ? '#ff9999': $color; // 80 = 50% opacity in hex
            
            $events[] = [
                'id' => $timeSlot->id,
                'title' => $title,
                'start' => $date . 'T' . $startTime,
                'end' => $date . 'T' . $endTime,
                'backgroundColor' => $backgroundColor,
                'borderColor' => $color,
                'textColor' => $textColor,
                'extendedProps' => [
                    'doctor' => $timeSlot->doctor->name,
                    'clinic' => $timeSlot->clinic->name,
                    'maxAppointments' => $timeSlot->max_appointments,
                    'bookedAppointments' => $timeSlot->booked_appointments,
                    'isActive' => $timeSlot->is_active,
                    'timeslot' => "$startTime - $endTime",
                ]
            ];
        }
        
        // Combine timeslot events with holidays
        $events = array_merge($events, $holidays);
        
        // Create a flag to check if holidays are shown
        $showHolidays = $request->has('show_holidays') ? (bool)$request->show_holidays : true;
        
        return view('timeslots.schedule', compact('events', 'clinics', 'doctors', 'clinicColors', 'showHolidays'));
    }
}