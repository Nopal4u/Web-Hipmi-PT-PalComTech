<div class="adm-page">
<x-admin-page-header
  title="Struktur Pengurus"
  desc="Perbarui susunan pengurus, CV, dan portofolio anggota."
  :link="route('team')"
/>

<x-admin-metrics />

<div class="adm-bar">
  <b>Cadangan data</b>
  <button class="btn o" type="button" wire:click="export">Ekspor struktur.json</button>
  <label class="btn o" style="cursor:pointer">
    Impor
    <input type="file" accept=".json,application/json" hidden wire:model="importFile">
  </label>
  <button class="btn o" type="button" wire:click="reloadData">Muat ulang data</button>
  <span class="mono adm-save" id="as">{{ $savedAt ? 'Tersimpan · '.$savedAt : '' }}</span>
</div>

@error('importFile')
  <div class="adm-bar" style="border-color:#FECACA;background:#FEF2F2;color:#B91C1C;font-size:13px">{{ $message }}</div>
@enderror

<div class="adm-grid">
  @foreach ($groups as $group)
    <details class="adm-card adm-grp" style="--c:{{ $group['color'] }}" open wire:key="grp-{{ $group['key'] }}">
      <summary>
        <i class="adm-dot"></i>{{ $group['title'] }}
        <span class="tag mono">{{ $group['count'] }} orang</span>
      </summary>

      <div class="adm-gb">
        @foreach ($group['rows'] as $row)
          @php
            $id = $row['id'];
            $person = $people[$id] ?? [];
            $dosen = ($person['profile_type'] ?? 'mahasiswa') === 'dosen';
            $photoUrl = \App\Support\Media::url($person['photo'] ?? '');
          @endphp

          <div class="arw" wire:key="person-{{ $id }}"
               x-data="{ mods: @js(array_values($row['active_mods'])) }">
            <div class="ar">
              <label class="ap" title="Pilih foto">
                <x-avatar :name="$person['name'] ?? '?'" :color="$group['color']" :size="48" :photo="$photoUrl ?: null" />
                <input type="file" accept="image/*" hidden wire:model="photoUploads.{{ $id }}">
              </label>

              <div>
                <small>{{ $row['label'] }}</small>
                <input type="text" aria-label="Nama: {{ $row['label'] }}" placeholder="Nama"
                       wire:model.live.debounce.600ms="people.{{ $id }}.name">
              </div>

              @if ($photoUrl)
                <button class="xs" type="button" wire:click="removePhoto({{ $id }})">Hapus foto</button>
              @endif

              @if ($row['removable'])
                <button class="xs" type="button" wire:click="removeMember({{ $id }})"
                        wire:confirm="Hapus anggota ini?">Hapus</button>
              @endif
            </div>

            <details class="adm-pd" wire:ignore.self>
              <summary>
                <span>Detail profil &amp; opsi tambahan</span>
                <span class="adm-cnt" x-text="mods.length + ' opsi tambahan'">{{ count($row['active_mods']) }} opsi tambahan</span>
              </summary>

              <fieldset class="adm-kat">
                <legend>Kategori profil</legend>
                <label class="adm-rd">
                  <input type="radio" name="tipe-{{ $id }}" value="mahasiswa" @checked(! $dosen)
                         wire:model.live="people.{{ $id }}.profile_type"> Pengurus Mahasiswa
                </label>
                <label class="adm-rd">
                  <input type="radio" name="tipe-{{ $id }}" value="dosen" @checked($dosen)
                         wire:model.live="people.{{ $id }}.profile_type"> Dosen Pembimbing
                </label>
              </fieldset>

              <h5>Data utama</h5>
              <label>Jabatan di organisasi
                <input type="text" value="{{ $person['position'] ?? '' }}" placeholder="{{ $row['label'] }}"
                       wire:model.live.debounce.600ms="people.{{ $id }}.position">
              </label>
              <p class="adm-note">Divisi / bidang: <b>{{ $row['group_name'] }}</b>, mengikuti grup tempat orang ini berada.</p>
              <label>Keahlian / spesialisasi
                <input type="text" value="{{ $person['expertise'] ?? '' }}" placeholder="mis. Web Development, Financial Analysis"
                       wire:model.live.debounce.600ms="people.{{ $id }}.expertise">
              </label>

              @if ($dosen)
                <div class="adm-row2">
                  <label>Fakultas / instansi kampus
                    <input type="text" value="{{ $person['faculty'] ?? '' }}" placeholder="mis. Fakultas Ilmu Komputer"
                           wire:model.live.debounce.600ms="people.{{ $id }}.faculty">
                  </label>
                  <label>NIDN / gelar akademik
                    <input type="text" value="{{ $person['nidn'] ?? '' }}" placeholder="mis. 021405xxxx"
                           wire:model.live.debounce.600ms="people.{{ $id }}.nidn">
                  </label>
                </div>
              @else
                <div class="adm-row2">
                  <label>Program studi
                    <input type="text" value="{{ $person['study_program'] ?? '' }}" placeholder="mis. Sistem Informasi"
                           wire:model.live.debounce.600ms="people.{{ $id }}.study_program">
                  </label>
                  <label>Angkatan
                    <input type="text" value="{{ $person['year'] ?? '' }}" placeholder="2024"
                           wire:model.live.debounce.600ms="people.{{ $id }}.year">
                  </label>
                </div>
              @endif

              <h5>Opsi tambahan</h5>
              <div class="adm-add" role="group" aria-label="Tambah opsi" x-show="mods.length < {{ count($moduleMeta) }}">
                @foreach ($moduleMeta as $module => $meta)
                  <button class="xs" type="button" x-cloak
                          x-show="! mods.includes('{{ $module }}')"
                          @click="if (! mods.includes('{{ $module }}')) { mods.push('{{ $module }}'); $wire.addModule({{ $id }}, '{{ $module }}'); document.getElementById('as').textContent = 'Tersimpan · ' + new Date().toTimeString().slice(0, 8) }">
                    {{ $meta['button'] }}
                  </button>
                @endforeach
              </div>
              <p class="adm-note" x-cloak x-show="mods.length >= {{ count($moduleMeta) }}">Semua opsi tambahan sudah dipakai.</p>

              @foreach ($moduleMeta as $module => $meta)
                <div class="adm-mod" wire:key="mod-{{ $id }}-{{ $module }}" x-cloak x-show="mods.includes('{{ $module }}')">
                  <div class="adm-mh">
                    <span>{{ $module === 'bisnis' && $dosen ? $meta['title_dosen'] : $meta['title'] }}</span>
                    <button class="xs" type="button"
                            @click="mods = mods.filter(m => m !== '{{ $module }}')"
                            wire:click="removeModule({{ $id }}, '{{ $module }}')"
                            aria-label="Hapus opsi {{ $meta['title'] }}">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18M8 6V4h8v2M6 6l1 14h10l1-14M10 11v6M14 11v6"/></svg>
                      Hapus
                    </button>
                  </div>

                  @if ($module === 'bisnis')
                    <label>Nama usaha / brand
                      <input type="text" value="{{ $person['business'] ?? '' }}" placeholder="mis. TechNova IT Solution"
                             wire:model.live.debounce.600ms="people.{{ $id }}.business">
                    </label>
                  @elseif ($module === 'cv')
                    <label>URL berkas
                      <input type="text" inputmode="url" value="{{ $person['cv_url'] ?? '' }}" placeholder="https://drive.google.com/file/d/…"
                             wire:model.live.debounce.600ms="people.{{ $id }}.cv_url">
                    </label>
                  @elseif ($module === 'porto')
                    <label>URL portofolio
                      <input type="text" inputmode="url" value="{{ $person['portfolio_url'] ?? '' }}" placeholder="https://behance.net/…"
                             wire:model.live.debounce.600ms="people.{{ $id }}.portfolio_url">
                    </label>
                  @elseif ($module === 'sosmed')
                    <div class="adm-row2">
                      <label>LinkedIn (URL profil)
                        <input type="text" inputmode="url" value="{{ $person['linkedin'] ?? '' }}" placeholder="https://linkedin.com/in/…"
                               wire:model.live.debounce.600ms="people.{{ $id }}.linkedin">
                      </label>
                      <label>Instagram (tanpa @)
                        <input type="text" value="{{ $person['instagram'] ?? '' }}" placeholder="username"
                               wire:model.live.debounce.600ms="people.{{ $id }}.instagram">
                      </label>
                    </div>
                  @endif
                </div>
              @endforeach
            </details>
          </div>
        @endforeach

        @if ($group['add_dosen'])
          <button class="btn o" type="button" wire:click="addDosen">+ Tambah dosen pembimbing</button>
        @endif

        @if ($group['add_division'])
          <button class="btn o" type="button" wire:click="addMember({{ $group['add_division'] }})">+ Tambah anggota</button>
        @endif
      </div>
    </details>
  @endforeach
</div>
</div>
