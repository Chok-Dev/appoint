@extends('layouts.backend')

@section('css')
  @include('timeslots.partials.bulk-slot-picker-css')
@endsection

@section('js')
  @include('timeslots.partials.bulk-slot-picker-js')
  @include('timeslots.partials.bulk-slot-scripts', ['prefixes' => ['create'], 'deletePrefixes' => []])
@endsection

@section('content')
  <div class="content">
    <div class="block block-rounded">
      <div class="block-header block-header-default">
        <div>
          <h3 class="block-title">เพิ่ม slot หลายรายการ</h3>
          <p class="text-muted small mb-0">เพิ่มตามวันในสัปดาห์ (เลือกได้หลายวัน) + ช่วงวันที่ + คลินิก/แพทย์</p>
        </div>
        <div class="block-options">
          <a href="{{ route('timeslots.index') }}" class="btn btn-alt-secondary">
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

        @include('timeslots.partials.bulk-slot-form', ['prefix' => 'create'])
      </div>
    </div>
  </div>
@endsection
