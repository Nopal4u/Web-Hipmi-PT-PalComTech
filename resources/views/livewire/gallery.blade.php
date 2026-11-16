<section class="pg on" id="p-galeri">
  <div class="w">
    <x-section-heading
      tag="Activity"
      title="Galeri kegiatan"
      description="Dokumentasi bootcamp, pelantikan, kunjungan industri, dan bazar. Klik foto untuk memperbesar." />

    <div class="tabs" id="gt">
      <button class="tab {{ $filter === 'Semua' ? 'on' : '' }}" wire:click="$set('filter','Semua')">Semua</button>
      @foreach (\App\Models\GalleryAlbum::CATEGORIES as $category)
        <button class="tab {{ $filter === $category ? 'on' : '' }}" wire:click="$set('filter','{{ $category }}')">{{ $category }}</button>
      @endforeach
    </div>

    <div class="ms" id="gl">
      @forelse ($albums as $album)
        <article class="gc">
          <x-slider
            :photos="$album->photos->map(fn ($photo) => \App\Support\Media::url($photo->path))->all()"
            ratio="4/3"
            :color="$album->color"
            :name="$album->title"
            :alt="$album->title"
            :badge="true"
            lightbox-type="gallery"
            :lightbox-id="$album->id" />
          <div class="gc-t">
            <h3>{{ $album->title }}</h3>
            <small class="mono">{{ $album->date_label }} · {{ $album->category }}</small>
          </div>
        </article>
      @empty
        <p class="empty">Belum ada dokumentasi.</p>
      @endforelse
    </div>
  </div>
</section>

<script type="application/json" data-hipmi-data="albums">@json($payload)</script>
