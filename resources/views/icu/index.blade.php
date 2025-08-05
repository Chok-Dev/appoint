@extends('layouts.backend')

@section('css')
<style>
.bed-card {
    transition: all 0.3s ease;
    height: 200px;
}

.bed-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.bed-available {
    border-left: 5px solid #28a745;
    background: linear-gradient(135deg, #d4edda 0%, #f8f9fa 100%);
}

.bed-occupied {
    border-left: 5px solid #dc3545;
    background: linear-gradient(135deg, #f8d7da 0%, #f8f9fa 100%);
}

.bed-number {
    font-size: 2rem;
    font-weight: bold;
}

.bed-status-badge {
    font-size: 0.9rem;
    padding: 0.5rem 1rem;
}

.patient-info {
    font-size: 0.9rem;
}

.refresh-btn {
    position: fixed;
    bottom: 30px;
    right: 30px;
    z-index: 1000;
    border-radius: 50%;
    width: 60px;
    height: 60px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.3);
}

.last-updated {
    font-size: 0.8rem;
    color: #6c757d;
}

.room-selector {
    background: #fff;
    padding: 1.5rem;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
}

.stats-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 15px;
    padding: 1.5rem;
    margin-bottom: 2rem;
}

.error-message {
    display: none;
    background: #f8d7da;
    color: #721c24;
    padding: 1rem;
    border-radius: 5px;
    margin-bottom: 1rem;
}

@media (max-width: 768px) {
    .bed-card {
        height: auto;
        min-height: 180px;
    }
    
    .bed-number {
        font-size: 1.5rem;
    }
    
    .refresh-btn {
        bottom: 20px;
        right: 20px;
        width: 50px;
        height: 50px;
    }
}
</style>
@endsection

