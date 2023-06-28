@extends('layouts.master')

@section('pageTitle', $pageTitle)

@section('main')
  <div class="form-container">
    <h1 class="form-title">{{ $pageTitle }}</h1>
    <form
      class="form"
      method="POST"
      action="{{ route('roles.store') }}"
    >
      @csrf
      <div class="form-item">
        <label>Role Name:</label>
        @error('name')
          <div class="alert-danger">{{ $message }}</div>
        @enderror

        <input
          class="form-input"
          type="text"
          value="{{ old('name') }}"
          name="name"
        >
      </div>


      <div class="form-item">
        <label>Permissions:</label>

        @error('permissions')
          <div class="alert-danger">{{ $message }}</div>
        @enderror

        @foreach ($permissions as $permission)
          <div>
            <input
              type="checkbox"
              name="permissionIds[]"
              value="{{ $permission->id }}"
            >
            <label for="{{ $permission->name }}">
              {{ $permission->description }}
            </label>
          </div>
        @endforeach
      </div>

      <button type="submit" class="form-button">Submit</button>
    </form>
  </div>
@endsection