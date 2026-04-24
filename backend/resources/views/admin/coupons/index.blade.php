@extends('admin.layouts.app')
@section('title', '쿠폰 관리')
@section('page-title', '쿠폰 관리')

@section('page-actions')
  <a href="{{ route('admin.coupons.create') }}" class="btn btn-primary btn-sm">
    <i class="bi bi-plus-lg me-1"></i>쿠폰 발행
  </a>
@endsection

@section('content')
<div class="card">
  <div class="card-body p-0">
    <table class="table table-hover mb-0 align-middle">
      <thead class="table-light">
        <tr>
          <th>코드</th>
          <th>이름</th>
          <th>할인</th>
          <th>최소주문</th>
          <th>사용현황</th>
          <th>유효기간</th>
          <th>상태</th>
          <th class="text-end">관리</th>
        </tr>
      </thead>
      <tbody>
        @forelse($coupons as $coupon)
        <tr>
          <td><code class="fw-bold">{{ $coupon->code }}</code></td>
          <td>{{ $coupon->name }}</td>
          <td>
            @if($coupon->type === 'fixed')
              {{ number_format($coupon->value) }}원 할인
            @else
              {{ $coupon->value }}% 할인
              @if($coupon->max_discount_amount)
                <small class="text-muted">(최대 {{ number_format($coupon->max_discount_amount) }}원)</small>
              @endif
            @endif
          </td>
          <td>{{ $coupon->min_order_amount ? number_format($coupon->min_order_amount).'원' : '-' }}</td>
          <td>
            {{ $coupon->used_count }}
            @if($coupon->max_uses)
              / {{ $coupon->max_uses }}
            @else
              / ∞
            @endif
          </td>
          <td>
            @if($coupon->expires_at)
              {{ $coupon->expires_at->format('Y-m-d H:i') }}
              @if($coupon->expires_at->isPast())
                <span class="badge bg-secondary ms-1">만료</span>
              @endif
            @else
              <span class="text-muted">-</span>
            @endif
          </td>
          <td>
            @if($coupon->is_active && $coupon->isValid())
              <span class="badge bg-success">활성</span>
            @elseif(!$coupon->is_active)
              <span class="badge bg-secondary">비활성</span>
            @else
              <span class="badge bg-danger">소진/만료</span>
            @endif
          </td>
          <td class="text-end">
            @if(Auth::user()->role !== 'demo')
            <a href="{{ route('admin.coupons.edit', $coupon) }}" class="btn btn-sm btn-outline-secondary">
              <i class="bi bi-pencil"></i>
            </a>
            <form action="{{ route('admin.coupons.destroy', $coupon) }}" method="POST" class="d-inline"
                  onsubmit="return confirm('삭제하시겠습니까?')">
              @csrf @method('DELETE')
              <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
            </form>
            @else
              <span class="text-muted small">조회 전용</span>
            @endif
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="8" class="text-center py-5 text-muted">등록된 쿠폰이 없습니다.</td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  @if($coupons->hasPages())
  <div class="card-footer">{{ $coupons->links() }}</div>
  @endif
</div>
@endsection
