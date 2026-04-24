@extends('admin.layouts.app')
@section('title', '멤버십 설정')
@section('page-title', '멤버십 등급 설정')

@section('content')
@if(session('success'))
  <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
@endif

<div class="row mb-3">
  <div class="col-12">
    <small class="text-muted">
      <i class="bi bi-info-circle me-1"></i>
      등급별 기준 금액(최근 12개월 확정 구매액) 및 적립률을 관리합니다.
      등급 재산정은 주문 확정(배송완료) 시 자동으로 실행됩니다.
    </small>
  </div>
</div>

<div class="row g-3">
  @php
    $gradeColors = ['newbie' => 'secondary', 'silver' => 'info', 'gold' => 'warning', 'vip' => 'danger'];
    $gradeIcons  = ['newbie' => 'bi-person', 'silver' => 'bi-star', 'gold' => 'bi-star-fill', 'vip' => 'bi-gem'];
  @endphp

  @foreach($configs as $config)
  <div class="col-md-6 col-xl-3">
    <div class="card h-100">
      <div class="card-header bg-{{ $gradeColors[$config->grade] ?? 'secondary' }} text-white d-flex align-items-center gap-2">
        <i class="bi {{ $gradeIcons[$config->grade] ?? 'bi-person' }} fs-5"></i>
        <span class="fw-bold text-uppercase">{{ $config->grade }}</span>
      </div>
      <div class="card-body">
        <p class="text-muted small mb-3">{{ $config->description }}</p>
        @if(Auth::user()->role !== 'demo')
        <form method="POST" action="{{ route('admin.membership.update', $config) }}">
          @csrf @method('PUT')
          <div class="mb-3">
            <label class="form-label fw-semibold small">기준 금액 (원)</label>
            <input type="number" name="min_amount" value="{{ $config->min_amount }}"
                   min="0" class="form-control form-control-sm" required
                   {{ $config->grade === 'newbie' ? 'readonly' : '' }}>
            @if($config->grade === 'newbie')
              <div class="form-text">Newbie는 기본 등급 (0원 고정)</div>
            @endif
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold small">적립률 (%)</label>
            <div class="input-group input-group-sm">
              <input type="number" name="point_rate" value="{{ $config->point_rate }}"
                     min="0" max="100" step="0.1" class="form-control" required>
              <span class="input-group-text">%</span>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold small">설명</label>
            <input type="text" name="description" value="{{ $config->description }}"
                   maxlength="200" class="form-control form-control-sm">
          </div>
          <button type="submit" class="btn btn-dark btn-sm w-100">
            <i class="bi bi-save me-1"></i>저장
          </button>
        </form>
        @else
        <div class="mb-3">
          <label class="form-label small">기준 금액</label>
          <div class="form-control form-control-sm bg-light">{{ number_format($config->min_amount) }}원</div>
        </div>
        <div class="mb-3">
          <label class="form-label small">적립률</label>
          <div class="form-control form-control-sm bg-light">{{ $config->point_rate }}%</div>
        </div>
        <div class="alert alert-warning py-2 small mb-0">
          <i class="bi bi-lock me-1"></i>데모 계정은 수정 불가
        </div>
        @endif
      </div>
    </div>
  </div>
  @endforeach
</div>

{{-- 등급 흐름 다이어그램 --}}
<div class="card mt-4">
  <div class="card-header"><i class="bi bi-diagram-3 me-2"></i>등급 상향 흐름</div>
  <div class="card-body">
    <div class="d-flex align-items-center gap-3 flex-wrap">
      @foreach($configs->sortBy('min_amount') as $config)
      <div class="text-center">
        <span class="badge bg-{{ $gradeColors[$config->grade] ?? 'secondary' }} fs-6 px-3 py-2 text-uppercase">
          {{ $config->grade }}
        </span>
        <div class="text-muted small mt-1">
          @if($config->min_amount > 0) {{ number_format($config->min_amount) }}원 이상
          @else 기본 @endif
        </div>
        <div class="text-muted small">
          {{ $config->point_rate > 0 ? $config->point_rate.'% 적립' : '적립 없음' }}
        </div>
      </div>
      @if(!$loop->last)
      <i class="bi bi-arrow-right fs-4 text-muted"></i>
      @endif
      @endforeach
    </div>
  </div>
</div>
@endsection
