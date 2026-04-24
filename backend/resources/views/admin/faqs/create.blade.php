@extends('admin.layouts.app')
@section('title', 'FAQ 등록')
@section('page-title', 'FAQ 등록')

@section('content')
<form action="{{ route('admin.faqs.store') }}" method="POST">
  @csrf
  @include('admin.faqs._form')
</form>
@endsection
