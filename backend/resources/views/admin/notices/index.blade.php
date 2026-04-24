@extends('admin.layouts.app')
@section('title', '공지사항 관리')
@section('page-title', '공지사항 관리')
@section('page-actions')
  <a href="{{ route('admin.notices.create') }}" class="btn btn-dark btn-sm">
    <i class="bi bi-plus-lg me-1"></i>공지사항 등록
  </a>
@endsection

@php
$categoryLabels = ['general'=>'일반','event'=>'이벤트','policy'=>'정책','delivery'=>'배송','system'=>'시스템'];
$categoryColors = ['general'=>'secondary','event'=>'primary','policy'=>'info','delivery'=>'warning','system'=>'danger'];
@endphp

@section('content')
<div class="card border-0 shadow-sm mb-3">
  <div class="card-body py-2">
    <form class="row g-2 align-items-center" method="GET">
      <div class="col-auto">
        <input type="text" name="search" class="form-control form-control-sm"
               placeholder="제목 검색" value="{{ request('search') }}">
      </div>
      <div class="col-auto">
        <select name="category" class="form-select form-select-sm">
          <option value="">전체 카테고리</option>
          @foreach($categoryLabels as $val => $label)
          <option value="{{ $val }}" {{ request('category')==$val ? 'selected':'' }}>{{ $label }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-auto">
        <button class="btn btn-secondary btn-sm" type="submit">검색</button>
        <a href="{{ route('admin.notices.index') }}" class="btn btn-outline-secondary btn-sm">초기화</a>
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
            <th class="ps-3" style="width:50px">핀</th>
            <th>제목</th>
            <th style="width:120px">카테고리</th>
            <th style="width:120px">등록일</th>
            <th class="text-end pe-3" style="width:140px">관리</th>
          </tr>
        </thead>
        <tbody>
          @forelse($notices as $notice)
          <tr>
            <td class="ps-3 text-center">
              @if($notice->is_pinned)
                <i class="bi bi-pin-fill text-danger"></i>
              @endif
            </td>
            <td class="fw-medium">{{ $notice->title }}</td>
            <td>
              <span class="badge bg-{{ $categoryColors[$notice->category] ?? 'secondary' }}">
                {{ $categoryLabels[$notice->category] ?? $notice->category }}
              </span>
            </td>
            <td class="text-muted small">{{ $notice->created_at->format('Y.m.d') }}</td>
            <td class="text-end pe-3">
              @if(Auth::user()->role !== 'demo')
              <a href="{{ route('admin.notices.edit', $notice) }}" class="btn btn-sm btn-outline-primary me-1">수정</a>
              <form action="{{ route('admin.notices.destroy', $notice) }}" method="POST" class="d-inline"
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
          <tr><td colspan="5" class="text-center text-muted py-5">등록된 공지사항이 없습니다.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
  @if($notices->hasPages())
  <div class="card-footer bg-transparent">{{ $notices->links() }}</div>
  @endif
</div>
@endsection
