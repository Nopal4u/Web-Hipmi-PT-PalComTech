<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\BusinessSubmission;
use App\Models\Division;
use App\Models\GalleryAlbum;
use App\Models\Information;
use App\Models\Member;
use App\Models\Program;
use App\Models\Registration;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class HipmiSeeder extends Seeder
{
    public function run(): void
    {
        /* ------------------------------------------------------------------
         |  DIVISI
         * ------------------------------------------------------------------ */
        $divisions = [
            [
                'numeral' => 'I',
                'name' => 'Organisasi, Kaderisasi & Keanggotaan',
                'short_name' => 'Kaderisasi',
                'color' => '#2563EB',
                'icon' => 'M4 19h16M6 19V9l6-5 6 5v10M10 19v-6h4v6',
                'focus' => 'Mengelola administrasi, merekrut anggota baru, dan melatih kepemimpinan kader.',
                'learning' => 'Kepemimpinan, manajemen tim, dan cara menjaga loyalitas anggota.',
                'leader_name' => 'Lulu',
                'sort_order' => 1,
                'members' => ['Chelsea', 'Aqila', 'Zidan', 'Ridho'],
            ],
            [
                'numeral' => 'II',
                'name' => 'Ekonomi, UMKM & Pengembangan Wirausaha',
                'short_name' => 'UMKM',
                'color' => '#10B981',
                'icon' => 'M3 17l6-6 4 4 8-8M21 7v6h-6',
                'focus' => 'Mewadahi dan membimbing usaha mahasiswa agar naik kelas dan mandiri finansial.',
                'learning' => 'Manajemen cashflow dan scaling produk.',
                'leader_name' => 'Maryam',
                'sort_order' => 2,
                'members' => ['Bella', 'Destri', 'Andra', 'Salsa'],
            ],
            [
                'numeral' => 'III',
                'name' => 'Perindustrian, Perdagangan, Pasar Modal & Hukum',
                'short_name' => 'Hukum & Pasar Modal',
                'color' => '#8B5CF6',
                'icon' => 'M4 4h16v12H8l-4 4V4z',
                'focus' => 'Hilirisasi bisnis, ekspansi pasar, dan legalitas usaha mahasiswa.',
                'learning' => 'Legalitas PT/CV dan literasi saham.',
                'leader_name' => 'Fairuz',
                'sort_order' => 3,
                'members' => ['Deka', 'Nopal', 'Amin', 'Dana'],
            ],
            [
                'numeral' => 'IV',
                'name' => 'Pengembangan & Kajian Ekonomi',
                'short_name' => 'Kajian',
                'color' => '#6366F1',
                'icon' => 'M11 4a7 7 0 100 14 7 7 0 000-14zM21 21l-4.3-4.3',
                'focus' => 'Riset pasar dan analisis isu ekonomi untuk arah gerak bisnis organisasi.',
                'learning' => 'Riset pasar, membaca data, dan menyusun rekomendasi strategis.',
                'leader_name' => 'Ricky',
                'sort_order' => 4,
                'members' => ['Adi', 'Ayu Wahyu', 'Fajar', 'Rehan'],
            ],
            [
                'numeral' => 'V',
                'name' => 'Humas, Media & Kreatif',
                'short_name' => 'Humas & Kreatif',
                'color' => '#F59E0B',
                'icon' => 'M3 3h18v18H3zM3 9h18M9 21V9',
                'focus' => 'Mengelola media sosial, konten kreatif, branding, dan kerja sama eksternal.',
                'learning' => 'Content creation, branding, dan negosiasi sponsor.',
                'leader_name' => 'Asher',
                'sort_order' => 5,
                'members' => ['Ara', 'Ayu Mey', 'Alif', 'Pani'],
            ],
            [
                'numeral' => 'VI',
                'name' => 'Kepemudaan & Olahraga',
                'short_name' => 'Kepemudaan',
                'color' => '#0EA5E9',
                'icon' => 'M12 5a3 3 0 100 6 3 3 0 000-6zM5 21c0-4 3-6.5 7-6.5S19 17 19 21',
                'focus' => 'Mewadahi minat bakat anggota lewat olahraga dan kegiatan kepemudaan.',
                'learning' => 'Kerja tim, event organizing, dan membangun jejaring antaranggota.',
                'leader_name' => 'Khusnul',
                'sort_order' => 6,
                'members' => ['Tama', 'Panji', 'Jimmy', 'Hafiza'],
            ],
        ];

        $divisionModels = collect();

        foreach ($divisions as $data) {
            $members = $data['members'];
            unset($data['members']);

            $division = Division::create($data);
            $divisionModels->push($division);

            // Ketua divisi
            Member::create([
                'division_id' => $division->id,
                'group' => 'division',
                'name' => $division->leader_name,
                'position' => 'Ketua Divisi',
                'profile_type' => 'mahasiswa',
                'sort_order' => 0,
            ]);

            foreach ($members as $i => $name) {
                Member::create([
                    'division_id' => $division->id,
                    'group' => 'division',
                    'name' => $name,
                    'position' => 'Anggota',
                    'profile_type' => 'mahasiswa',
                    'sort_order' => $i + 1,
                ]);
            }
        }

        /* ------------------------------------------------------------------
         |  DOSEN PEMBIMBING & BPH
         * ------------------------------------------------------------------ */
        Member::create([
            'group' => 'dosen',
            'name' => 'Eko Setiawan',
            'position' => 'Dosen Pembimbing',
            'profile_type' => 'dosen',
            'sort_order' => 0,
        ]);

        $bph = [
            ['Ketua Umum', 'Handri'],
            ['Sekretaris', 'Malika'],
            ['Wakil Sekretaris', 'Kezia'],
            ['Bendahara', 'Kiki'],
            ['Wakil Bendahara', 'Aca'],
        ];

        foreach ($bph as $i => [$position, $name]) {
            Member::create([
                'group' => 'bph',
                'name' => $name,
                'position' => $position,
                'profile_type' => 'mahasiswa',
                'sort_order' => $i,
            ]);
        }

        /* ------------------------------------------------------------------
         |  PROGRAM KERJA
         * ------------------------------------------------------------------ */
        $programs = [
            ['AGU 2026', 'Business Bootcamp Angkatan Baru', 'Pelatihan intensif dasar kewirausahaan untuk calon anggota.', 'Calon anggota', 0, 'selesai'],
            ['SEP 2026', 'Talkshow: Dari Kos-kosan ke Startup', 'Diskusi bersama alumni yang membangun usaha sejak kuliah.', 'Mahasiswa umum', 1, 'selesai'],
            ['OKT 2026', 'Business Matching bersama UMKM Lokal', 'Kolaborasi dan mentoring langsung dengan UMKM mitra kampus.', 'Anggota & UMKM mitra', 4, 'berjalan'],
            ['NOV 2026', 'Kajian Ekonomi: Peluang Usaha Digital', 'Diskusi terbuka tentang tren bisnis digital bagi mahasiswa.', 'Mahasiswa umum', 3, 'mendatang'],
            ['NOV 2026', 'Showcase Produk Anggota', 'Konten dan pameran produk usaha anggota di media sosial.', 'Anggota', 4, 'mendatang'],
            ['DES 2026', 'Workshop Legalitas Usaha', 'Mengenal izin usaha, NIB, dan dasar PT/CV.', 'Anggota & umum', 2, 'mendatang'],
            ['DES 2026', 'Futsal Antaranggota', 'Mempererat solidaritas lewat olahraga bersama.', 'Anggota', 5, 'mendatang'],
        ];

        foreach ($programs as $i => [$schedule, $title, $description, $audience, $divisionIndex, $status]) {
            Program::create([
                'division_id' => $divisionModels[$divisionIndex]->id,
                'title' => $title,
                'description' => $description,
                'schedule' => $schedule,
                'audience' => $audience,
                'status' => $status,
                'sort_order' => $i,
            ]);
        }

        /* ------------------------------------------------------------------
         |  GALERI
         * ------------------------------------------------------------------ */
        $albums = [
            ['Business Bootcamp', 'Agu 2026', 'Internal', '#2563EB', 'Sesi pelatihan dasar kewirausahaan bagi calon anggota.', true],
            ['Pelantikan Pengurus', 'Jul 2026', 'Internal', '#1E40AF', 'Pelantikan pengurus HIPMI PT periode pertama.', false],
            ['Kunjungan Industri', 'Sep 2026', 'Eksternal', '#8B5CF6', 'Kunjungan ke pelaku usaha untuk belajar langsung operasional bisnis.', false],
            ['Talkshow Founder Muda', 'Sep 2026', 'Workshop', '#F59E0B', 'Diskusi bersama alumni yang merintis usaha sejak kuliah.', false],
            ['Bazar Kampus', 'Okt 2026', 'Eksternal', '#10B981', 'Anggota membuka lapak produk di bazar kampus.', false],
            ['Aksi Sosial', 'Okt 2026', 'Eksternal', '#0EA5E9', 'Kegiatan berbagi bersama masyarakat sekitar kampus.', false],
        ];

        foreach ($albums as $i => [$title, $date, $category, $color, $description, $pinned]) {
            GalleryAlbum::create([
                'title' => $title,
                'date_label' => $date,
                'category' => $category,
                'color' => $color,
                'description' => $description,
                'is_pinned' => $pinned,
                'sort_order' => $i,
            ]);
        }

        /* ------------------------------------------------------------------
         |  USAHA / UMKM
         * ------------------------------------------------------------------ */
        $businesses = [
            ['Kopi Kampus', 'F&B', '#10B981', 'Contoh Founder · Manajemen', 'Kopi susu dan minuman kekinian untuk mahasiswa.', 'kopikampus'],
            ['PixelCraft Studio', 'Digital & Jasa IT', '#8B5CF6', 'Contoh Founder · Sistem Informasi', 'Jasa desain, landing page, dan konten untuk UMKM.', 'pixelcraft'],
            ['Thrift Corner', 'Fashion & Apparel', '#F59E0B', 'Contoh Founder · Bisnis Digital', 'Pakaian preloved pilihan dengan harga mahasiswa.', 'thriftcorner'],
        ];

        foreach ($businesses as $i => [$name, $category, $color, $founder, $description, $instagram]) {
            Business::create([
                'name' => $name,
                'category' => $category,
                'color' => $color,
                'founder' => $founder,
                'description' => $description,
                'instagram' => $instagram,
                'whatsapp' => '081200000000',
                'is_active' => true,
                'sort_order' => $i,
            ]);
        }

        /* ------------------------------------------------------------------
         |  INFORMASI
         * ------------------------------------------------------------------ */
        Information::create([
            'title' => 'Open Recruitment Pengurus & Anggota Muda Batch II',
            'category' => 'Open Recruitment',
            'date_label' => '10 - 25 November 2026',
            'status' => 'buka',
            'summary' => 'Bergabunglah menjadi bagian dari ekosistem wirausaha muda ITB PalComTech.',
            'description' => "Persyaratan:\n1. Mahasiswa aktif ITB PalComTech\n2. Memiliki minat tinggi dalam wirausaha\n3. Bersedia mengikuti wawancara santai dan Business Bootcamp\n\nDaftar lewat tombol di bawah sebelum batas waktu pendaftaran.",
            'action_label' => 'Daftar Sekarang',
            'action_url' => 'https://forms.gle/contohFormOprec',
            'is_pinned' => true,
        ]);

        Information::create([
            'title' => 'Kajian Ekonomi: Peluang Usaha Digital',
            'category' => 'Agenda & Event',
            'date_label' => 'November 2026',
            'status' => 'buka',
            'summary' => 'Diskusi terbuka tentang tren bisnis digital bagi mahasiswa.',
            'description' => "Diskusi terbuka bersama Bidang Pengembangan & Kajian Ekonomi tentang tren dan peluang bisnis digital.\n\nTerbuka untuk mahasiswa umum. Hubungi narahubung untuk detail waktu dan tempat.",
            'action_label' => 'Daftar Sekarang',
        ]);

        Information::create([
            'title' => 'Business Bootcamp Angkatan Baru Telah Selesai',
            'category' => 'Pengumuman',
            'date_label' => 'Agustus 2026',
            'status' => 'tutup',
            'summary' => 'Pelatihan dasar kewirausahaan untuk calon anggota telah selesai dilaksanakan.',
            'description' => 'Pelatihan intensif dasar kewirausahaan untuk calon anggota telah selesai dilaksanakan. Pantau halaman ini untuk kabar angkatan berikutnya.',
            'action_label' => 'Detail',
        ]);

        /* ------------------------------------------------------------------
         |  AKUN ADMIN (password awal: hipmi2026)
         * ------------------------------------------------------------------ */
        $users = [
            ['Super Admin HIPMI', 'super_admin', 'super@hipmi.local', 'Ketua Umum', 'super_admin'],
            ['Admin Media & Kominfo', 'admin_kominfo', 'kominfo@hipmi.local', 'Media & Kominfo', 'admin_media'],
            ['Admin Divisi UMKM', 'admin_umkm', 'umkm@hipmi.local', 'Pengembangan UMKM', 'admin_umkm'],
        ];

        foreach ($users as [$name, $username, $email, $division, $role]) {
            User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'username' => $username,
                    'division' => $division,
                    'role' => $role,
                    'is_active' => true,
                    'must_change_password' => true,
                    'password' => Hash::make('hipmi2026'),
                ]
            );
        }

        /* ------------------------------------------------------------------
         |  PENGATURAN SITUS
         * ------------------------------------------------------------------ */
        $settings = [
            'site_name' => 'HIPMI PT PalComTech',
            'site_tagline' => 'Hub Wirausaha Muda ITB PalComTech',
            'whatsapp' => '62000000000',
            'email' => 'info@hipmipt.org',
            'instagram' => 'hipmipt',
            'secretariat' => 'Sekretariat HIPMI PT · ITB PalComTech',
            'ticker' => json_encode([
                'GALERI KEGIATAN & KATALOG USAHA MAHASISWA',
                'SEMINAR KEWIRAUSAHAAN SETIAP BULAN',
                'BAZAR KAMPUS & SHOWCASE USAHA ANGGOTA',
                'MENTORING BERSAMA PENGUSAHA SENIOR BPC HIPMI',
                'BUSINESS MATCHING BERSAMA MITRA UMKM',
            ]),
        ];

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
