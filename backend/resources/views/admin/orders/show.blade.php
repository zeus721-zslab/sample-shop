@extends('admin.layouts.app')
@section('title', '주문 상세')
@section('page-title', '주문 상세 — ' . $order->order_number)
@section('page-actions')
  <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline-secondary">
    <i class="bi bi-arrow-left me-1"></i>목록
  </a>
@endsection

@section('content')
<div class="row g-3">
  {{-- 주문 정보 --}}
  <div class="col-12 col-lg-7">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-transparent fw-semibold">주문 정보</div>
      <div class="card-body">
        <dl class="row mb-0">
          <dt class="col-4 text-muted">주문번호</dt><dd class="col-8">{{ $order->order_number }}</dd>
          <dt class="col-4 text-muted">주문자</dt><dd class="col-8">{{ $order->user->name ?? '-' }} ({{ $order->user->email ?? '-' }})</dd>
          <dt class="col-4 text-muted">결제금액</dt><dd class="col-8 fw-semibold">{{ number_format($order->paid_amount) }}원</dd>
          <dt class="col-4 text-muted">결제방법</dt><dd class="col-8">{{ $order->payment_method ?? '-' }}</dd>
          <dt class="col-4 text-muted">결제일시</dt><dd class="col-8">{{ $order->paid_at?->format('Y.m.d H:i') ?? '-' }}</dd>
          <dt class="col-4 text-muted">주문일시</dt><dd class="col-8">{{ $order->created_at->format('Y.m.d H:i') }}</dd>
        </dl>
      </div>
    </div>

    {{-- 상품 목록 --}}
    <div class="card border-0 shadow-sm mt-3">
      <div class="card-header bg-transparent fw-semibold">주문 상품</div>
      <div class="card-body p-0">
        <table class="table align-middle mb-0">
          <thead class="table-light">
            <tr><th class="ps-3">상품</th><th>수량</th><th>단가</th><th>소계</th></tr>
          </thead>
          <tbody>
            @foreach($order->items as $item)
            <tr>
              <td class="ps-3">
                <div class="d-flex align-items-center gap-2">
                  @if($item->product_image)
                    <img src="{{ $item->product_image }}" class="rounded" style="width:36px;height:36px;object-fit:cover">
                  @endif
                  <span class="fw-medium">{{ $item->product_name }}</span>
                </div>
              </td>
              <td>{{ $item->quantity }}</td>
              <td>{{ number_format($item->price) }}원</td>
              <td class="fw-semibold">{{ number_format($item->subtotal) }}원</td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>

  {{-- 배송지 + 상태 변경 --}}
  <div class="col-12 col-lg-5">
    <div class="card border-0 shadow-sm mb-3">
      <div class="card-header bg-transparent fw-semibold">배송 정보</div>
      <div class="card-body">
        @php $addr = is_array($order->shipping_address) ? $order->shipping_address : json_decode($order->shipping_address, true); @endphp
        <dl class="row mb-0">
          <dt class="col-4 text-muted">수취인</dt><dd class="col-8">{{ $addr['name'] ?? '-' }}</dd>
          <dt class="col-4 text-muted">연락처</dt><dd class="col-8">{{ $addr['phone'] ?? '-' }}</dd>
          <dt class="col-4 text-muted">우편번호</dt><dd class="col-8">{{ $addr['zip'] ?? '-' }}</dd>
          <dt class="col-4 text-muted">주소</dt><dd class="col-8">{{ ($addr['address1'] ?? '') . ' ' . ($addr['address2'] ?? '') }}</dd>
        </dl>
      </div>
    </div>

    <div class="card border-0 shadow-sm">
      <div class="card-header bg-transparent fw-semibold">상태 변경</div>
      <div class="card-body">
        @php
          $badges = ['pending'=>'secondary','paid'=>'success','shipping'=>'info','delivered'=>'primary','cancelled'=>'danger'];
          $labels = ['pending'=>'대기','paid'=>'결제완료','shipping'=>'배송중','delivered'=>'배송완료','cancelled'=>'취소'];
        @endphp
        <p>현재 상태:
          <span class="badge bg-{{ $badges[$order->status] ?? 'secondary' }} fs-6">
            {{ $labels[$order->status] ?? $order->status }}
          </span>
        </p>
        <form action="{{ route('admin.orders.status', $order) }}" method="POST">
          @csrf @method('PATCH')
          <div class="input-group">
            <select name="status" class="form-select">
              @foreach($labels as $v => $l)
                <option value="{{ $v }}" {{ $order->status == $v ? 'selected':'' }}>{{ $l }}</option>
              @endforeach
            </select>
            <button class="btn btn-dark" type="submit">변경</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