@section('js')
<script>
$(document).ready(function() {
    let autoRefreshInterval;
    
    // หยุด header loader เมื่อหน้าโหลดเสร็จ
    Codebase.layout('header_loader_off');
    
    // Auto refresh every 30 seconds
    function startAutoRefresh() {
        autoRefreshInterval = setInterval(function() {
            // ใช้ header loader แบบเงียบๆ สำหรับ auto refresh
            Codebase.layout('header_loader_on');
            refreshDataSilent();
        }, 30000);
    }
    
    // Refresh data แบบเงียบ (ไม่แสดง error message)
    function refreshDataSilent() {
        const selectedRoom = $('#room-selector').val();
        
        $.ajax({
            url: '{{ route("icu.api-status") }}',
            method: 'GET',
            data: { room: selectedRoom },
            success: function(response) {
                if (response.success) {
                    updateBedStatus(response.data);
                    updateLastUpdated(response.last_updated);
                }
            },
            error: function() {
                // Auto refresh ไม่แสดง error
                console.log('Auto refresh failed');
            },
            complete: function() {
                Codebase.layout('header_loader_off');
            }
        });
    }
    
    // Stop auto refresh
    function stopAutoRefresh() {
        if (autoRefreshInterval) {
            clearInterval(autoRefreshInterval);
        }
    }
    
    // Refresh data
    function refreshData() {
        const selectedRoom = $('#room-selector').val();
        
        // เริ่ม header loader
        Codebase.layout('header_loader_on');
        $('.error-message').hide();
        
        $.ajax({
            url: '{{ route("icu.api-status") }}',
            method: 'GET',
            data: { room: selectedRoom },
            success: function(response) {
                if (response.success) {
                    updateBedStatus(response.data);
                    updateLastUpdated(response.last_updated);
                } else {
                    showError('เกิดข้อผิดพลาดในการดึงข้อมูล');
                }
            },
            error: function() {
                showError('ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้');
            },
            complete: function() {
                // หยุด header loader
                Codebase.layout('header_loader_off');
            }
        });
    }
    
    // Update bed status display
    function updateBedStatus(beds) {
        const container = $('#bed-container');
        container.empty();
        
        let availableCount = 0;
        let occupiedCount = 0;
        
        beds.forEach(function(bed) {
            const isOccupied = bed.bed_status === 'ไม่ว่าง';
            
            if (isOccupied) {
                occupiedCount++;
            } else {
                availableCount++;
            }
            
            const cardClass = isOccupied ? 'bed-occupied' : 'bed-available';
            const statusBadge = isOccupied ? 
                '<span class="badge bg-danger bed-status-badge">ไม่ว่าง</span>' : 
                '<span class="badge bg-success bed-status-badge">ว่าง</span>';
            
            let patientInfo = '';
            if (isOccupied && bed.fname) {
                patientInfo = `
                    <div class="patient-info mt-2">
                        <small class="text-muted">ผู้ป่วย:</small><br>
                        <strong>${bed.pname || ''}${bed.fname || ''} ${bed.lname || ''}</strong><br>
                        <small>HN: ${bed.hn || '-'}</small><br>
                        <small>วันที่เข้า: ${formatDate(bed.regdate)} ${bed.regtime || ''}</small>
                    </div>
                `;
            }
            
            const bedCard = `
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                    <div class="card bed-card ${cardClass} h-100">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="bed-number text-primary">${bed.bedno}</div>
                                ${statusBadge}
                            </div>
                            <div class="flex-grow-1">
                                ${patientInfo}
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            container.append(bedCard);
        });
        
        // Update statistics
        $('#available-count').text(availableCount);
        $('#occupied-count').text(occupiedCount);
        $('#total-count').text(availableCount + occupiedCount);
    }
    
    // Format date
    function formatDate(dateString) {
        if (!dateString) return '-';
        const date = new Date(dateString);
        return date.toLocaleDateString('th-TH');
    }
    
    // Update last updated time
    function updateLastUpdated(timestamp) {
        $('#last-updated').text(timestamp);
    }
    
    // Show error message
    function showError(message) {
        $('.error-message').text(message).show();
    }
    
    // Room selector change
    $('#room-selector').change(function() {
        const selectedRoom = $(this).val();
        
        // เริ่ม header loader ขณะเปลี่ยนหน้า
        Codebase.layout('header_loader_on');
        
        window.location.href = '{{ route("icu.index") }}?room=' + selectedRoom;
    });
    
    // Manual refresh button
    $('#refresh-btn').click(function() {
        refreshData();
    });
    
    // Start auto refresh on page load
    startAutoRefresh();
    
    // Stop auto refresh when page is hidden
    $(document).on('visibilitychange', function() {
        if (document.hidden) {
            stopAutoRefresh();
        } else {
            startAutoRefresh();
        }
    });
    
    // Stop auto refresh when leaving page
    $(window).on('beforeunload', function() {
        stopAutoRefresh();
    });
});
</script>
@endsection

@section('content')
<div class="content">
    <!-- Room Selector and Statistics -->
    <div class="room-selector">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h3 class="mb-3">
                    <i class="fa fa-bed text-primary me-2"></i>
                    สถานะเตียง ICU
                </h3>
                <div class="mb-3">
                    <label class="form-label fw-bold">เลือกหอผู้ป่วย:</label>
                    <select class="form-select" id="room-selector" style="width: auto; display: inline-block;">
                        @foreach($icuRooms as $room)
                            <option value="{{ $room->roomno }}" {{ $selectedRoom == $room->roomno ? 'selected' : '' }}>
                                {{ $room->roomno }} - {{ $room->name ?? $room->roomno }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="stats-card text-center">
                    <div class="row">
                        <div class="col-4">
                            <div class="fs-2 fw-bold" id="available-count">
                                {{ collect($bedStatus)->where('bed_status', 'ว่าง')->count() }}
                            </div>
                            <div>เตียงว่าง</div>
                        </div>
                        <div class="col-4">
                            <div class="fs-2 fw-bold" id="occupied-count">
                                {{ collect($bedStatus)->where('bed_status', 'ไม่ว่าง')->count() }}
                            </div>
                            <div>เตียงไม่ว่าง</div>
                        </div>
                        <div class="col-4">
                            <div class="fs-2 fw-bold" id="total-count">
                                {{ count($bedStatus) }}
                            </div>
                            <div>เตียงทั้งหมด</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Error Message -->
    @if(isset($error))
        <div class="alert alert-danger">
            <i class="fa fa-exclamation-triangle me-2"></i>
            {{ $error }}
        </div>
    @endif
    
    <div class="error-message"></div>

    <!-- Bed Status Grid -->
    <div class="row" id="bed-container">
        @foreach($bedStatus as $bed)
            @php
                $isOccupied = $bed->bed_status === 'ไม่ว่าง';
                $cardClass = $isOccupied ? 'bed-occupied' : 'bed-available';
            @endphp
            
            <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                <div class="card bed-card {{ $cardClass }} h-100">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="bed-number text-primary">{{ $bed->bedno }}</div>
                            @if($isOccupied)
                                <span class="badge bg-danger bed-status-badge">ไม่ว่าง</span>
                            @else
                                <span class="badge bg-success bed-status-badge">ว่าง</span>
                            @endif
                        </div>
                        
                        <div class="flex-grow-1">
                            @if($isOccupied && $bed->fname)
                                <div class="patient-info mt-2">
                                    <small class="text-muted">ผู้ป่วย:</small><br>
                                    <strong>{{ $bed->pname }}{{ $bed->fname }} {{ $bed->lname }}</strong><br>
                                    <small>HN: {{ $bed->hn ?: '-' }}</small><br>
                                    <small>วันที่เข้า: {{ $bed->regdate ? \Carbon\Carbon::parse($bed->regdate)->thaidate() : '-' }} {{ $bed->regtime }}</small>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Last Updated Info -->
    <div class="text-center mt-4">
        <div class="last-updated">
            <i class="fa fa-clock me-1"></i>
            อัพเดทล่าสุด: <span id="last-updated">{{ now()->format('d/m/Y H:i:s') }}</span>
        </div>
        <small class="text-muted d-block mt-1">
            ระบบจะอัพเดทข้อมูลอัตโนมัติทุก 30 วินาที
        </small>
    </div>

    <!-- Floating Refresh Button -->
    <button class="btn btn-primary refresh-btn" id="refresh-btn" title="รีเฟรชข้อมูล">
        <i class="fa fa-sync-alt"></i>
    </button>
</div>
@endsection