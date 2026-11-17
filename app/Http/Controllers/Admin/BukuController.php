<?php
 
namespace App\Http\Controllers\Admin;
 
use App\Http\Controllers\Controller;
use App\Models\Buku;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
 
class BukuController extends Controller
{
    public function index(Request $request)
    {
        $buku = Buku::with('kategori')
            ->when($request->q, fn ($query, $cari) => $query->where('judul', 'like', "%{$cari}%"))
            ->orderBy('judul')
            ->paginate(10)
            ->withQueryString();
    
        return view('admin.data-buku', compact('buku'));
    }

 
    public function create()
    {
        $kategori = Kategori::orderBy('nama_kategori')->get();
        $buku = new Buku();
    
        return view('admin.buku.create', compact('kategori', 'buku'));
    }

 
public function store(Request $request)
{
    $data = $this->validasi($request);
 
    if ($request->hasFile('cover')) {
        $data['cover'] = $request->file('cover')->store('cover', 'public');
    }
 
    Buku::create($data);
 
    return redirect()->route('admin.buku.index')
        ->with('success', 'Buku berhasil ditambahkan.');
}
 
public function show(Buku $buku)
    {
        return view('admin.buku.show', compact('buku'));
    }

    public function edit(Buku $buku)
    {
        $kategori = Kategori::orderBy('nama_kategori')->get();

        return view('admin.buku.edit', compact('buku', 'kategori'));
    }

public function update(Request $request, Buku $buku)
{
    $data = $this->validasi($request);
 
    if ($request->hasFile('cover')) {
        $this->hapusCover($buku);   // buang file lama
        $data['cover'] = $request->file('cover')->store('cover', 'public');
    }
 
    $buku->update($data);
 
    return redirect()->route('admin.buku.index')
        ->with('success', 'Data buku berhasil diperbarui.');
}
 
public function destroy(Buku $buku)
{
    $this->hapusCover($buku);
    $buku->delete();
 
    return redirect()->route('admin.buku.index')
        ->with('success', 'Buku "' . $buku->judul . '" berhasil dihapus.');
}
 
private function validasi(Request $request): array
{
    $data = $request->validate([
        'kategori_id'  => 'required|exists:kategori,id',
        'judul'        => 'required|string|max:150',
        'penulis'      => 'required|string|max:100',
        'penerbit'     => 'required|string|max:100',
        'tahun_terbit' => 'required|integer|min:1901|max:' . date('Y'),
        'stok'         => 'required|integer|min:0',
        'cover'        => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
    ], [
        'required' => ':attribute wajib diisi.',
        'string'   => ':attribute harus berupa teks.',
        'integer'  => ':attribute harus berupa angka bulat.',
        'max'      => ':attribute melebihi batas yang diizinkan.',
        'min'      => ':attribute kurang dari batas yang diizinkan.',
        'exists'   => 'Kategori yang dipilih tidak valid.',
        'image'    => ':attribute harus berupa gambar.',
        'mimes'    => ':attribute harus berformat JPG atau PNG.',
    ], [
        'kategori_id'  => 'Kategori',
        'judul'        => 'Judul',
        'penulis'      => 'Penulis',
        'penerbit'     => 'Penerbit',
        'tahun_terbit' => 'Tahun terbit',
        'stok'         => 'Stok',
        'cover'        => 'Cover',
    ]);
 
    // File cover diproses terpisah (lihat store dan update)
    unset($data['cover']);
 
    return $data;
}
 
private function hapusCover(Buku $buku): void
{
    // Hanya hapus file hasil upload, jangan hapus aset template (course/...)
    if ($buku->cover && ! str_starts_with($buku->cover, 'course/')) {
        Storage::disk('public')->delete($buku->cover);
    }
}


}
