@extends('admin.layouts.app')
@section('title', '공지사항 수정')
@section('page-title', '공지사항 수정')

@section('content')
<form action="{{ route('admin.notices.update', $notice) }}" method="POST">
  @csrf
  @method('PUT')
  @include('admin.notices._form')
</form>
@endsection
