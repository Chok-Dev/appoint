@extends('layouts.backend')

@section('css')
  <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.min.css">
  <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
@endsection

@section('js')
  <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
  <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
  <script
    src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js">
  </script>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // ซ่อนฟอร์มกรอกข้อมูลผู้ป่วยเมื่อโหลดหน้าครั้งแรก
      $('#patient-info-form').hide();

      // แสดงข้อมูลผู้ป่วยปัจจุบันที่กำลังแก้ไข
      $('#search-result').html(`
        <div class="alert alert-success">
            <h5>ข้อมูลผู้ป่วยปัจจุบัน</h5>
            <p>
                ชื่อ-นามสกุล: {{ $appointment->patient_pname }} {{ $appointment->patient_fname }} {{ $appointment->patient_lname }}<br>
                เลขบัตรประชาชน: {{ $appointment->patient_cid }}<br>
                HN: {{ $appointment->patient_hn ?: 'ไม่มีข้อมูล' }}<br>
                อายุ: {{ $appointment->patient_age ?: 'ไม่มีข้อมูล' }} ปี<br>
                เบอร์โทรศัพท์: {{ $appointment->patient_phone ?: 'ไม่มีข้อมูล' }}
            </p>
            <button type="button" class="btn btn-secondary btn-sm" id="change-patient">
                เปลี่ยนผู้ป่วย
            </button>
        </div>
      `);

      // เมื่อคลิกปุ่มเปลี่ยนผู้ป่วย
      $(document).on('click', '#change-patient', function() {
        // ล้างข้อมูลผู้ป่วยใน hidden fields
        $('#patient_cid').val('');
        $('#patient_hn').val('');
        $('#patient_pname').val('');
        $('#patient_fname').val('');
        $('#patient_lname').val('');
        $('#patient_birthdate').val('');
        $('#patient_age').val('');
        $('#patient_phone').val('');

        // ล้างผลการค้นหา
        $('#search-result').html('');
        $('#search-term').val('');
        $('#search-form').show();
      });

      // เมื่อคลิกปุ่มเปลี่ยนผู้ป่วย
      $(document).on('click', '#clear-patient', function() {
        // ล้างข้อมูลผู้ป่วยใน hidden fields
        $('#patient_cid').val('');
        $('#patient_hn').val('');
        $('#patient_pname').val('');
        $('#patient_fname').val('');
        $('#patient_lname').val('');
        $('#patient_birthdate').val('');
        $('#patient_age').val('');
        $('#patient_phone').val('');

        // ล้างผลการค้นหา
        $('#search-result').html('');
        $('#search-term').val('');
      });

      // เมื่อคลิกปุ่มค้นหา
      $('#search-patient-btn').click(function() {
        const searchTerm = $('#search-term').val();
        const searchType = $('#search-type').val();

        if (!searchTerm) {
          alert('กรุณากรอกคำค้นหา');
          return;
        }

        // ตรวจสอบความยาวของคำค้นหา (ควรมีอย่างน้อย 2 ตัวอักษร)
        if (searchTerm.length < 13 && searchType == "cid") {
          alert('กรุณากรอกคำค้นหาอย่างน้อย 13 ตัวอักษร');
          return;
        } else if (searchTerm.length < 7 && searchType == "hn") {
          alert('กรุณากรอกคำค้นหาอย่างน้อย 7 ตัวอักษร');
          return;
        }

        // แสดง loading
        $('#search-result').html(
          '<div class="d-flex justify-content-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>'
        );

        // ส่ง AJAX request เพื่อค้นหาข้อมูลผู้ป่วย
        $.ajax({
          url: "{{ route('search.patient') }}",
          type: "GET",
          dataType: "json",
          data: {
            search_term: searchTerm,
            search_type: searchType
          },
          success: function(response) {
            if (response.success && response.data.length > 0) {
              // พบข้อมูลผู้ป่วย
              const patients = response.data;
              let html = '<div class="alert alert-success">';

              if (patients.length === 1) {
                // กรณีพบข้อมูลผู้ป่วยเพียงคนเดียว
                const patient = patients[0];

                // คำนวณอายุ
                let age = '';
                if (patient.birthdate) {
                  age = moment().diff(moment(patient.birthdate), 'years');
                } else {
                  age = 'ไม่มีข้อมูล';
                }

                html += `
                <h5>พบข้อมูลผู้ป่วย HN: ${patient.patient_hn || 'ไม่มีข้อมูล'}</h5>
                <p>
                    ชื่อ-นามสกุล: ${patient.pname} ${patient.fname} ${patient.lname}<br>
                    เลขบัตรประชาชน: ${patient.cid || 'ไม่มีข้อมูล'}<br>
                    อายุ: ${age} ปี<br>
                    เบอร์โทรศัพท์: ${patient.mobile_phone || 'ไม่มีข้อมูล'}
                </p>
                <button type="button" class="btn btn-primary btn-sm select-patient" 
                    data-cid="${patient.cid}"
                    data-hn="${patient.patient_hn || ''}"
                    data-pname="${patient.pname || ''}"
                    data-fname="${patient.fname || ''}"
                    data-lname="${patient.lname || ''}"
                    data-birthdate="${patient.birthdate || ''}"
                    data-age="${age !== 'ไม่มีข้อมูล' ? age : ''}"
                    data-phone="${patient.mobile_phone || ''}">
                    เลือกผู้ป่วยนี้
                </button>
            `;
              } else {
                // กรณีพบข้อมูลผู้ป่วยหลายคน
                html += `<h5>พบข้อมูลผู้ป่วย ${patients.length} คน</h5>`;
                html += '<div class="table-responsive"><table class="table table-sm table-bordered">';
                html +=
                  '<thead><tr><th>HN</th><th>เลขบัตรประชาชน</th><th>ชื่อ-นามสกุล</th><th>เบอร์โทรศัพท์</th><th>การจัดการ</th></tr></thead>';
                html += '<tbody>';

                patients.forEach(function(patient) {
                  // คำนวณอายุ
                  let age = '';
                  if (patient.birthdate) {
                    age = moment().diff(moment(patient.birthdate), 'years');
                  } else {
                    age = 'ไม่มีข้อมูล';
                  }

                  html += `<tr>
                    <td>${patient.patient_hn || 'ไม่มีข้อมูล'}</td>
                    <td>${patient.cid || 'ไม่มีข้อมูล'}</td>
                    <td>${patient.pname} ${patient.fname} ${patient.lname}</td>
                    <td>${patient.mobile_phone || 'ไม่มีข้อมูล'}</td>
                    <td>
                        <button type="button" class="btn btn-primary btn-sm select-patient" 
                            data-cid="${patient.cid}"
                            data-hn="${patient.patient_hn || ''}"
                            data-pname="${patient.pname || ''}"
                            data-fname="${patient.fname || ''}"
                            data-lname="${patient.lname || ''}"
                            data-birthdate="${patient.birthdate || ''}"
                            data-age="${age !== 'ไม่มีข้อมูล' ? age : ''}"
                            data-phone="${patient.mobile_phone || ''}">
                            เลือก
                        </button>
                    </td>
                </tr>`;
                });

                html += '</tbody></table></div>';
              }

              html += '</div>';
              $('#search-result').html(html);

              // เมื่อคลิกปุ่มเลือกผู้ป่วย
              $('.select-patient').click(function() {
                const patientData = $(this).data();

                // เก็บข้อมูลผู้ป่วยใน hidden fields
                $('#patient_cid').val(patientData.cid);
                $('#patient_hn').val(patientData.hn);
                $('#patient_pname').val(patientData.pname);
                $('#patient_fname').val(patientData.fname);
                $('#patient_lname').val(patientData.lname);
                $('#patient_birthdate').val(patientData.birthdate);
                $('#patient_age').val(patientData.age);
                $('#patient_phone').val(patientData.phone);

                console.log('Patient data set to hidden fields:', {
                  cid: $('#patient_cid').val(),
                  hn: $('#patient_hn').val(),
                  pname: $('#patient_pname').val(),
                  fname: $('#patient_fname').val(),
                  lname: $('#patient_lname').val(),
                  age: $('#patient_age').val(),
                  phone: $('#patient_phone').val()
                });

                // แสดงข้อมูลที่เลือก
                $('#search-result').html(`
                <div class="alert alert-success">
                    <h5>ข้อมูลผู้ป่วยที่เลือก</h5>
                    <p>
                        ชื่อ-นามสกุล: ${patientData.pname} ${patientData.fname} ${patientData.lname}<br>
                        เลขบัตรประชาชน: ${patientData.cid}<br>
                        HN: ${patientData.hn || 'ไม่มีข้อมูล'}<br>
                        อายุ: ${patientData.age || 'ไม่มีข้อมูล'} ปี<br>
                        เบอร์โทรศัพท์: ${patientData.phone || 'ไม่มีข้อมูล'}
                    </p>
                    <button type="button" class="btn btn-secondary btn-sm" id="clear-patient">
                        เปลี่ยนผู้ป่วย
                    </button>
                </div>
            `);

                // ซ่อนฟอร์มกรอกข้อมูลผู้ป่วย
                $('#patient-info-form').hide();
              });
            } else {
              // ไม่พบข้อมูลผู้ป่วย
              $('#search-result').html(`
                <div class="alert alert-warning">
                    <h5>ไม่พบข้อมูลผู้ป่วย</h5>
                    <p>กรุณากรอกข้อมูลผู้ป่วยด้านล่าง</p>
                </div>
              `);

              // แสดงฟอร์มกรอกข้อมูลผู้ป่วย
              $('#patient-info-form').show();

              // ตั้งค่า cid ตามการค้นหา (ถ้าเป็นการค้นหาด้วย cid)
              if (searchType === 'cid') {
                $('#patient_cid').val(searchTerm);
              } else {
                $('#patient_cid').val('');
              }

              $('#manual_pname').val('');
              $('#manual_fname').val('');
              $('#manual_lname').val('');
              $('#manual_age').val('');
              $('#manual_phone').val('');
            }
          },
          error: function(xhr, status, error) {
            console.error('AJAX error:', error, xhr);
            $('#search-result').html(`
              <div class="alert alert-danger">
                  <h5>เกิดข้อผิดพลาดในการค้นหา</h5>
                  <p>${error}</p>
              </div>
            `);

            // แสดงฟอร์มกรอกข้อมูลผู้ป่วย
            $('#patient-info-form').show();
          }
        });
      });

      // ตั้งค่ากลุ่มงาน คลินิก และแพทย์ตามข้อมูลเดิม
      $('#group_id').val({{ $appointment->clinic->group_id }});

      // เมื่อตั้งค่ากลุ่มงานแล้ว ให้โหลดคลินิกและตั้งค่าคลินิกเริ่มต้น
      const groupId = {{ $appointment->clinic->group_id }};
      $('#clinic-loading').show();

      $.ajax({
        url: "{{ route('get.clinics.by.group') }}",
        type: "GET",
        dataType: "json",
        data: {
          group_id: groupId
        },
        success: function(data) {
          $('#clinic-loading').hide();

          if (data && data.length > 0) {
            $('#clinic_id').empty().append('<option value="">-- เลือกคลินิก --</option>');

            $.each(data, function(key, value) {
              $('#clinic_id').append('<option value="' + value.id + '">' + value.name + '</option>');
            });

            $('#clinic_id').prop('disabled', false);

            // ตั้งค่าคลินิกเริ่มต้น
            $('#clinic_id').val({{ $appointment->clinic_id }});

            // เมื่อตั้งค่าคลินิกแล้ว ให้โหลดแพทย์และตั้งค่าแพทย์เริ่มต้น
            const clinicId = {{ $appointment->clinic_id }};
            $('#doctor-loading').show();

            $.ajax({
              url: "{{ route('get.doctors') }}",
              type: "GET",
              dataType: "json",
              data: {
                clinic_id: clinicId
              },
              success: function(docData) {
                $('#doctor-loading').hide();

                if (docData && docData.length > 0) {
                  $('#doctor_id').empty().append('<option value="">-- เลือกแพทย์ --</option>');

                  $.each(docData, function(key, value) {
                    $('#doctor_id').append('<option value="' + value.id + '">' + value.name +
                      '</option>');
                  });

                  $('#doctor_id').prop('disabled', false);

                  // ตั้งค่าแพทย์เริ่มต้น
                  $('#doctor_id').val({{ $appointment->doctor_id }});

                  // เมื่อตั้งค่าแพทย์แล้ว ให้โหลดวันที่และช่วงเวลา
                  const doctorId = {{ $appointment->doctor_id }};
                  $('#date-loading').show();

                  $.ajax({
                    url: "{{ route('get.available.dates') }}",
                    type: "GET",
                    dataType: "json",
                    data: {
                      clinic_id: clinicId,
                      doctor_id: doctorId
                    },
                    success: function(response) {
                      $('#date-loading').hide();

                      const availableDates = response.dates || [];
                      const holidays = response.holidays || {};

                      // กรณีที่เป็นการแก้ไข ให้เพิ่มวันที่ปัจจุบันเข้าไปด้วย
                      const currentDate = "{{ $appointment->timeSlot->date->format('Y-m-d') }}";
                      if (!availableDates.includes(currentDate)) {
                        availableDates.push(currentDate);
                        availableDates.sort(); // เรียงวันที่ใหม่
                      }

                      // ตรวจสอบว่ามีวันที่ให้เลือกหรือไม่
                      if (availableDates && availableDates.length > 0) {
                        // สร้าง datepicker และจำกัดให้เลือกได้เฉพาะวันที่มี time slots ว่าง
                        $('#date').daterangepicker({
                          "singleDatePicker": true,
                          opens: 'center',
                          "minDate": moment().format('YYYY-MM-DD'), // ไม่ให้เลือกวันที่ผ่านมาแล้ว
                          "locale": {
                            "format": "YYYY-MM-DD",
                            "separator": "-",
                            "applyLabel": "ตกลง",
                            "cancelLabel": "ยกเลิก",
                            "fromLabel": "จาก",
                            "toLabel": "ถึง",
                            "customRangeLabel": "Custom",
                            "daysOfWeek": [
                              "อา.", "จ.", "อ.", "พุธ.", "พฤ.", "ศ.", "ส."
                            ],
                            "monthNames": [
                              "ม.ค.", "ก.พ.", "มี.ค.", "เม.ย.", "พ.ค.", "มิ.ย.",
                              "ก.ค.", "ส.ค.", "ก.ย.", "ต.ค.", "พ.ย.", "ธ.ค."
                            ],
                            "firstDay": 1
                          },
                          isInvalidDate: function(date) {
                            const formattedDate = date.format('YYYY-MM-DD');
                            return !availableDates.includes(formattedDate);
                          },
                          isCustomDate: function(date) {
                            const formattedDate = date.format('YYYY-MM-DD');
                            if (holidays[formattedDate]) {
                              return ['holiday', 'available']; // CSS class สำหรับวันหยุด
                            }
                            return availableDates.includes(formattedDate) ? 'available' :
                              '';
                          }
                        });

                        // เพิ่ม CSS สำหรับวันหยุด
                        $('<style>')
                          .text(
                            '.holiday { background-color: #ffdddd !important; color: #ff0000 !important; }'
                          )
                          .appendTo('head');

                        $('#date').prop('disabled', false);

                        // ตั้งค่าวันที่เริ่มต้น
                        $('#date').val(currentDate);

                        // ตรวจสอบว่าวันที่ปัจจุบันเป็นวันหยุดหรือไม่
                        if (holidays[currentDate]) {
                          $('#date-holiday-warning').remove();
                          $(`<div id="date-holiday-warning" class="alert alert-danger mt-2">
                            <i class="fa fa-exclamation-triangle me-1"></i> วันที่คุณเลือกเป็นวันหยุด: ${holidays[currentDate].day_name}
                            <br><strong>ไม่สามารถนัดหมายในวันหยุดได้ กรุณาเลือกวันอื่น</strong>
                          </div>`).insertAfter('#date');

                          // ล้างและปิดใช้งานช่องเลือกเวลา
                          $('#time_slot_id').empty().append(
                            '<option value="">-- ไม่สามารถนัดหมายในวันหยุด --</option>');
                          $('#time_slot_id').prop('disabled', true);
                          $('#time-message').hide();
                        } else {
                          // โหลดช่วงเวลาที่มีอยู่สำหรับวันที่
                          loadTimeSlots();
                        }

                        // ตั้งค่า event สำหรับเมื่อเลือกวันที่
                        $('#date').on('apply.daterangepicker', function(ev, picker) {
                          const selectedDate = picker.startDate.format('YYYY-MM-DD');

                          if (holidays[selectedDate]) {
                            $('#date-holiday-warning').remove();
                            $(`<div id="date-holiday-warning" class="alert alert-danger mt-2">
                              <i class="fa fa-exclamation-triangle me-1"></i> วันที่คุณเลือกเป็นวันหยุด: ${holidays[selectedDate].day_name}
                              <br><strong>ไม่สามารถนัดหมายในวันหยุดได้ กรุณาเลือกวันอื่น</strong>
                            </div>`).insertAfter('#date');

                            // ล้างและปิดใช้งานช่องเลือกเวลา
                            $('#time_slot_id').empty().append(
                              '<option value="">-- ไม่สามารถนัดหมายในวันหยุด --</option>');
                            $('#time_slot_id').prop('disabled', true);
                            $('#time-message').hide();
                          } else {
                            $('#date-holiday-warning').remove();
                            loadTimeSlots();
                          }
                        });
                      }
                    },
                    error: function(xhr, status, error) {
                      $('#date-loading').hide();
                      console.error('AJAX error:', error, xhr);
                    }
                  });
                }
              },
              error: function(xhr, status, error) {
                $('#doctor-loading').hide();
                console.error('AJAX error:', error, xhr);
              }
            });
          }
        },
        error: function(xhr, status, error) {
          $('#clinic-loading').hide();
          console.error('AJAX error:', error, xhr);
        }
      });

      // เมื่อกลุ่มงานถูกเลือก ให้โหลดคลินิกที่เกี่ยวข้อง
      $('#group_id').change(function() {
        const groupId = $(this).val();

        // รีเซ็ตค่าเดิม
        $('#clinic_id').empty().append('<option value="">-- เลือกคลินิก --</option>').prop('disabled', true);
        $('#doctor_id').empty().append('<option value="">-- เลือกแพทย์ --</option>').prop('disabled', true);
        $('#date').val('').prop('disabled', true);
        $('#time_slot_id').empty().append('<option value="">-- เลือกช่วงเวลา --</option>').prop('disabled', true);

        if (groupId) {
          // แสดง loading spinner
          $('#clinic-loading').show();

          // ส่ง AJAX เพื่อดึงข้อมูลคลินิกที่อยู่ในกลุ่มงานนี้
          $.ajax({
            url: "{{ route('get.clinics.by.group') }}",
            type: "GET",
            dataType: "json",
            data: {
              group_id: groupId
            },
            success: function(data) {
              $('#clinic-loading').hide();
              console.log('Clinics data:', data);

              if (data && data.length > 0) {
                // เคลียร์และเพิ่มตัวเลือกเริ่มต้น
                $('#clinic_id').empty().append('<option value="">-- เลือกคลินิก --</option>');

                // เพิ่มตัวเลือกคลินิกจากข้อมูลที่ได้
                $.each(data, function(key, value) {
                  $('#clinic_id').append('<option value="' + value.id + '">' + value.name +
                    '</option>');
                });

                // เปิดใช้งาน dropdown คลินิก
                $('#clinic_id').prop('disabled', false);
              } else {
                // กรณีไม่พบคลินิกในกลุ่มงานนี้
                $('#clinic_id').append('<option disabled>ไม่พบคลินิกในกลุ่มงานนี้</option>');
              }
            },
            error: function(xhr, status, error) {
              // กรณีเกิดข้อผิดพลาดในการเรียก API
              $('#clinic-loading').hide();
              console.error('AJAX error:', error, xhr);
              $('#clinic_id').empty().append(
                '<option value="">-- เกิดข้อผิดพลาดในการโหลดข้อมูล --</option>');
            }
          });
        }
      });

      // เมื่อคลินิกถูกเลือก ให้โหลดแพทย์ที่เกี่ยวข้อง
      $('#clinic_id').change(function() {
        const clinicId = $(this).val();
        if (clinicId) {
          // รีเซ็ตค่าเดิม
          $('#doctor_id').empty().append('<option value="">-- เลือกแพทย์ --</option>').prop('disabled', true);
          $('#date').val('').prop('disabled', true);
          $('#time_slot_id').empty().append('<option value="">-- เลือกช่วงเวลา --</option>').prop('disabled',
            true);

          // แสดง loading spinner
          $('#doctor-loading').show();

          // ใช้ AJAX แบบ jQuery
          $.ajax({
            url: "{{ route('get.doctors') }}",
            type: "GET",
            dataType: "json",
            data: {
              clinic_id: clinicId
            },
            success: function(data) {
              $('#doctor-loading').hide();
              console.log('Doctors data:', data);
              $('#doctor_id').empty().append('<option value="">-- เลือกแพทย์ --</option>');

              if (data && data.length > 0) {
                $.each(data, function(key, value) {
                  $('#doctor_id').append('<option value="' + value.id + '">' + value.name +
                    '</option>');
                });
                $('#doctor_id').prop('disabled', false);
              } else {
                $('#doctor_id').append('<option disabled>ไม่พบแพทย์ในคลินิกนี้</option>');
                $('#doctor_id').prop('disabled', true);
              }
            },
            error: function(xhr, status, error) {
              $('#doctor-loading').hide();
              console.error('AJAX error:', error, xhr);
              $('#doctor_id').empty().append(
                '<option value="">-- เกิดข้อผิดพลาดในการโหลดข้อมูล --</option>');
              $('#doctor_id').prop('disabled', true);
            }
          });
        } else {
          $('#doctor_id').empty().append('<option value="">-- เลือกแพทย์ --</option>');
          $('#doctor_id').prop('disabled', true);

          $('#date').val('').prop('disabled', true);
          $('#time_slot_id').empty().append('<option value="">-- เลือกช่วงเวลา --</option>');
          $('#time_slot_id').prop('disabled', true);
        }
      });

      // เมื่อแพทย์ถูกเลือก
      $('#doctor_id').change(function() {
        // รีเซ็ตค่าเวลาและวันที่
        $('#date').val('').prop('disabled', true);
        $('#time_slot_id').empty().append('<option value="">-- เลือกช่วงเวลา --</option>').prop('disabled', true);

        const clinicId = $('#clinic_id').val();
        const doctorId = $(this).val();

        if (clinicId && doctorId) {
          // แสดง loading
          $('#date-loading').show();
          $('#date-message').hide();

          // ใช้ AJAX เพื่อดึงวันที่ที่มีช่วงเวลาว่าง
          $.ajax({
            url: "{{ route('get.available.dates') }}",
            type: "GET",
            dataType: "json",
            data: {
              clinic_id: clinicId,
              doctor_id: doctorId
            },
            success: function(response) {
              $('#date-loading').hide();
              console.log('Available dates response:', response);
              // แสดงข้อความแจ้งเตือนถ้ามี
              if (response.message && !response.success) {
                $('#date-message').html(`<div class="alert alert-warning mt-2">${response.message}</div>`)
                  .show();
              } else if (response.message) {
                $('#date-message').html(`<div class="alert alert-info mt-2">${response.message}</div>`)
                  .show();
              } else {
                $('#date-message').hide();
              }

              const availableDates = response.dates || [];
              const holidays = response.holidays || {};

              // ตรวจสอบว่ามีวันที่ให้เลือกหรือไม่
              if (availableDates && availableDates.length > 0) {
                // สร้าง datepicker และจำกัดให้เลือกได้เฉพาะวันที่มี time slots ว่าง
                $('#date').daterangepicker({
                  "singleDatePicker": true,
                  opens: 'center',
                  "minDate": moment().format('YYYY-MM-DD'), // ไม่ให้เลือกวันที่ผ่านมาแล้ว
                  "locale": {
                    "format": "YYYY-MM-DD",
                    "separator": "-",
                    "applyLabel": "ตกลง",
                    "cancelLabel": "ยกเลิก",
                    "fromLabel": "จาก",
                    "toLabel": "ถึง",
                    "customRangeLabel": "Custom",
                    "daysOfWeek": [
                      "อา.", "จ.", "อ.", "พุธ.", "พฤ.", "ศ.", "ส."
                    ],
                    "monthNames": [
                      "ม.ค.", "ก.พ.", "มี.ค.", "เม.ย.", "พ.ค.", "มิ.ย.",
                      "ก.ค.", "ส.ค.", "ก.ย.", "ต.ค.", "พ.ย.", "ธ.ค."
                    ],
                    "firstDay": 1
                  },
                  isInvalidDate: function(date) {
                    const formattedDate = date.format('YYYY-MM-DD');
                    return !availableDates.includes(formattedDate);
                  },
                  isCustomDate: function(date) {
                    const formattedDate = date.format('YYYY-MM-DD');
                    if (holidays[formattedDate]) {
                      return ['holiday', 'available']; // CSS class สำหรับวันหยุด
                    }
                    return availableDates.includes(formattedDate) ? 'available' : '';
                  }
                });

                // เพิ่ม CSS สำหรับวันหยุด
                $('<style>')
                  .text('.holiday { background-color: #ffdddd !important; color: #ff0000 !important; }')
                  .appendTo('head');

                $('#date').prop('disabled', false);

                // เมื่อเลือกวันที่
                $('#date').on('apply.daterangepicker', function(ev, picker) {
                  const selectedDate = picker.startDate.format('YYYY-MM-DD');

                  // แสดงข้อความแจ้งเตือนถ้าเป็นวันหยุดและป้องกันการเลือกช่วงเวลา
                  if (holidays[selectedDate]) {
                    $('#date-holiday-warning').remove(); // ลบข้อความเดิมถ้ามี
                    $(`<div id="date-holiday-warning" class="alert alert-danger mt-2">
                      <i class="fa fa-exclamation-triangle me-1"></i> วันที่คุณเลือกเป็นวันหยุด: ${holidays[selectedDate].day_name}
                      <br><strong>ไม่สามารถนัดหมายในวันหยุดได้ กรุณาเลือกวันอื่น</strong>
                    </div>`).insertAfter('#date');

                    // ล้างและปิดใช้งานช่องเลือกเวลา
                    $('#time_slot_id').empty().append(
                      '<option value="">-- ไม่สามารถนัดหมายในวันหยุด --</option>');
                    $('#time_slot_id').prop('disabled', true);
                    $('#time-message').hide();
                  } else {
                    $('#date-holiday-warning').remove();
                    loadTimeSlots();
                  }
                });
              } else {
                // กรณีไม่มีวันที่ให้เลือก
                $('#date-message').html(
                  `<div class="alert alert-danger mt-2">ไม่พบวันที่ที่มีช่วงเวลาว่าง</div>`).show();
              }
            },
            error: function(xhr, status, error) {
              $('#date-loading').hide();
              console.error('AJAX error:', error, xhr);
              $('#date-message').html(
                  `<div class="alert alert-danger mt-2">เกิดข้อผิดพลาดในการดึงข้อมูลวันที่: ${error}</div>`)
                .show();
            }
          });
        }
      });

      // ฟังก์ชันสำหรับโหลดช่วงเวลา
      function loadTimeSlots() {
        const clinicId = $('#clinic_id').val();
        const doctorId = $('#doctor_id').val();
        const date = $('#date').val();
        const currentTimeSlotId = "{{ $appointment->time_slot_id }}";

        // สร้างคำขอ AJAX ใหม่เพื่อตรวจสอบวันหยุด
        if (clinicId && doctorId && date) {
          // ตรวจสอบก่อนว่าวันที่นี้เป็นวันหยุดหรือไม่
          $.ajax({
            url: "{{ route('get.available.dates') }}",
            type: "GET",
            dataType: "json",
            data: {
              clinic_id: clinicId,
              doctor_id: doctorId
            },
            success: function(response) {
              const holidays = response.holidays || {};
              const selectedDate = date;

              if (holidays[selectedDate]) {
                // ถ้าเป็นวันหยุด ปิดการใช้งาน time slots
                $('#date-holiday-warning').remove();
                $(`<div id="date-holiday-warning" class="alert alert-danger mt-2">
                  <i class="fa fa-exclamation-triangle me-1"></i> วันที่คุณเลือกเป็นวันหยุด: ${holidays[selectedDate].day_name}
                  <br><strong>ไม่สามารถนัดหมายในวันหยุดได้ กรุณาเลือกวันอื่น</strong>
                </div>`).insertAfter('#date');

                // ล้างและปิดใช้งานช่องเลือกเวลา
                $('#time_slot_id').empty().append('<option value="">-- ไม่สามารถนัดหมายในวันหยุด --</option>');
                $('#time_slot_id').prop('disabled', true);
                $('#time-message').hide();
                return; // ออกจากฟังก์ชันทันที - ไม่ต้องโหลด time slots
              }

              // แสดงไอคอนโหลดขณะดึงข้อมูล
              $('#time-loading').show();
              $('#time_slot_id').prop('disabled', true);
              $('#time-message').hide();

              // ใช้ AJAX เพื่อดึงข้อมูลช่วงเวลาที่ว่าง
              $.ajax({
                url: "{{ route('get.timeslots') }}",
                type: "GET",
                dataType: "json",
                data: {
                  clinic_id: clinicId,
                  doctor_id: doctorId,
                  date: date
                },
                success: function(data) {
                  $('#time-loading').hide();
                  console.log('ข้อมูลช่วงเวลา:', data);

                  // ล้างและเพิ่มตัวเลือกเริ่มต้นในช่องเลือกช่วงเวลา
                  $('#time_slot_id').empty().append('<option value="">-- เลือกช่วงเวลา --</option>');

                  let currentTimeSlotExists = false; // ตัวแปรเพื่อตรวจสอบว่าช่วงเวลาเดิมมีอยู่ในรายการที่ดึงมาหรือไม่

                  // เพิ่มช่วงเวลาที่ว่างทั้งหมดในรายการ
                  if (data && data.length > 0) {
                    $.each(data, function(key, timeSlot) {
                      let startTime = timeSlot.start_time.substr(0, 5);
                      let endTime = timeSlot.end_time.substr(0, 5);
                      let availableSlots = timeSlot.max_appointments - timeSlot.booked_appointments;

                      // ตรวจสอบว่าช่วงเวลานี้คือช่วงเวลาเดิมหรือไม่
                      if (timeSlot.id == currentTimeSlotId) {
                        currentTimeSlotExists = true; // พบช่วงเวลาเดิมในรายการ
                      }

                      // เพิ่มตัวเลือกในช่องเลือก
                      $('#time_slot_id').append('<option value="' + timeSlot.id + '">' +
                        startTime + ' - ' + endTime + ' (ว่าง ' + availableSlots + ' คิว)</option>');
                    });

                    // ถ้าวันที่ที่เลือกตรงกับวันที่เดิมของการนัดหมาย
                    if (date === "{{ $appointment->timeSlot->date->format('Y-m-d') }}") {
                      // เปิดใช้งานช่องเลือก
                      $('#time_slot_id').prop('disabled', false);

                      // พยายามเลือกช่วงเวลาเดิมเสมอถ้าเราอยู่ในวันที่เดิม
                      $('#time_slot_id').val(currentTimeSlotId);

                      // ถ้าช่วงเวลาเดิมไม่มีอยู่ในรายการ (อาจเพราะตอนนี้เต็มแล้ว)
                      // เราต้องเพิ่มมันเข้าไปเองเพื่อรักษาการเลือกเดิมไว้
                      if (!currentTimeSlotExists) {
                        // ดึงข้อมูลช่วงเวลาเดิม
                        const originalSlot = {
                          id: "{{ $appointment->time_slot_id }}",
                          start_time: "{{ \Carbon\Carbon::parse($appointment->timeSlot->start_time)->format('H:i') }}",
                          end_time: "{{ \Carbon\Carbon::parse($appointment->timeSlot->end_time)->format('H:i') }}"
                        };

                        // เพิ่มช่วงเวลาเดิมเข้าไปในรายการพร้อมระบุว่าเป็นช่วงเวลาเดิม
                        $('#time_slot_id').append('<option value="' + originalSlot.id +
                          '" data-original="true">' +
                          originalSlot.start_time + ' - ' + originalSlot.end_time +
                          ' (ช่วงเวลาเดิม)</option>');

                        // เลือกช่วงเวลาเดิม
                        $('#time_slot_id').val(originalSlot.id);
                      }
                    } else {
                      // กรณีเลือกวันที่อื่นที่ไม่ใช่วันที่เดิม เพียงแค่เปิดใช้งานช่องเลือก
                      $('#time_slot_id').prop('disabled', false);
                    }
                  } else {
                    // ไม่มีช่วงเวลาว่างสำหรับวันที่ที่เลือก
                    if (date === "{{ $appointment->timeSlot->date->format('Y-m-d') }}") {
                      // ถ้านี่คือวันที่เดิมแต่ไม่มีช่วงเวลาที่ว่าง ยังคงแสดงช่วงเวลาเดิม
                      const originalSlot = {
                        id: "{{ $appointment->time_slot_id }}",
                        start_time: "{{ \Carbon\Carbon::parse($appointment->timeSlot->start_time)->format('H:i') }}",
                        end_time: "{{ \Carbon\Carbon::parse($appointment->timeSlot->end_time)->format('H:i') }}"
                      };

                      $('#time_slot_id').append('<option value="' + originalSlot.id +
                        '" data-original="true">' +
                        originalSlot.start_time + ' - ' + originalSlot.end_time +
                        ' (ช่วงเวลาเดิม)</option>');

                      $('#time_slot_id').val(originalSlot.id);
                      $('#time_slot_id').prop('disabled', false);
                    } else {
                      // วันที่อื่นที่ไม่มีช่วงเวลาว่าง
                      $('#time_slot_id').append('<option disabled>ไม่พบช่วงเวลาที่ว่าง</option>');
                      $('#time_slot_id').prop('disabled', true);
                      $('#time-message').html(
                        `<div class="alert alert-warning mt-2">ไม่พบช่วงเวลาที่ว่างในวันที่ ${date}</div>`
                      ).show();
                    }
                  }
                },
                error: function(xhr, status, error) {
                  // แสดงข้อความเมื่อเกิดข้อผิดพลาดในการดึงข้อมูล
                  $('#time-loading').hide();
                  console.error('AJAX error:', error, xhr);
                  $('#time_slot_id').empty().append(
                    '<option value="">-- เกิดข้อผิดพลาดในการโหลดข้อมูล --</option>');
                  $('#time_slot_id').prop('disabled', true);
                  $('#time-message').html(
                      `<div class="alert alert-danger mt-2">เกิดข้อผิดพลาดในการโหลดข้อมูลช่วงเวลา: ${error}</div>`
                    )
                    .show();
                }
              });
            },
            error: function(xhr, status, error) {
              console.error('Error checking holidays:', error);
              // หากเกิดข้อผิดพลาดในการโหลดข้อมูลวันหยุด ให้โหลด time slots ตามปกติ
              loadRegularTimeSlots();
            }
          });
        } else {
          // ยังไม่มีการเลือกคลินิก, แพทย์ หรือวันที่ครบถ้วน
          $('#time_slot_id').empty().append('<option value="">-- เลือกช่วงเวลา --</option>');
          $('#time_slot_id').prop('disabled', true);
          $('#time-message').hide();
        }
      }

      // ฟังก์ชันโหลด time slots แบบปกติ (ใช้เมื่อไม่สามารถตรวจสอบวันหยุดได้)
      function loadRegularTimeSlots() {
        const clinicId = $('#clinic_id').val();
        const doctorId = $('#doctor_id').val();
        const date = $('#date').val();
        const currentTimeSlotId = "{{ $appointment->time_slot_id }}";

        if (clinicId && doctorId && date) {
          // แสดง loading
          $('#time-loading').show();
          $('#time_slot_id').prop('disabled', true);
          $('#time-message').hide();

          // ใช้ AJAX แบบ jQuery
          $.ajax({
            url: "{{ route('get.timeslots') }}",
            type: "GET",
            dataType: "json",
            data: {
              clinic_id: clinicId,
              doctor_id: doctorId,
              date: date
            },
            success: function(data) {
              $('#time-loading').hide();
              console.log('TimeSlots data:', data);
              $('#time_slot_id').empty().append('<option value="">-- เลือกช่วงเวลา --</option>');

              let currentTimeSlotExists = false;

              if (data && data.length > 0) {
                $.each(data, function(key, timeSlot) {
                  let startTime = timeSlot.start_time.substr(0, 5);
                  let endTime = timeSlot.end_time.substr(0, 5);
                  let availableSlots = timeSlot.max_appointments - timeSlot.booked_appointments;

                  if (timeSlot.id == currentTimeSlotId) {
                    currentTimeSlotExists = true;
                  }

                  $('#time_slot_id').append('<option value="' + timeSlot.id + '">' + startTime + ' - ' +
                    endTime + ' (ว่าง ' + availableSlots + ' คิว)</option>');
                });

                // ถ้าวันที่ที่เลือกตรงกับวันที่เดิมของการนัดหมาย
                if (date === "{{ $appointment->timeSlot->date->format('Y-m-d') }}") {
                  // เปิดใช้งานช่องเลือก
                  $('#time_slot_id').prop('disabled', false);

                  // พยายามเลือกช่วงเวลาเดิมเสมอถ้าเราอยู่ในวันที่เดิม
                  $('#time_slot_id').val(currentTimeSlotId);

                  // ถ้าช่วงเวลาเดิมไม่มีอยู่ในรายการ
                  if (!currentTimeSlotExists) {
                    const originalSlot = {
                      id: "{{ $appointment->time_slot_id }}",
                      start_time: "{{ \Carbon\Carbon::parse($appointment->timeSlot->start_time)->format('H:i') }}",
                      end_time: "{{ \Carbon\Carbon::parse($appointment->timeSlot->end_time)->format('H:i') }}"
                    };

                    $('#time_slot_id').append('<option value="' + originalSlot.id + '" data-original="true">' +
                      originalSlot.start_time + ' - ' + originalSlot.end_time + ' (ช่วงเวลาเดิม)</option>');

                    $('#time_slot_id').val(originalSlot.id);
                  }
                } else {
                  $('#time_slot_id').prop('disabled', false);
                }
              } else {
                // ไม่มีช่วงเวลาว่างสำหรับวันที่ที่เลือก
                if (date === "{{ $appointment->timeSlot->date->format('Y-m-d') }}") {
                  // ถ้านี่คือวันที่เดิม ยังคงแสดงช่วงเวลาเดิม
                  const originalSlot = {
                    id: "{{ $appointment->time_slot_id }}",
                    start_time: "{{ \Carbon\Carbon::parse($appointment->timeSlot->start_time)->format('H:i') }}",
                    end_time: "{{ \Carbon\Carbon::parse($appointment->timeSlot->end_time)->format('H:i') }}"
                  };

                  $('#time_slot_id').append('<option value="' + originalSlot.id + '" data-original="true">' +
                    originalSlot.start_time + ' - ' + originalSlot.end_time + ' (ช่วงเวลาเดิม)</option>');

                  $('#time_slot_id').val(originalSlot.id);
                  $('#time_slot_id').prop('disabled', false);
                } else {
                  $('#time_slot_id').append('<option disabled>ไม่พบช่วงเวลาที่ว่าง</option>');
                  $('#time_slot_id').prop('disabled', true);
                  $('#time-message').html(
                    `<div class="alert alert-warning mt-2">ไม่พบช่วงเวลาที่ว่างในวันที่ ${date}</div>`).show();
                }
              }
            },
            error: function(xhr, status, error) {
              $('#time-loading').hide();
              console.error('AJAX error:', error, xhr);
              $('#time_slot_id').empty().append(
                '<option value="">-- เกิดข้อผิดพลาดในการโหลดข้อมูล --</option>');
              $('#time_slot_id').prop('disabled', true);
              $('#time-message').html(
                  `<div class="alert alert-danger mt-2">เกิดข้อผิดพลาดในการโหลดข้อมูลช่วงเวลา: ${error}</div>`)
                .show();
            }
          });
        } else {
          $('#time_slot_id').empty().append('<option value="">-- เลือกช่วงเวลา --</option>');
          $('#time_slot_id').prop('disabled', true);
          $('#time-message').hide();
        }
      }

      // เพิ่มการตรวจสอบฟอร์มก่อนส่ง
      $('form').on('submit', function(e) {
        // ตรวจสอบว่ามีการเลือกวันหยุดหรือไม่
        const holidayWarning = $('#date-holiday-warning').length > 0;

        if (holidayWarning) {
          e.preventDefault();
          alert('ไม่สามารถนัดหมายในวันหยุดได้ กรุณาเลือกวันอื่น');
          return false;
        }

        // เพิ่ม log เพื่อตรวจสอบข้อมูลก่อนส่ง
        console.log('Form data before submit:', {
          cid: $('#patient_cid').val(),
          hn: $('#patient_hn').val(),
          pname: $('#patient_pname').val(),
          fname: $('#patient_fname').val(),
          lname: $('#patient_lname').val(),
          age: $('#patient_age').val(),
          phone: $('#patient_phone').val()
        });

        // ตรวจสอบว่าได้กรอกเลขบัตรประชาชนหรือไม่
        if ($('#patient_cid').val() === '') {
          alert('กรุณาระบุเลขบัตรประชาชนผู้ป่วย');
          e.preventDefault();
          return false;
        }

        // ตรวจสอบว่าต้องใช้ข้อมูลจาก manual fields หรือไม่
        if ($('#patient_fname').val() === '' || $('#patient_lname').val() === '') {
          // ถ้าฟอร์มกรอกข้อมูลผู้ป่วยด้วยตนเองแสดงอยู่
          if ($('#patient-info-form').is(':visible')) {
            // ตรวจสอบข้อมูลในฟอร์ม
            if ($('#manual_pname').val() === '') {
              alert('กรุณาเลือกคำนำหน้าผู้ป่วย');
              $('#manual_pname').focus();
              e.preventDefault();
              return false;
            }

            if ($('#manual_fname').val() === '') {
              alert('กรุณากรอกชื่อผู้ป่วย');
              $('#manual_fname').focus();
              e.preventDefault();
              return false;
            }

            if ($('#manual_lname').val() === '') {
              alert('กรุณากรอกนามสกุลผู้ป่วย');
              $('#manual_lname').focus();
              e.preventDefault();
              return false;
            }

            if ($('#manual_age').val() === '') {
              alert('กรุณาระบุอายุผู้ป่วย');
              $('#manual_age').focus();
              e.preventDefault();
              return false;
            }

            // โอนข้อมูลจาก manual fields ไปยัง patient fields
            $('#patient_pname').val($('#manual_pname').val());
            $('#patient_fname').val($('#manual_fname').val());
            $('#patient_lname').val($('#manual_lname').val());
            $('#patient_age').val($('#manual_age').val());
            $('#patient_phone').val($('#manual_phone').val());
            
            console.log('Manual data after transfer:', {
              cid: $('#patient_cid').val(),
              hn: $('#patient_hn').val(),
              pname: $('#patient_pname').val(),
              fname: $('#patient_fname').val(),
              lname: $('#patient_lname').val(),
              age: $('#patient_age').val(),
              phone: $('#patient_phone').val()
            });
          } else {
            // ถ้าฟอร์มไม่ได้แสดง แต่ไม่มีข้อมูลผู้ป่วย
            alert('กรุณาค้นหาและเลือกผู้ป่วย หรือกรอกข้อมูลผู้ป่วยด้วยตนเอง');
            e.preventDefault();
            return false;
          }
        }

        return true;
      });
    });
  </script>
