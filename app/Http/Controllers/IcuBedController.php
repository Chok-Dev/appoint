<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class IcuBedController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display ICU bed status
     */
    public function index(Request $request)
    {
        try {
            // Get ICU room list
            $icuRooms = $this->getIcuRooms();

            // Get selected room (default to first ICU room)
            $selectedRoom = $request->get('room', $icuRooms->first()->roomno ?? 'ICU1');

            // Get bed status for selected room
            $bedStatus = $this->getBedStatus($selectedRoom);

            // Get room details
            $roomDetails = $this->getRoomDetails($selectedRoom);

            return view('icu.index', compact('bedStatus', 'icuRooms', 'selectedRoom', 'roomDetails'));
        } catch (\Exception $e) {
            Log::error('Error fetching ICU bed status: ' . $e->getMessage());

            return view('icu.index', [
                'bedStatus' => collect(),
                'icuRooms' => collect(),
                'selectedRoom' => 'ICU1',
                'roomDetails' => null,
                'error' => 'เกิดข้อผิดพลาดในการดึงข้อมูลสถานะเตียง: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get ICU rooms list
     */
    private function getIcuRooms()
    {
        return DB::connection('pgsql')
            ->table('roomno')
            ->where('roomno', 'like', 'ICU%')
            ->orderBy('roomno')
            ->get(['roomno', 'name']);
    }

    /**
     * Get bed status for specified room
     */
    private function getBedStatus($roomno)
    {
        $query = "
        SELECT 
    b.bedno,
    CASE 
        WHEN EXISTS (
            SELECT 1
            FROM iptadm i1
            LEFT JOIN ipt i2 ON i2.an = i1.an
            WHERE i1.bedno = b.bedno
              AND (i2.dchtype = ' ' OR i2.dchtype IS NULL)
        )
        THEN 'ไม่ว่าง'
        ELSE 'ว่าง'
    END AS bed_status,
    -- ดึงข้อมูลผู้ป่วยสำหรับเตียงที่ไม่ว่าง
    (SELECT p.pname FROM iptadm i1 
     LEFT JOIN ipt i2 ON i2.an = i1.an 
     LEFT JOIN patient p ON i2.hn = p.hn
     WHERE i1.bedno = b.bedno 
       AND (i2.dchtype = ' ' OR i2.dchtype IS NULL)
     LIMIT 1) as pname,
    (SELECT p.fname FROM iptadm i1 
     LEFT JOIN ipt i2 ON i2.an = i1.an 
     LEFT JOIN patient p ON i2.hn = p.hn
     WHERE i1.bedno = b.bedno 
       AND (i2.dchtype = ' ' OR i2.dchtype IS NULL)
     LIMIT 1) as fname,
    (SELECT p.lname FROM iptadm i1 
     LEFT JOIN ipt i2 ON i2.an = i1.an 
     LEFT JOIN patient p ON i2.hn = p.hn
     WHERE i1.bedno = b.bedno 
       AND (i2.dchtype = ' ' OR i2.dchtype IS NULL)
     LIMIT 1) as lname,
    (SELECT p.hn FROM iptadm i1 
     LEFT JOIN ipt i2 ON i2.an = i1.an 
     LEFT JOIN patient p ON i2.hn = p.hn
     WHERE i1.bedno = b.bedno 
       AND (i2.dchtype = ' ' OR i2.dchtype IS NULL)
     LIMIT 1) as hn,
    -- แก้ไข: ใช้ i2.regdate และ i2.regtime แทน i1
    (SELECT i2.regdate FROM iptadm i1 
     LEFT JOIN ipt i2 ON i2.an = i1.an
     WHERE i1.bedno = b.bedno 
       AND (i2.dchtype = ' ' OR i2.dchtype IS NULL)
     LIMIT 1) as regdate,
    (SELECT i2.regtime FROM iptadm i1 
     LEFT JOIN ipt i2 ON i2.an = i1.an
     WHERE i1.bedno = b.bedno 
       AND (i2.dchtype = ' ' OR i2.dchtype IS NULL)
     LIMIT 1) as regtime
FROM bedno b
WHERE b.roomno = ? 
  AND b.bedno LIKE 'ICU%'
ORDER BY b.bedno;
        ";

        return DB::connection('pgsql')
            ->select($query, [$roomno]);
    }

    /**
     * Get room details
     */
    private function getRoomDetails($roomno)
    {
        return DB::connection('pgsql')
            ->table('roomno')
            ->where('roomno', $roomno)
            ->first();
    }

    /**
     * Get detailed patient information
     */
    public function getPatientDetails(Request $request)
    {
        // ลบฟังก์ชันนี้ออก - ไม่ต้องการรายละเอียดผู้ป่วย
        return response()->json([
            'success' => false,
            'message' => 'ฟีเจอร์นี้ถูกปิดใช้งาน'
        ], 404);
    }

    /**
     * Get patient details by AN
     */
    private function getPatientDetailsByAn($an)
    {
        // ลบฟังก์ชันนี้ออก - ไม่ต้องการรายละเอียดผู้ป่วย
        return null;
    }

    /**
     * API endpoint for real-time updates
     */
    public function apiStatus(Request $request)
    {
        try {
            $roomno = $request->get('room', 'ICU1');
            $bedStatus = $this->getBedStatus($roomno);

            // แปลง object เป็น array สำหรับ JSON response
            $formattedBedStatus = collect($bedStatus)->map(function ($bed) {
                return [
                    'bedno' => $bed->bedno,
                    'bed_status' => $bed->bed_status,
                    'pname' => $bed->pname,
                    'fname' => $bed->fname,
                    'lname' => $bed->lname,
                    'hn' => $bed->hn,
                    'regdate' => $bed->regdate,
                    'regtime' => $bed->regtime
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formattedBedStatus,
                'room' => $roomno,
                'last_updated' => now()->format('d/m/Y H:i:s')
            ]);
        } catch (\Exception $e) {
            Log::error('Error in ICU API status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'เกิดข้อผิดพลาดในการดึงข้อมูล',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
