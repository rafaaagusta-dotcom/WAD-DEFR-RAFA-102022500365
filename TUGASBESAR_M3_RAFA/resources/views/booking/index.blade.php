@extends('layouts.app')
 
@section('title', 'Booking Meja')
@section('heading', 'Booking Meja')
@section('deskripsi', 'Isi data berikut untuk memesan meja atau tempat.')
 
@section('content')
<div class="tombol-baris" style="margin-bottom:1.5rem">
  <a href="{{ route('meja.tambah') }}" class="btn">+ Tambah Meja</a>
</div>
  <form method="post" action="{{ route('booking.store') }}" class="kartu-form">
    @csrf
    <div class="grid-form">
      <label>Nama Pelanggan
        <input type="text" name="nama" value="{{ old('nama') }}" required>
      </label>
      <label>No. Telepon
        <input type="tel" name="telepon" value="{{ old('telepon') }}" required>
      </label>
      <label>Meja
        <select name="id_meja" required>
          <option value="">-- Pilih meja --</option>
          @foreach ($daftarMeja as $m)
            <option value="{{ $m->id_meja }}" {{ (int) old('id_meja', $mejaDipilih) === (int) $m->id_meja ? 'selected' : '' }}>
              Meja {{ $m->nomor_meja }} - {{ $m->nama_area }} ({{ $m->kapasitas }} orang)
            </option>
          @endforeach
        </select>
      </label>
      <label>Tanggal
        <input type="date" name="tanggal" min="{{ now()->format('Y-m-d') }}"
               value="{{ old('tanggal', now()->format('Y-m-d')) }}" required>
      </label>
      <label>Jam Mulai
        <input type="time" name="mulai" value="{{ old('mulai') }}" required>
      </label>
      <label>Jam Selesai
        <input type="time" name="selesai" value="{{ old('selesai') }}" required>
      </label>
      <label>Jumlah Orang
        <input type="number" name="jumlah" min="1" value="{{ old('jumlah', 1) }}" required>
      </label>
    </div>
 
    <div class="tombol-baris">
      <button type="submit" class="btn">Booking Sekarang</button>
      <a href="{{ route('meja.index') }}" class="btn garis">Kembali</a>
    </div>
  </form>
 
  <h2 class="judul">Daftar Booking</h2>
  <div class="tabel-bungkus">
    <table>
      <thead>
        <tr>
          <th>Nama</th>
          <th>Telepon</th>
          <th>Meja</th>
          <th>Tanggal</th>
          <th>Jam</th>
          <th>Orang</th>
          <th>Status</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($reservasi as $row)
          <tr class="{{ $row->status === 'batal' ? 'batal' : '' }}">
            <td>{{ $row->nama_pelanggan }}</td>
            <td>{{ $row->no_telepon }}</td>
            <td>{{ $row->nomor_meja }} ({{ $row->nama_area }})</td>
            <td>{{ \Carbon\Carbon::parse($row->tanggal)->format('d-m-Y') }}</td>
            <td>{{ substr($row->jam_mulai, 0, 5) }} - {{ substr($row->jam_selesai, 0, 5) }}</td>
            <td>{{ $row->jumlah_orang }}</td>
            <td><span class="badge {{ $row->status }}">{{ ucfirst($row->status) }}</span></td>
            <td>
              @if ($row->status === 'aktif')
                <form method="post" action="{{ route('booking.batal') }}" style="margin:0"
                      onsubmit="return confirm('Batalkan booking ini?')">
                  @csrf
                  <input type="hidden" name="id_reservasi" value="{{ $row->id_reservasi }}">
                  <button type="submit" class="btn kecil bahaya">Batalkan</button>
                </form>
              @else
                -
              @endif
            </td>
          </tr>
        @empty
          <tr><td colspan="8">Belum ada booking.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
@endsection