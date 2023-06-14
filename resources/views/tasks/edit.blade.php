@extends('layouts.master')

@section('pageTitle', $pageTitle)

@section('main')
  <div class="form-container">
    <h1 class="form-title">{{ $pageTitle }}</h1>
    <form class="form" method="POST" action="{{ route('tasks.update', ['id' => $task->id]) }}">
      @method('PUT')
      @csrf
      <div class="form-item">
        <label>Name:</label>
        <input class="form-input" type="text" value="{{ old('name', $task->name) }}" name="name">
        @error('name')
          <div class="alert-danger">{{ $message }}</div>
        @enderror
      </div>

      <div class="form-item">
        <label>Detail:</label>
        <textarea class="form-text-area" name="detail">{{ old('detail', $task->detail) }}</textarea>
      </div>

      <div class="form-item">
        <label>Due Date:</label>
        <input
          class="form-input"
          type="date"
          value="{{ old('due_date', $task->due_date) }}"
          name="due_date"
        >
        @error('due_date')
          <div class="alert-danger">{{ $message }}</div>
        @enderror
      </div>

      <div class="form-item">
        <label>Progress:</label>
        <select class="form-input" name="status">
          <option value="">Choose one</option>
          <option @if (old('status', $task->status) == 'not_started') selected @endif value="not_started">Not Started</option>
          <option @if (old('status', $task->status) == 'in_progress') selected @endif value="in_progress">In Progress</option>
          <option @if (old('status', $task->status) == 'in_review') selected @endif value="in_review">Waiting/In Review</option>
          <option @if (old('status', $task->status) == 'completed') selected @endif value="completed">Completed</option>
        </select>
        @error('status')
          <div class="alert-danger">{{ $message }}</div>
        @enderror
      </div>
      <button type="submit" class="form-button">Submit</button>
    </form>
  </div>
@endsection