<?php
 
namespace App\Http\Controllers;
 
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\Rule;
 
class RestoranController extends Controller
{
 
    public function index()
    {
        $meja = DB::table('meja as m')
            ->join('area as a', 'm.id_area', '=', 'a.id_area')
            ->select('m.id_meja', 'm.nomor_meja', 'a.nama_area', 'm.kapasitas')
            ->orderBy('a.nama_area')
            ->orderBy('m.nomor_meja')
            ->get();

        $meja->each(function ($row) {
            $row->denah = $this->gambarMeja($row->kapasitas);
        });
 
        $perArea = $meja->groupBy('nama_area');
 
        return view('meja.index', [
            'perArea'    => $perArea,
            'totalMeja'  => $meja->count(),
            'totalArea'  => $perArea->count(),
            'totalKursi' => (int) $meja->sum('kapasitas'),
        ]);
    }
    public function tambah()
{
    $daftarArea = DB::table('area')->orderBy('id_area')->pluck('nama_area', 'id_area');

    return view('meja.tambah', compact('daftarArea'));
}
public function simpan(Request $request)
{
    $data = $request->validate([
        'nomor_meja' => ['required', 'integer', 'min:1', Rule::unique('meja', 'nomor_meja')],
        'id_area'    => ['required', Rule::exists('area', 'id_area')],
        'kapasitas'  => ['required', 'integer', 'min:1'],
    ], [
        'required'          => 'Semua kolom wajib diisi dengan benar.',
        'integer'           => 'Semua kolom wajib diisi dengan benar.',
        'min'               => 'Semua kolom wajib diisi dengan benar.',
        'exists'            => 'Area tidak valid.',
        'nomor_meja.unique' => 'Nomor meja sudah digunakan.',
    ]);

    DB::table('meja')->insert([
        'nomor_meja' => $data['nomor_meja'],
        'id_area'    => $data['id_area'],
        'kapasitas'  => $data['kapasitas'],
    ]);

    return redirect()->route('meja.index')->with('sukses', 'Meja baru berhasil ditambahkan.');
}
 
    public function edit($id)
    {
        $meja = DB::table('meja')->where('id_meja', $id)->first();
 
        if (!$meja) {
            return redirect()->route('meja.index')->with('error', 'Data meja tidak ditemukan.');
        }
 
        $daftarArea = DB::table('area')->orderBy('id_area')->pluck('nama_area', 'id_area');
 
        return view('meja.edit', compact('meja', 'daftarArea'));
    }
 
    public function update(Request $request, $id)
    {
        if (!DB::table('meja')->where('id_meja', $id)->exists()) {
            return redirect()->route('meja.index')->with('error', 'Data meja tidak ditemukan.');
        }
 
        $data = $request->validate([
            'nomor_meja' => [
                'required', 'integer', 'min:1',
                Rule::unique('meja', 'nomor_meja')->ignore($id, 'id_meja'),
            ],
            'id_area'    => ['required', Rule::exists('area', 'id_area')],
            'kapasitas'  => ['required', 'integer', 'min:1'],
        ], [
            'required'          => 'Semua kolom wajib diisi dengan benar.',
            'integer'           => 'Semua kolom wajib diisi dengan benar.',
            'min'               => 'Semua kolom wajib diisi dengan benar.',
            'exists'            => 'Area tidak valid.',
            'nomor_meja.unique' => 'Nomor meja sudah digunakan.',
        ]);
 
        DB::table('meja')->where('id_meja', $id)->update([
            'nomor_meja' => $data['nomor_meja'],
            'id_area'    => $data['id_area'],
            'kapasitas'  => $data['kapasitas'],
        ]);
 
        return redirect()->route('meja.index')->with('sukses', 'Data meja berhasil diperbarui.');
    }
 
    public function booking(Request $request)
    {
        $daftarMeja = DB::table('meja as m')
            ->join('area as a', 'm.id_area', '=', 'a.id_area')
            ->select('m.id_meja', 'm.nomor_meja', 'm.kapasitas', 'a.nama_area')
            ->orderBy('m.nomor_meja')
            ->get();
 
        $reservasi = DB::table('reservasi as r')
            ->join('meja as m', 'r.id_meja', '=', 'm.id_meja')
            ->join('area as a', 'm.id_area', '=', 'a.id_area')
            ->select('r.*', 'm.nomor_meja', 'a.nama_area')
            ->orderByDesc('r.tanggal')
            ->orderByDesc('r.jam_mulai')
            ->get();
 
        return view('booking.index', [
            'daftarMeja'  => $daftarMeja,
            'reservasi'   => $reservasi,
            'mejaDipilih' => (int) $request->query('meja', 0),
        ]);
    }
 
