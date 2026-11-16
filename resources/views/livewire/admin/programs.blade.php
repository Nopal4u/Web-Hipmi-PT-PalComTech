<div class="adm-page">
<x-admin-page-header
  title="Program Kerja"
  desc="Atur program kerja per divisi beserta status pelaksanaannya."
  :link="route('programs')"
/>

<x-admin-metrics />

<div class="adm-tools">
  <div class="adm-search">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
    <input type="search" wire:model.live.debounce.300ms="search" placeholder="Cari program kerja…" aria-label="Cari program kerja">
  </div>
  <button class="btn p" type="button" wire:click="openPanel">+ Tambah Data Baru</button>
</div>

<div class="adm-tcard">
  <div class="adm-wrap">
    <table class="adm-tb adm-dt">
      <thead>
        <tr>
          <th>Foto</th>
          <th>Program kerja</th>
          <th>Divisi &amp; waktu</th>
          <th>Status</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($programs as $program)
          <tr>
            <td class="c-th">
              <div class="adm-thumb" style="--c:{{ $program->division?->color ?? '#2563EB' }}"></div>
            </td>
            <td class="c-nm"><b>{{ $program->title }}</b></td>
            <td class="c-ds">{{ $program->division?->name ?? 'Tanpa divisi' }}{{ $program->schedule ? ' · '.$program->schedule : '' }}</td>
            <td class="c-st">
              <span class="tag mono" style="--c:{{ $program->statusColor() }}">{{ $program->statusLabel() }}</span>
            </td>
            <td class="c-ac">
              <div class="adm-btns">
                <button class="xs ok" type="button" wire:click="cycleStatus({{ $program->id }})">Jadikan {{ $program->nextStatusLabel() }}</button>
                <button class="xs ok" type="button" wire:click="duplicate({{ $program->id }})">Duplikasi</button>
                <button class="adm-ic" type="button" title="Edit" aria-label="Edit {{ $program->title }}" wire:click="edit({{ $program->id }})">
                  <x-icon-edit />
                </button>
                <button class="adm-ic del" type="button" title="Hapus" aria-label="Hapus {{ $program->title }}" wire:click="delete({{ $program->id }})"
                        wire:confirm="Hapus program &quot;{{ $program->title }}&quot;?">
                  <x-icon-trash />
                </button>
              </div>
            </td>
          </tr>
        @empty
          <tr class="er">
            <td colspan="5" class="empty">Belum ada program kerja. Klik “+ Tambah Data Baru” untuk mulai.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="adm-tf">
    <span>Total program kerja</span>
    <span class="tag mono">{{ $total }}</span>
  </div>
</div>

<div class="mf {{ $panelOpen ? 'open' : '' }}" role="dialog" aria-modal="true" aria-label="Formulir program kerja">
  <div class="adm-card adm-sticky">
    <div class="adm-card-h">
      <h3>{{ $editingId ? 'Edit program kerja' : 'Tambah program kerja' }}</h3>
      @if ($editingId)
        <span class="tag mono">Mode edit</span>
      @endif
      <button type="button" class="adm-x" aria-label="Tutup formulir" wire:click="closePanel">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6L6 18"/><path d="M6 6l12 12"/></svg>
      </button>
    </div>

    <form wire:submit="save">
      <div class="adm-fs">
        <label class="adm-lb">Judul program kerja *
          <input type="text" wire:model="title" maxlength="160" placeholder="mis. Business Bootcamp Batch 3">
          @error('title') <small style="color:#B91C1C">{{ $message }}</small> @enderror
        </label>

        <div class="adm-row2">
          <label class="adm-lb">Divisi penanggung jawab
            <select wire:model="division_id">
              @foreach ($divisions as $division)
                <option value="{{ $division->id }}">{{ $division->name }}</option>
              @endforeach
            </select>
            @error('division_id') <small style="color:#B91C1C">{{ $message }}</small> @enderror
          </label>
          <label class="adm-lb">Status pelaksanaan
            <select wire:model="status">
              @foreach ($statuses as $key => $meta)
                <option value="{{ $key }}">{{ $meta['label'] }}</option>
              @endforeach
            </select>
          </label>
        </div>

        <label class="adm-lb">Waktu pelaksanaan
          <input type="text" wire:model="schedule" maxlength="60" placeholder="mis. NOV 2026">
        </label>

        <label class="adm-lb">Target sasaran peserta
          <input type="text" wire:model="audience" maxlength="100" placeholder="mis. Mahasiswa umum, Anggota baru">
        </label>

        <label class="adm-lb">Deskripsi &amp; output kegiatan
          <textarea wire:model="description" rows="4" maxlength="600"></textarea>
        </label>
      </div>

      <div class="adm-foot" style="display:flex;gap:8px;margin-top:18px">
        <button class="btn p" type="submit">{{ $editingId ? 'Simpan perubahan' : 'Tambah' }}</button>
        @if ($editingId)
          <button class="btn o" type="button" wire:click="closePanel">Batal</button>
        @endif
      </div>
    </form>
  </div>
</div>
<div class="adm-dov" wire:click="closePanel"></div>
</div>
