@extends('admin.layouts.app')
@section('title', '공지사항 등록')
@section('page-title', '공지사항 등록')

@section('content')
<form action="{{ route('admin.notices.store') }}" method="POST">
  @csrf
  @include('admin.notices._form')
</form>
@endsection
