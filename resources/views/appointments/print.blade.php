<!DOCTYPE html>
<html lang="th">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ใบนัดหมาย</title>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Thai:wght@100;200;300;400;500;600;700&display=swap');

    * {
      font-family: 'IBM Plex Sans Thai', sans-serif;
    }
  </style>
  <style>
    body {

      line-height: 1.6;
      color: #333;
      margin: 0;
      padding: 0;
    }

    .print-container {
      width: 800px;
      margin: 0 auto;
      padding: 20px;
    }

    .header {
      text-align: center;
      margin-bottom: 20px;
      border-bottom: 2px solid #000;
      padding-bottom: 10px;
    }

    .hospital-name {
      font-size: 24px;
      font-weight: bold;
      margin-bottom: 5px;
    }

    .title {
      font-size: 22px;
      font-weight: bold;
      margin: 15px 0;
      text-align: center;
    }

    .appointment-info,
    .patient-info {
      margin-bottom: 20px;
    }

    .section-title {
      font-size: 18px;
      font-weight: bold;
      margin-bottom: 10px;
      padding-bottom: 5px;
      border-bottom: 1px solid #ccc;
    }

    .info-row {
      display: flex;
      margin-bottom: 10px;
    }

    .info-label {
      font-weight: bold;
      width: 180px;
    }

    .info-value {
      flex: 1;
    }

    .note-box {
      border: 1px dashed #888;
      padding: 10px 15px;
      margin: 15px 0;
      background-color: #f9f9f9;
    }

    .footer {
      margin-top: 30px;
      font-size: 14px;
      text-align: center;
      color: #666;
    }

    .barcode {
      text-align: start;
      margin: 20px 0;
      font-family: monospace;
      font-size: 14px;
    }

    .qr-code {
      text-align: center;
      margin: 20px 0;
    }

    @media print {
      .no-print {
        display: none;
      }

      body {
        margin: 0;
        padding: 0;
      }

      .print-container {
        width: 100%;
        padding: 0;
      }

      /* ส่วนที่จะไม่แสดงเมื่อไม่เลือก "Headers and footers" */
      @page :header {
        display: none;
      }

      @page :footer {
        display: none;
      }
    }
  </style>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
</head>

<body>
  <div class="print-container">
    <div class="header ">
      <img src="{{ asset('media/logo/hospital-logo.png') }}" alt="Hospital Logo" style="height: 100px;">
      <div>
        <p class="hospital-name">โรงพยาบาลหนองหาน</p>
        <p class="fs-6">โทรศัพท์-อุบัติเหตุฉุกเฉิน 042-261135-6 ต่อ 111</p>

      </div>
      <!-- อื่นๆ เหมือนเดิม -->
    </div>
    <div class="title">ใบนัดหมาย</div>

    <div class="patient-info">
      <div class="section-title">ข้อมูลผู้ป่วย</div>
      <div class="info-row">
        <div class="info-label">ชื่อ-นามสกุล:</div>
        <div class="info-value">{{ $appointment->patient_pname }} {{ $appointment->patient_fname }}
          {{ $appointment->patient_lname }}</div>
      </div>
      <div class="info-row">
        <div class="info-label">เลขประจำตัวประชาชน:</div>
        <div class="info-value">{{ $appointment->patient_cid }}</div>
      </div>
      <div class="info-row">
        <div class="info-label">เบอร์โทรศัพท์:</div>
        <div class="info-value">{{ $appointment->patient_phone ?: '-' }}</div>
      </div>
      <div class="info-row">
        <div class="info-label">HN รพ.หนองหาน:</div>
        <div class="info-value">{{ $appointment->patient_hn ?: '-' }}</div>
      </div>
      <div class="info-row">
        <div class="info-label">อายุ:</div>
        <div class="info-value">
          @if ($appointment->patient_birthdate)
            {{ \Carbon\Carbon::parse($appointment->patient_birthdate)->age }} ปี
          @elseif($appointment->patient_age)
            {{ $appointment->patient_age }} ปี
          @else
            -
          @endif
        </div>
      </div>
    </div>

    <div class="appointment-info">
      <div class="section-title">รายละเอียดการนัดหมาย</div>
      <div class="info-row">
        <div class="info-label">คลินิก:</div>
        <div class="info-value">{{ $appointment->clinic->name }}</div>
      </div>
      <div class="info-row">
        <div class="info-label">แพทย์:</div>
        <div class="info-value">{{ $appointment->doctor->name }}</div>
      </div>
      <div class="info-row">
        <div class="info-label">วันที่นัด:</div>
        <div class="info-value">{{ \Carbon\Carbon::parse($appointment->timeSlot->date)->thaidate() }}</div>
      </div>
      <div class="info-row">
        <div class="info-label">เวลา:</div>
        <div class="info-value">{{ \Carbon\Carbon::parse($appointment->timeSlot->start_time)->format('H:i') }} -
          {{ \Carbon\Carbon::parse($appointment->timeSlot->end_time)->format('H:i') }} น.</div>
      </div>
      {{--  <div class="info-row">
        <div class="info-label">รหัสการนัดหมาย:</div>
        <div class="info-value">{{ $appointment->id }}</div>
      </div> --}}
      <div class="info-row">
        <div class="info-label">วันที่ออกใบนัด:</div>
        <div class="info-value">{{ now()->thaidate() }} {{ now()->format('H:i') }} น.</div>
      </div>
      <div class="info-row">
        <div class="info-label">ผู้นัด:</div>
        <div class="info-value">{{ $appointment->user->name }} ({{ $appointment->user->department }})</div>
      </div>

      @if ($appointment->notes)
        <div class="note-box">
          <div style="font-weight: bold;">หมายเหตุ:</div>
          <div>{{ $appointment->notes }}</div>
        </div>
      @endif
    </div>

    <div class="qr-code">
      {!! QrCode::size(80)->generate(url('/appointments/' . $appointment->id)) !!}
      <div style="margin-top: 5px; font-size: 14px;">
        สแกนเพื่อดูรายละเอียดการนัดหมาย
      </div>
    </div>

    {{--  <div class="barcode">
      *A{{ str_pad($appointment->id, 8, '0', STR_PAD_LEFT) }}*
    </div>
 --}}
    <div class="footer">
      <p>กรุณานำใบนัดมาด้วยทุกครั้งที่มาพบแพทย์</p>
      <p>โปรดมาก่อนเวลานัด 30 นาที เพื่อเตรียมเอกสารและตรวจสอบสิทธิ์</p>
      <p>หากมีข้อสงสัยหรือต้องการเลื่อนนัด กรุณาติดต่อ 042-261135-6</p>
    </div>

    <div class="no-print" style="margin-top: 20px; text-align: center;">
      <button onclick="window.print();"
        style="padding: 10px 20px; background-color: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px;">
        พิมพ์ใบนัด
      </button>
      <button onclick="window.close();"
        style="padding: 10px 20px; background-color: #f44336; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; margin-left: 10px;">
        ปิด
      </button>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>
  <script>
    // Auto print when the page loads (optional)
    window.onload = function() {
      window.print();
    };
  </script>
</body>

</html>
