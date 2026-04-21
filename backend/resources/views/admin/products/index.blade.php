@extends('admin.layouts.app')
@section('title', '상품 관리')
@section('page-title', '상품 관리')
@section('page-actions')
  <a href="{{ route('admin.products.create') }}" class="btn btn-dark btn-sm">
    <i class="bi bi-plus-lg me-1"></i>상품 등록
  </a>
@endsection

@section('content')
{{-- 검색/필터 --}}
<div class="card border-0 shadow-sm mb-3">
  <div class="card-body py-2">
    <form class="row g-2 align-items-center" method="GET">
      <div class="col-auto">
        <input type="text" name="search" class="form-control form-control-sm"
               placeholder="상품명 검색" value="{{ request('search') }}">
      </div>
      <div class="col-auto">
        <select name="status" class="form-select form-select-sm">
          <option value="">전체 상태</option>
          <option value="active"   {{ request('status')=='active'   ? 'selected':'' }}>판매중</option>
          <option value="inactive" {{ request('status')=='inactive' ? 'selected':'' }}>비활성</option>
          <option value="soldout"  {{ request('status')=='soldout'  ? 'selected':'' }}>품절</option>
        </select>
      </div>
      <div class="col-auto">
        <button class="btn btn-secondary btn-sm" type="submit">검색</button>
        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary btn-sm">초기화</a>
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
            <th class="ps-3" style="width:60px">이미지</th>
            <th>상품명</th>
            <th>카테고리</th>
            <th>가격</th>
            <th>재고</th>
            <th>상태</th>
            <th>등록일</th>
            <th class="text-end pe-3">관리</th>
          </tr>
        </thead>
        <tbody>
          @forelse($products as $product)
          <tr>
            <td class="ps-3">
              @if($product->images && count($product->images))
                <img src="{{ $product->images[0] }}" class="rounded" style="width:44px;height:44px;object-fit:cover">
              @else
                <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width:44px;height:44px">
                  <i class="bi bi-image text-muted"></i>
                </div>
              @endif
            </td>
            <td class="fw-medium">{{ $product->name }}</td>
            <td class="text-muted small">{{ $product->category->name ?? '-' }}</td>
            <td>
              @if($product->sale_price)
                <span class="text-danger fw-semibold">{{ number_format($product->sale_price) }}</span>
                <small class="text-muted text-decoration-line-through ms-1">{{ number_format($product->price) }}</small>
              @else
                {{ number_format($product->price) }}원
              @endif
            </td>
            <td>{{ number_format($product->stock) }}</td>
            <td>
              @php $s=['active'=>['success','판매중'],'inactive'=>['secondary','비활성'],'soldout'=>['warning','품절']] @endphp
              <span class="badge bg-{{ $s[$product->status][0] ?? 'secondary' }}">{{ $s[$product->status][1] ?? $product->status }}</span>
            </td>
            <td class="text-muted small">{{ $product->created_at->format('Y.m.d') }}</td>
            <td class="text-end pe-3">
              @if(Auth::user()->role !== 'demo')
              <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-sm btn-outline-primary me-1">수정</a>
              <form action="{{ route('admin.products.destroy', $product) }}" method="POST" class="d-inline"
                    onsubmit="return confirm('삭제하시겠습니까?')">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-outline-danger">삭제</button>
              </form>
              @else
              <span class="text-muted small">조회 전용</span>
              @endif
            </td>
          </tr>
          @empty
          <tr><td colspan="8" class="text-center text-muted py-5">등록된 상품이 없습니다.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
  @if($products->hasPages())
  <div class="card-footer bg-transparent">{{ $products->links() }}</div>
  @endif
</div>
@endsection
