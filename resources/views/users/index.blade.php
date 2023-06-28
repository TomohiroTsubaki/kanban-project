@extends('layouts.master')

@section('pageTitle', $pageTitle)

@section('main')
  <div class="task-list-container">
    <h1 class="task-list-heading">{{ $pageTitle }}</h1>

    <div>
      <div class="task-list-table-head">
        <div class="task-list-header-task-name">User</div>
        <div class="task-list-header-detail">Roles</div>
      </div>

      @foreach ($users as $user)
        <div class="table-body">
          <div class="table-body-user-name">{{ $user->name }}</div>
          <div class="table-body-user-role">
              {{ $user->role ? $user->role->name : 'No Role'}}
          </div>
          <div class="table-body-link">
            <a href="{{ route('users.editRole', ['id' => $user->id]) }}">Edit Role</a>
          </div>
        </div>
      @endforeach
    </div>
  </div>
@endsection