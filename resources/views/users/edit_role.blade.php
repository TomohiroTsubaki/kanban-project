@extends('layouts.master')

@section('pageTitle', $pageTitle)

@section('main')
  <div class="form-container">
    <h1 class="form-title">{{ $pageTitle }}</h1>
    <form
      class="form"
      method="POST"
      action="{{ route('users.updateRole', ['id' => $user->id]) }}"
    >
      @csrf
      @method('PUT')

      <div class="form-item">
        <label>User:</label>
        <input class="form-input" type="text" value="{{$user->name }}" disabled>
      </div>


      <div class="form-item">
        <label>Role:</label>

        @error('roles')
          <div class="alert-danger">{{ $message }}</div>
        @enderror

        <select name="role_id" class="form-input">
          <option value="">No Role</option>
          @foreach ($roles as $role)
            <option
              value="{{ $role->id }}"
              @if($user->role && $role->id == $user->role->id)
                selected
              @endif
            >
              {{ $role->name }}
            </option>
          @endforeach
        </select>
      </div>

      <button type="submit" class="form-button">Submit</button>
    </form>
  </div>
@endsection