@extends('admin.layouts.app')
@section('title', '대시보드')
@section('page-title', '대시보드')

@section('content')
{{-- 통계 카드 --}}
<div class="row g-3 mb-4">
  <div class="col-6 col-xl-3">
    <div class="card bg-primary text-white border-0 shadow-sm h-100">
      <div class="card-body d-flex justify-content-between align-items-center">
        <div>
          <div class="text-white-75 small mb-1">오늘 주문</div>
          <div class="fs-3 fw-bold">{{ number_format($todayOrders) }}</div>
        </div>
        <i class="bi bi-receipt fs-1 opacity-25"></i>
      </div>
    </div>
  </div>
  <div class="col-6 col-xl-3">
    <div class="card bg-success text-white border-0 shadow-sm h-100">
      <div class="card-body d-flex justify-content-between align-items-center">
        <div>
          <div class="text-white-75 small mb-1">오늘 매출</div>
          <div class="fs-3 fw-bold">{{ number_format($todayRevenue) }}원</div>
        </div>
        <i class="bi bi-currency-dollar fs-1 opacity-25"></i>
      </div>
    </div>
  </div>
  <div class="col-6 col-xl-3">
    <div class="card bg-info text-white border-0 shadow-sm h-100">
      <div class="card-body d-flex justify-content-between align-items-center">
        <div>
          <div class="text-white-75 small mb-1">전체 회원</div>
          <div class="fs-3 fw-bold">{{ number_format($totalMembers) }}</div>
        </div>
        <i class="bi bi-people fs-1 opacity-25"></i>
      </div>
    </div>
  </div>
  <div class="col-6 col-xl-3">
    <div class="card bg-warning text-white border-0 shadow-sm h-100">
      <div class="card-body d-flex justify-content-between align-items-center">
        <div>
          <div class="text-white-75 small mb-1">전체 상품</div>
          <div class="fs-3 fw-bold">{{ number_format($totalProducts) }}</div>
        </div>
        <i class="bi bi-box-seam fs-1 opacity-25"></i>
      </div>
    </div>
  </div>
</div>

<div class="row g-3">
  {{-- 최근 주문 --}}
  <div class="col-12 col-xl-7">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
        <span class="fw-semibold">최근 주문</span>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline-secondary">전체 보기</a>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
              <tr>
                <th class="ps-3">주문번호</th>
                <th>회원</th>
                <th>금액</th>
                <th>상태</th>
                <th>일시</th>
              </tr>
            </thead>
            <tbody>
              @forelse($recentOrders as $order)
              <tr>
                <td class="ps-3">
                  <a href="{{ route('admin.orders.show', $order) }}" class="text-decoration-none fw-medium">
                    {{ $order->order_number }}
                  </a>
                </td>
                <td>{{ $order->user->name ?? '-' }}</td>
                <td>{{ number_format($order->paid_amount) }}원</td>
                <td>
                  @php
                    $badges = ['pending'=>'secondary','paid'=>'success','shipping'=>'info','delivered'=>'primary','cancelled'=>'danger'];
                    $labels = ['pending'=>'대기','paid'=>'결제완료','shipping'=>'배송중','delivered'=>'배송완료','cancelled'=>'취소'];
                  @endphp
                  <span class="badge bg-{{ $badges[$order->status] ?? 'secondary' }}">
                    {{ $labels[$order->status] ?? $order->status }}
                  </span>
                </td>
                <td class="text-muted small">{{ $order->created_at->format('m/d H:i') }}</td>
              </tr>
              @empty
              <tr><td colspan="5" class="text-center text-muted py-4">주문 없음</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  {{-- 최근 가입 --}}
  <div class="col-12 col-xl-5">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
        <span class="fw-semibold">최근 가입 회원</span>
        <a href="{{ route('admin.members.index') }}" class="btn btn-sm btn-outline-secondary">전체 보기</a>
      </div>
      <div class="card-body p-0">
        <ul class="list-group list-group-flush">
          @forelse($recentMembers as $member)
          <li class="list-group-item d-flex justify-content-between align-items-center px-3">
            <div>
              <div class="fw-medium">{{ $member->name }}</div>
              <small class="text-muted">{{ $member->email }}</small>
            </div>
            <small class="text-muted">{{ $member->created_at->diffForHumans() }}</small>
          </li>
          @empty
          <li class="list-group-item text-center text-muted py-4">회원 없음</li>
          @endforelse
        </ul>
      </div>
    </div>
  </div>
</div>
@endsection
