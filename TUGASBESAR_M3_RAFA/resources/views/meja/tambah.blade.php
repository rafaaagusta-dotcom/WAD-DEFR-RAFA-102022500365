@extends('layouts.app')

@section('title', 'Tambah Meja')
@section('heading', 'Tambah Meja')
@section('deskripsi', 'Isi data meja baru, lalu simpan.')

@section('content')
  <form method="post" action="{{ route('meja.simpan') }}" class="kartu-form sempit">
    @csrf
    <div class="grid-form">
      <label>Nomor Meja
        <input type="number" name="nomor_meja" min="1" value="{{ old('nomor_meja') }}" required>
      </label>
      <label>Area
        <select name="id_area" required>
          <option value="">-- Pilih area --</option>
          @foreach ($daftarArea as $idArea => $nama)
            <option value="{{ $idArea }}" {{ (int) old('id_area') === (int) $idArea ? 'selected' : '' }}>
              {{ $nama }}
            </option>
          @endforeach
        </select>
      </label>
      <label>Kapasitas (Orang)
        <input type="number" name="kapasitas" min="1" value="{{ old('kapasitas', 2) }}" required>
      </label>
    </div>

    <div class="tombol-baris">
      <button type="submit" class="btn">Simpan Meja</button>
      <a href="{{ route('meja.index') }}" class="btn garis">Batal</a>
    </div>
  </form>
@endsection