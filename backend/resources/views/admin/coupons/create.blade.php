@extends('admin.layouts.app')
@section('title', '쿠폰 발행')
@section('page-title', '쿠폰 발행')

@section('page-actions')
  <a href="{{ route('admin.coupons.index') }}" class="btn btn-outline-secondary btn-sm">
    <i class="bi bi-arrow-left me-1"></i>목록으로
  </a>
@endsection

@section('content')
<div class="card">
  <div class="card-body">
    <form action="{{ route('admin.coupons.store') }}" method="POST">
      @csrf
      @include('admin.coupons._form')
      <div class="mt-4 d-flex gap-2">
        <button type="submit" class="btn btn-primary">
          <i class="bi bi-check-lg me-1"></i>발행하기
        </button>
        <a href="{{ route('admin.coupons.index') }}" class="btn btn-outline-secondary">취소</a>
      </div>
    </form>
  </div>
</div>
@endsection
