@extends('admin.layouts.app')
@section('title', 'FAQ 관리')
@section('page-title', 'FAQ 관리')
@section('page-actions')
  <a href="{{ route('admin.faqs.create') }}" class="btn btn-dark btn-sm">
    <i class="bi bi-plus-lg me-1"></i>FAQ 등록
  </a>
@endsection

@section('content')
<div class="card border-0 shadow-sm mb-3">
  <div class="card-body py-2">
    <form class="row g-2 align-items-center" method="GET">
      <div class="col-auto">
        <input type="text" name="search" class="form-control form-control-sm"
               placeholder="질문 검색" value="{{ request('search') }}">
      </div>
      <div class="col-auto">
        <select name="category" class="form-select form-select-sm">
          <option value="">전체 카테고리</option>
          @foreach(['주문/결제','배송','반품/교환','회원/계정'] as $cat)
          <option value="{{ $cat }}" {{ request('category')==$cat ? 'selected':'' }}>{{ $cat }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-auto">
        <button class="btn btn-secondary btn-sm" type="submit">검색</button>
        <a href="{{ route('admin.faqs.index') }}" class="btn btn-outline-secondary btn-sm">초기화</a>
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
            <th class="ps-3" style="width:120px">카테고리</th>
            <th style="width:60px">순서</th>
            <th>질문</th>
            <th>답변 미리보기</th>
            <th class="text-end pe-3" style="width:140px">관리</th>
          </tr>
        </thead>
        <tbody>
          @forelse($faqs as $faq)
          <tr>
            <td class="ps-3">
              <span class="badge bg-dark">{{ $faq->category }}</span>
            </td>
            <td class="text-center text-muted small">{{ $faq->sort_order }}</td>
            <td class="fw-medium">{{ $faq->question }}</td>
            <td class="text-muted small text-truncate" style="max-width:300px">{{ Str::limit($faq->answer, 60) }}</td>
            <td class="text-end pe-3">
              @if(Auth::user()->role !== 'demo')
              <a href="{{ route('admin.faqs.edit', $faq) }}" class="btn btn-sm btn-outline-primary me-1">수정</a>
              <form action="{{ route('admin.faqs.destroy', $faq) }}" method="POST" class="d-inline"
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
          <tr><td colspan="5" class="text-center text-muted py-5">등록된 FAQ가 없습니다.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
  @if($faqs->hasPages())
  <div class="card-footer bg-transparent">{{ $faqs->links() }}</div>
  @endif
</div>
@endsection
