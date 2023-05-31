<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
  <link rel="stylesheet" href="{{ asset('style.css') }}">
  <title>@yield('pageTitle')</title>
</head>

<body>
  <div class="container">
    <div class="sidebar">
      <div class="sidebar-container">
        <a class="sidebar-link" href="{{ route('home') }}"> <!-- Diperbarui -->
          <span class="material-icons sidebar-icon">home</span>
          <p class="sidebar-text">Home</p>
        </a>
        <a class="sidebar-link" href="{{ route('tasks.index') }}"> <!-- Diperbarui -->
          <span class="material-icons sidebar-icon">list</span>
          <p class="sidebar-text">Task List</p>
        </a>
      </div>
    </div>
    <div class="main">
      @yield('main')
    </div>
</div>
</body>

</html>
