<?php
 
namespace App\Models;
 
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
 
class Buku extends Model
{
    use HasFactory;
 
    protected $table = 'buku';
 
    protected $fillable = [
        'kategori_id',
        'judul',
        'penulis',
        'penerbit',
        'tahun_terbit',
        'stok',
        'cover',
    ];
 
    public function kategori()
    {
        return $this->belongsTo(Kategori::class);
    }
 
    // Alamat gambar sampul: dipanggil sebagai $buku->cover_url
    public function getCoverUrlAttribute(): string
    {
        if (! $this->cover) {
            return asset('assetsUser/img/course/1.png');   // gambar bawaan
        }
 
        return str_starts_with($this->cover, 'course/')
            ? asset('assetsUser/img/' . $this->cover)       // aset template
            : asset('storage/' . $this->cover);             // hasil upload
    }
}
