@php
  use App\Models\Setting;

  $secretariat = Setting::get('secretariat');
  $email = Setting::get('email');
  $instagram = Setting::get('instagram');
  $whatsapp = Setting::get('whatsapp');
@endphp

<footer>
  <div class="w fk">
    @if ($secretariat)
      <span><b>Sekretariat</b> {{ preg_replace('/^Sekretariat\s*/i', '', $secretariat) }}</span>
    @endif
    @if ($email)
      <span><b>Email</b> <a href="mailto:{{ $email }}">{{ $email }}</a></span>
    @endif
    @if ($instagram)
      <span><b>Instagram</b> <a href="https://instagram.com/{{ ltrim($instagram, '@') }}" target="_blank" rel="noopener">{{ '@'.ltrim($instagram, '@') }}</a></span>
    @endif
    @if ($whatsapp && $whatsapp !== '62000000000')
      <span><b>WhatsApp</b> <a href="https://wa.me/{{ $whatsapp }}" target="_blank" rel="noopener">Hubungi sekretariat</a></span>
    @endif
  </div>

  <div class="w">
    <div class="fl">
      <img src="{{ asset('images/hipmi.webp') }}" alt="HIPMI PT">
      <img src="{{ asset('images/itb.webp') }}" alt="ITB PalComTech">
      <span>Institut Teknologi dan Bisnis PalComTech</span>
    </div>
    <span>© {{ date('Y') }} HIPMI PT. Seluruh hak cipta dilindungi. · <a href="{{ route('admin.login') }}">Admin</a></span>
  </div>
</footer>
