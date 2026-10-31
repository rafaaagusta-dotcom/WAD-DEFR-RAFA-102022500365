@extends('layouts.app')
 
@section('title', 'Edit Meja & Area')
@section('heading', 'Edit Meja & Area')
@section('deskripsi', 'Ubah data meja yang dipilih, lalu simpan.')
 
@section('content')
  <form method="post" action="{{ route('meja.update', $meja->id_meja) }}" class="kartu-form sempit">
    @csrf
    <div class="grid-form">
      <label>Nomor Meja
        <input type="number" name="nomor_meja" min="1" value="{{ old('nomor_meja', $meja->nomor_meja) }}" required>
      </label>
      <label>Area
        <select name="id_area" required>
          @foreach ($daftarArea as $idArea => $nama)
            <option value="{{ $idArea }}" {{ (int) old('id_area', $meja->id_area) === (int) $idArea ? 'selected' : '' }}>
              {{ $nama }}
            </option>
          @endforeach
        </select>
      </label>
      <label>Kapasitas (Orang)
        <input type="number" name="kapasitas" min="1" value="{{ old('kapasitas', $meja->kapasitas) }}" required>
      </label>
    </div>
 
    <div class="tombol-baris">
      <button type="submit" class="btn">Simpan Perubahan</button>
      <a href="{{ route('meja.index') }}" class="btn garis">Batal</a>
      <button type="submit" form="form-hapus" class="btn bahaya">Hapus Meja</button>
    </div>
  </form>

  <form id="form-hapus" method="post" action="{{ route('meja.hapus', $meja->id_meja) }}"
        onsubmit="return confirm('Hapus meja ini? Data yang dihapus tidak bisa dikembalikan.')">
    @csrf
    @method('DELETE')
  </form>
@endsection