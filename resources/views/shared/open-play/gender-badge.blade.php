@php
  $normalizedGender = strtolower(trim((string) ($gender ?? '')));
  $genderLabel = $gender ?: __('Not specified');
  $genderStyle = $normalizedGender === 'male'
      ? 'color:#1976d2;'
      : ($normalizedGender === 'female'
          ? 'color:#e83e8c;'
          : 'color:#6c757d;');
  $genderIcon = $normalizedGender === 'male'
      ? 'fa-mars'
      : ($normalizedGender === 'female' ? 'fa-venus' : 'fa-genderless');
@endphp
<span class="badge badge-icon ml-1 p-1" style="{{ $genderStyle }}" title="{{ __($genderLabel) }}" aria-label="{{ __($genderLabel) }}">
  <i class="fas {{ $genderIcon }}" aria-hidden="true"></i>
</span>
