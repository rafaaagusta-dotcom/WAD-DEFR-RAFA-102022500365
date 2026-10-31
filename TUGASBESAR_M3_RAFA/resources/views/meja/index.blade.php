@extends('layouts.app')
 
@section('title', 'Meja & Area')
@section('heading', 'Master Meja & Area')
@section('deskripsi', 'Daftar meja dan area yang tersedia di restoran. Pilih meja untuk mengubah data atau membuat booking.')
 
@section('ringkasan')
  <div class="ringkasan">
    <div><b>{{ $totalMeja }}</b><span>Meja</span></div>
    <div><b>{{ $totalArea }}</b><span>Area</span></div>
    <div><b>{{ $totalKursi }}</b><span>Total kursi</span></div>
  </div>
@endsection
 
@section('content')
  @if ($totalMeja === 0)
    <div class="kosong">Belum ada data meja. Tambahkan data lewat database untuk mulai.</div>
  @endif
 
  @foreach ($perArea as $namaArea => $daftar)
    <section class="area area-{{ Str::slug($namaArea) }}">
      <div class="area-judul">
        <h2>{{ $namaArea }}</h2>
        <span>{{ $daftar->count() }} meja</span>
      </div>
 
      <div class="daftar">
        @foreach ($daftar as $row)
          <article class="meja">
            {{ $row->denah }}
            <h3>Meja {{ $row->nomor_meja }}</h3>
            <p class="kapasitas">Kapasitas {{ $row->kapasitas }} orang</p>
            <div class="aksi">
              <a href="{{ route('meja.edit', $row->id_meja) }}">Edit</a>
              <a class="utama" href="{{ route('booking.index', ['meja' => $row->id_meja]) }}">Booking</a>
            </div>
          </article>
        @endforeach
      </div>
    </section>
  @endforeach
@endsection