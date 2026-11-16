@php
  use App\Models\Business;
  use App\Models\GalleryAlbum;
  use App\Models\GalleryPhoto;
  use App\Models\Program;

  $totalBusiness  = Business::count();
  $activeBusiness = Business::where('is_active', true)->count();
  $totalAlbums    = GalleryAlbum::count();
  $totalPhotos    = GalleryPhoto::count();
  $averagePhotos  = $totalAlbums > 0
      ? number_format($totalPhotos / $totalAlbums, 1, ',', '.')
      : '0';
  $runningPrograms = Program::where('status', 'berjalan')->count();
  $totalPrograms   = Program::count();
@endphp

<div class="adm-met">
  <div class="mc-card">
    <div class="l">
      <span>UMKM terdaftar</span>
      <span class="adm-pill ok">Live</span>
    </div>
    <div class="n">{{ $totalBusiness }}</div>
    <div class="s">{{ $activeBusiness }} aktif tampil di web</div>
  </div>

  <div class="mc-card">
    <div class="l"><span>Album galeri</span></div>
    <div class="n">{{ $totalAlbums }}</div>
    <div class="s">{{ $totalPhotos > 0 ? 'rata-rata '.$averagePhotos.' foto per album' : 'belum ada foto diunggah' }}</div>
  </div>

  <div class="mc-card">
    <div class="l"><span>Proker berjalan</span></div>
    <div class="n">{{ $runningPrograms }}</div>
    <div class="s">dari {{ $totalPrograms }} program kerja</div>
  </div>
</div>