    public function simpanBooking(Request $request)
    {
        $data = $request->validate([
            'nama'    => ['required', 'string', 'max:100'],
            'telepon' => ['required', 'regex:/^[0-9+\-\s()]{8,20}$/'],
            'id_meja' => ['required', 'integer', 'exists:meja,id_meja'],
            'tanggal' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'mulai'   => ['required', 'date_format:H:i'],
            'selesai' => ['required', 'date_format:H:i', 'after:mulai'],
            'jumlah'  => ['required', 'integer', 'min:1'],
        ], [
            'nama.required'          => 'Nama dan nomor telepon wajib diisi.',
            'telepon.required'       => 'Nama dan nomor telepon wajib diisi.',
            'telepon.regex'          => 'Format nomor telepon tidak valid.',
            'id_meja.required'       => 'Silakan pilih meja yang valid.',
            'id_meja.exists'         => 'Silakan pilih meja yang valid.',
            'tanggal.required'       => 'Tanggal dan jam wajib diisi dengan benar.',
            'tanggal.date_format'    => 'Tanggal dan jam wajib diisi dengan benar.',
            'tanggal.after_or_equal' => 'Tanggal booking tidak boleh di masa lalu.',
            'mulai.required'         => 'Tanggal dan jam wajib diisi dengan benar.',
            'mulai.date_format'      => 'Tanggal dan jam wajib diisi dengan benar.',
            'selesai.required'       => 'Tanggal dan jam wajib diisi dengan benar.',
            'selesai.date_format'    => 'Tanggal dan jam wajib diisi dengan benar.',
            'selesai.after'          => 'Jam selesai harus lebih besar dari jam mulai.',
            'jumlah.required'        => 'Jumlah orang minimal 1.',
            'jumlah.min'             => 'Jumlah orang minimal 1.',
        ]);
 
        if ($data['tanggal'] === Carbon::now()->format('Y-m-d') && $data['mulai'] < Carbon::now()->format('H:i')) {
            return back()->withInput()->withErrors(['mulai' => 'Jam mulai sudah lewat.']);
        }

        $meja = DB::table('meja')->where('id_meja', $data['id_meja'])->first();
        if ($data['jumlah'] > (int) $meja->kapasitas) {
            return back()->withInput()->withErrors([
                'jumlah' => 'Jumlah orang melebihi kapasitas meja (' . $meja->kapasitas . ' orang).',
            ]);
        }
 
        $mulai   = $data['mulai'] . ':00';
        $selesai = $data['selesai'] . ':00';

        $bentrok = DB::table('reservasi')
            ->where('id_meja', $data['id_meja'])
            ->where('tanggal', $data['tanggal'])
            ->where('status', 'aktif')
            ->where('jam_mulai', '<', $selesai)
            ->where('jam_selesai', '>', $mulai)
            ->exists();
 
        if ($bentrok) {
            return back()->withInput()->withErrors([
                'id_meja' => 'Meja sudah dibooking pada jam tersebut. Pilih jam atau meja lain.',
            ]);
        }
 
        DB::table('reservasi')->insert([
            'id_meja'        => $data['id_meja'],
            'nama_pelanggan' => trim($data['nama']),
            'no_telepon'     => trim($data['telepon']),
            'tanggal'        => $data['tanggal'],
            'jam_mulai'      => $mulai,
            'jam_selesai'    => $selesai,
            'jumlah_orang'   => $data['jumlah'],
        ]);
 
        return redirect()->route('booking.index')->with('sukses', 'Booking berhasil disimpan.');
    }
    public function hapus($id)
{
    if (!DB::table('meja')->where('id_meja', $id)->exists()) {
        return redirect()->route('meja.index')->with('error', 'Data meja tidak ditemukan.');
    }

    DB::transaction(function () use ($id) {
        DB::table('reservasi')->where('id_meja', $id)->delete();
        DB::table('meja')->where('id_meja', $id)->delete();
    });

    return redirect()->route('meja.index')->with('sukses', 'Data meja dan seluruh bookingnya berhasil dihapus.');
}
    public function batalBooking(Request $request)
    {
        $diubah = DB::table('reservasi')
            ->where('id_reservasi', (int) $request->input('id_reservasi'))
            ->where('status', 'aktif')
            ->update(['status' => 'batal']);
 
        if ($diubah > 0) {
            return redirect()->route('booking.index')->with('sukses', 'Booking berhasil dibatalkan.');
        }
 
        return redirect()->route('booking.index')->with('error', 'Booking tidak ditemukan atau sudah dibatalkan.');
    }


    private function gambarMeja($kapasitas): HtmlString
    {
        $n = max(1, min((int) $kapasitas, 12));
 
        $svg = '<svg class="denah" viewBox="0 0 120 120" aria-hidden="true">';
        $svg .= '<circle class="meja-bulat" cx="60" cy="60" r="26"/>';
 
        for ($i = 0; $i < $n; $i++) {
            $sudut = 2 * M_PI * $i / $n - M_PI / 2;
            $x = round(60 + 42 * cos($sudut), 1);
            $y = round(60 + 42 * sin($sudut), 1);
            $svg .= '<circle class="kursi" cx="' . $x . '" cy="' . $y . '" r="7"/>';
        }
 
        return new HtmlString($svg . '</svg>');
    }
}