@endsection

@section('content')
  <!-- Page Content -->
  <div class="content">
    <div class="block block-rounded">
      <div class="block-header block-header-default">
        <h3 class="block-title">แก้ไขการนัดหมาย</h3>
        <div class="block-options">
          <a href="{{ route('appointments.index') }}" class="btn btn-alt-secondary">
            <i class="fa fa-arrow-left"></i> กลับ
          </a>
        </div>
      </div>
      <div class="block-content">
        @if ($errors->any())
          <div class="alert alert-danger alert-dismissible" role="alert">
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            <ul class="mb-0">
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        <!-- ข้อมูลผู้ป่วย -->
        <div class="block block-rounded mb-4">
          <div class="block-header block-header-default bg-primary">
            <h3 class="block-title text-white">ข้อมูลผู้ป่วย</h3>
          </div>
          <div class="block-content">
            <div id="search-form">
              <div class="row mb-3">
                <div class="col-md-3">
                  <select class="form-select" id="search-type">
                    <option value="cid">เลขบัตรประชาชน</option>
                    <option value="hn">HN</option>
                  </select>
                </div>
                <div class="col-md-9">
                  <div class="input-group">
                    <input type="text" class="form-control" id="search-term" placeholder="ระบุคำค้นหา" value="{{ $appointment->patient_cid }}">
                    <button type="button" class="btn btn-primary" id="search-patient-btn">
                      <i class="fa fa-search me-1"></i> ค้นหา
                    </button>
                  </div>
                </div>
              </div>
              <div class="form-text mb-3">
                <ul class="mb-0">
                  <li>ค้นหาด้วยเลขบัตรประชาชน: กรอกเลข 13 หลัก หรือบางส่วนของเลขบัตร</li>
                  <li>ค้นหาด้วย HN: กรอกหมายเลข HN</li>
                </ul>
              </div>
            </div>

            <!-- ผลการค้นหาผู้ป่วย -->
            <div id="search-result" class="mb-4">
              <!-- ผลการค้นหาจะถูกแสดงที่นี่ด้วย JavaScript -->
            </div>

            <!-- ฟอร์มกรอกข้อมูลผู้ป่วย (กรณีไม่พบข้อมูล) -->
            <div id="patient-info-form" style="display: none;">
              <div class="row mb-4">
                <div class="col-md-3">
                  <div class="mb-4">
                    <label class="form-label fw-bold text-primary" for="manual_pname">คำนำหน้า <span
                        class="text-danger">*</span></label>
                    <select class="form-select" id="manual_pname" name="manual_pname">
                      <option value="">-- เลือกคำนำหน้า --</option>
                      <option value="นาย" {{ old('manual_pname') == 'นาย' ? 'selected' : '' }}>นาย</option>
                      <option value="นาง" {{ old('manual_pname') == 'นาง' ? 'selected' : '' }}>นาง</option>
                      <option value="นางสาว" {{ old('manual_pname') == 'นางสาว' ? 'selected' : '' }}>นางสาว</option>
                      <option value="เด็กชาย" {{ old('manual_pname') == 'เด็กชาย' ? 'selected' : '' }}>เด็กชาย</option>
                      <option value="เด็กหญิง" {{ old('manual_pname') == 'เด็กหญิง' ? 'selected' : '' }}>เด็กหญิง
                      </option>
                    </select>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="mb-4">
                    <label class="form-label fw-bold text-primary" for="manual_fname">ชื่อ <span
                        class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="manual_fname" name="manual_fname"
                      value="{{ old('manual_fname') }}">
                  </div>
                </div>
                <div class="col-md-5">
                  <div class="mb-4">
                    <label class="form-label fw-bold text-primary" for="manual_lname">นามสกุล <span
                        class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="manual_lname" name="manual_lname"
                      value="{{ old('manual_lname') }}">
                  </div>
                </div>
              </div>
              <div class="row mb-4">
                <div class="col-md-6">
                  <div class="mb-4">
                    <label class="form-label fw-bold text-primary" for="manual_age">อายุ (ปี) <span
                        class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="manual_age" name="manual_age" min="0"
                      max="120" value="{{ old('manual_age') }}">
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-4">
                    <label class="form-label fw-bold text-primary" for="manual_phone">เบอร์โทรศัพท์</label>
                    <input type="text" class="form-control" id="manual_phone" name="manual_phone"
                      value="{{ old('manual_phone') }}">
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <form action="{{ route('appointments.update', $appointment) }}" method="POST">
          @csrf
          @method('PUT')

          <!-- Hidden fields สำหรับเก็บข้อมูลผู้ป่วย -->
          <input type="hidden" id="patient_cid" name="patient_cid"
            value="{{ old('patient_cid', $appointment->patient_cid) }}">
          <input type="hidden" id="patient_hn" name="patient_hn"
            value="{{ old('patient_hn', $appointment->patient_hn) }}">
          <input type="hidden" id="patient_pname" name="patient_pname"
            value="{{ old('patient_pname', $appointment->patient_pname) }}">
          <input type="hidden" id="patient_fname" name="patient_fname"
            value="{{ old('patient_fname', $appointment->patient_fname) }}">
          <input type="hidden" id="patient_lname" name="patient_lname"
            value="{{ old('patient_lname', $appointment->patient_lname) }}">
          <input type="hidden" id="patient_birthdate" name="patient_birthdate"
            value="{{ old('patient_birthdate', $appointment->patient_birthdate) }}">
          <input type="hidden" id="patient_age" name="patient_age"
            value="{{ old('patient_age', $appointment->patient_age) }}">
          <input type="hidden" id="patient_phone" name="patient_phone"
            value="{{ old('patient_phone', $appointment->patient_phone) }}">

          <!-- ข้อมูลการนัดหมาย -->
          <div class="block block-rounded mb-4">
            <div class="block-header block-header-default bg-primary">
              <h3 class="block-title text-white">ข้อมูลการนัดหมาย</h3>
            </div>
            <div class="block-content">
              <div class="row mb-4">
                <div class="col-md-4">
                  <div class="mb-4">
                    <label class="form-label fw-bold text-primary" for="group_id">กลุ่มงาน <span
                        class="text-danger">*</span></label>
                    <select class="form-select" id="group_id" name="group_id" required>
                      <option value="">-- เลือกกลุ่มงาน --</option>
                      @foreach ($groups as $group)
                        <option value="{{ $group->id }}"
                          {{ $appointment->clinic->group_id == $group->id ? 'selected' : '' }}>
                          {{ $group->name }}
                        </option>
                      @endforeach
                    </select>
                    <div id="clinic-loading" class="mt-2" style="display: none;">
                      <div class="spinner-border spinner-border-sm text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                      </div>
                      <small class="text-muted ms-1">กำลังโหลดข้อมูลคลินิก...</small>
                    </div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="mb-4">
                    <label class="form-label fw-bold text-primary" for="clinic_id">คลินิก <span
                        class="text-danger">*</span></label>
                    <select class="form-select @error('clinic_id') is-invalid @enderror" id="clinic_id"
                      name="clinic_id" required>
                      <option value="">-- เลือกคลินิก --</option>
                      <!-- คลินิกจะถูกโหลดผ่าน AJAX -->
                    </select>
                    @error('clinic_id')
                      <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                    <div id="doctor-loading" class="mt-2" style="display: none;">
                      <div class="spinner-border spinner-border-sm text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                      </div>
                      <small class="text-muted ms-1">กำลังโหลดข้อมูลแพทย์...</small>
                    </div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="mb-4">
                    <label class="form-label fw-bold text-primary" for="doctor_id">แพทย์ <span
                        class="text-danger">*</span></label>
                    <select class="form-select @error('doctor_id') is-invalid @enderror" id="doctor_id"
                      name="doctor_id" required>
                      <option value="">-- เลือกแพทย์ --</option>
                      <!-- แพทย์จะถูกโหลดผ่าน AJAX -->
                    </select>
                    @error('doctor_id')
                      <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                  </div>
                </div>
              </div>

              <div class="row mb-4">
                <div class="col-md-6">
                  <div class="mb-4">
                    <label class="form-label fw-bold text-primary" for="date">วันที่ <span
                        class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('date') is-invalid @enderror" id="date"
                      name="date" value="{{ old('date', $appointment->timeSlot->date->format('Y-m-d')) }}" disabled>
                    @error('date')
                      <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                    <div id="date-loading" class="mt-2" style="display: none;">
                      <div class="spinner-border spinner-border-sm text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                      </div>
                      <small class="text-muted ms-1">กำลังโหลดข้อมูลวันที่...</small>
                    </div>
                    <div id="date-message" class="mt-2" style="display: none;"></div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="mb-4">
                    <label class="form-label fw-bold text-primary" for="time_slot_id">ช่วงเวลา <span
                        class="text-danger">*</span></label>
                    <select class="form-select @error('time_slot_id') is-invalid @enderror" id="time_slot_id"
                      name="time_slot_id" required disabled>
                      <option value="">-- เลือกช่วงเวลา --</option>
                      <!-- ช่วงเวลาจะถูกโหลดผ่าน AJAX -->
                    </select>
                    @error('time_slot_id')
                      <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                    <div id="time-loading" class="mt-2" style="display: none;">
                      <div class="spinner-border spinner-border-sm text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                      </div>
                      <small class="text-muted ms-1">กำลังโหลดข้อมูลช่วงเวลา...</small>
                    </div>
                    <div id="time-message" class="mt-2" style="display: none;"></div>
                  </div>
                </div>
              </div>

              <div class="mb-4">
                <label class="form-label fw-bold text-primary" for="notes">หมายเหตุ</label>
                <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="4">{{ old('notes', $appointment->notes) }}</textarea>
                @error('notes')
                  <span class="invalid-feedback">{{ $message }}</span>
                @enderror
              </div>
            </div>
          </div>

          <div class="mb-4">
            <button type="submit" class="btn btn-lg btn-primary">
              <i class="fa fa-check me-1"></i> บันทึกการเปลี่ยนแปลง
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
  <!-- END Page Content -->
@endsection