@extends('layouts.master')

@section('pageTitle', $pageTitle)

@section('main')
  <div class="form-container">
    <h1 class="form-title">{{ $pageTitle }}</h1>
    @if (session('message-error'))
      <div class="alert-danger">
        {{ session('message-error') }}
      </div>
    @endif

    <form class="form" method="POST" action="{{ route('roles.destroy', ['id' => $role->id]) }}">
      @method('DELETE')
      @csrf

      <p>You are going to delete <strong>"{{ $role->name }}"</strong></p>

      <p>Are you sure?</p>

      <button type="submit" class="form-button">Delete</button>
    </form>
  </div>
@endsection