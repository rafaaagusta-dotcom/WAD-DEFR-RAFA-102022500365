<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,400;12..96,600;12..96,800&display=swap" rel="stylesheet">
  <title>@yield('title') | Restoran &amp; Cafe</title>
  <link rel="stylesheet" href="{{ asset('style.css') }}">
</head>
<body class="{{ file_exists(public_path('img/fotogue.jpg')) ? 'foto' : '' }}">
  <header class="resto">
    <div class="resto-isi">
      <nav>
        <strong>Restoran &amp; Cafe</strong>
        <a href="#">Dashboard</a>
        <a href="#">Buku Menu</a>
        <a href="{{ route('meja.index') }}" class="{{ request()->is('/') ? 'aktif' : '' }}">Meja &amp; Area</a>
        <a href="{{ route('booking.index') }}" class="{{ request()->is('booking*') ? 'aktif' : '' }}">Booking</a>
        <a href="#">Staff</a>
        <a href="#">Supplier</a>
      </nav>
      <h1>@yield('heading')</h1>
      <p>@yield('deskripsi')</p>
      @yield('ringkasan')
    </div>
  </header>
 
  <main>
    @if (session('error'))
      <p class="pesan error">{{ session('error') }}</p>
    @endif
    @if (session('sukses'))
      <p class="pesan">{{ session('sukses') }}</p>
    @endif
    @foreach ($errors->all() as $pesanError)
      <p class="pesan error">{{ $pesanError }}</p>
    @endforeach
 
    @yield('content')
  </main>
</body>
</html>