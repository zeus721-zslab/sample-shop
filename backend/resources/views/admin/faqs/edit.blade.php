@extends('admin.layouts.app')
@section('title', 'FAQ 수정')
@section('page-title', 'FAQ 수정')

@section('content')
<form action="{{ route('admin.faqs.update', $faq) }}" method="POST">
  @csrf
  @method('PUT')
  @include('admin.faqs._form')
</form>
@endsection
