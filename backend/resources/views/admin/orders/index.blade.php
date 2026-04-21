@extends('admin.layouts.app')
@section('title', '주문 관리')
@section('page-title', '주문 관리')

@section('content')
<div class="card border-0 shadow-sm mb-3">
  <div class="card-body py-2">
    <form class="row g-2 align-items-center" method="GET">
      <div class="col-auto">
        <input type="text" name="search" class="form-control form-control-sm"
               placeholder="주문번호 / 회원명" value="{{ request('search') }}">
      </div>
      <div class="col-auto">
        <select name="status" class="form-select form-select-sm">
          <option value="">전체 상태</option>
          @foreach(['pending'=>'대기','paid'=>'결제완료','shipping'=>'배송중','delivered'=>'배송완료','cancelled'=>'취소'] as $v=>$l)
            <option value="{{ $v }}" {{ request('status')==$v?'selected':'' }}>{{ $l }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-auto">
        <button class="btn btn-secondary btn-sm" type="submit">검색</button>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary btn-sm">초기화</a>
      </div>
    </form>
  </div>
</div>

<div class="card border-0 shadow-sm">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th class="ps-3">주문번호</th>
            <th>회원</th>
            <th>금액</th>
            <th>결제방법</th>
            <th>상태</th>
            <th>주문일시</th>
            <th class="text-end pe-3">관리</th>
          </tr>
        </thead>
        <tbody>
          @php
            $badges = ['pending'=>'secondary','paid'=>'success','shipping'=>'info','delivered'=>'primary','cancelled'=>'danger'];
            $labels = ['pending'=>'대기','paid'=>'결제완료','shipping'=>'배송중','delivered'=>'배송완료','cancelled'=>'취소'];
          @endphp
          @forelse($orders as $order)
          <tr>
            <td class="ps-3 fw-medium">
              <a href="{{ route('admin.orders.show', $order) }}" class="text-decoration-none">{{ $order->order_number }}</a>
            </td>
            <td>{{ $order->user->name ?? '-' }}</td>
            <td>{{ number_format($order->paid_amount) }}원</td>
            <td class="text-muted small">{{ $order->payment_method ?? '-' }}</td>
            <td>
              <span class="badge bg-{{ $badges[$order->status] ?? 'secondary' }}">{{ $labels[$order->status] ?? $order->status }}</span>
            </td>
            <td class="text-muted small">{{ $order->created_at->format('Y.m.d H:i') }}</td>
            <td class="text-end pe-3">
              <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-outline-primary">상세</a>
            </td>
          </tr>
          @empty
          <tr><td colspan="7" class="text-center text-muted py-5">주문이 없습니다.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
  @if($orders->hasPages())
  <div class="card-footer bg-transparent">{{ $orders->links() }}</div>
  @endif
</div>
@endsection
