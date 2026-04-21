@extends('admin.layouts.app')
@section('title', '상품 등록')
@section('page-title', '상품 등록')

@section('content')
<div class="card border-0 shadow-sm" style="max-width:760px">
  <div class="card-body">
    <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
      @csrf
      @include('admin.products._form')
      <div class="d-flex gap-2 mt-4">
        <button type="submit" class="btn btn-dark px-4">등록</button>
        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary px-4">취소</a>
      </div>
    </form>
  </div>
</div>
@endsection
