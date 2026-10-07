@extends('vendors.layout')

@section('content')
  @php
    $ewalletProviders = [
        'GCash',
        'Maya',
        'GrabPay',
        'ShopeePay',
        'Coins.ph',
        'PalawanPay',
        'GoTyme Bank',
        'DiskarTech',
    ];
  @endphp
  <div class="page-header">
    <h4 class="page-title">{{ __('E-wallet Payment Methods') }}</h4>
  </div>

  <div class="row">
    <div class="col-lg-5">
      <div class="card">
        <div class="card-header"><div class="card-title">{{ __('Add E-wallet') }}</div></div>
        <div class="card-body">
          <div class="alert alert-info">
            {{ __('For security, the e-wallet account and business documents must belong to the registered vendor. Verification documents are stored privately and are not shown to customers.') }}
          </div>
          <form action="{{ route('vendor.ewallets.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
              <label>{{ __('E-wallet Name') }} *</label>
              <select class="form-control" name="name" required>
                <option value="" disabled @selected(!old('name'))>{{ __('Select E-wallet') }}</option>
                @foreach ($ewalletProviders as $provider)
                  <option value="{{ $provider }}" @selected(old('name') === $provider)>{{ $provider }}</option>
                @endforeach
              </select>
              @error('name')<p class="text-danger mt-2">{{ $message }}</p>@enderror
            </div>
            <div class="form-group">
              <label>{{ __('Account Number') }} *</label>
              <input class="form-control" name="account_number" value="{{ old('account_number') }}" required>
              @error('account_number')<p class="text-danger mt-2">{{ $message }}</p>@enderror
            </div>
            <div class="form-group">
              <label>{{ __('Fully Verified E-wallet Name') }} *</label>
              <input class="form-control" name="verified_name" value="{{ old('verified_name') }}" maxlength="150" required>
              <small class="form-text text-muted">{{ __('Enter the name exactly as it appears on the verified e-wallet profile.') }}</small>
              @error('verified_name')<p class="text-danger mt-2">{{ $message }}</p>@enderror
            </div>
            <div class="form-group">
              <label>{{ __('TIN Number') }} *</label>
              <input class="form-control" name="tin_number" value="{{ old('tin_number') }}" maxlength="30" pattern="[0-9-]{9,20}" required>
              @error('tin_number')<p class="text-danger mt-2">{{ $message }}</p>@enderror
            </div>
            <div class="form-group">
              <label>{{ __('DTI Business Name Number') }} *</label>
              <input class="form-control" name="dti_business_name_number" value="{{ old('dti_business_name_number') }}" maxlength="100" required>
              @error('dti_business_name_number')<p class="text-danger mt-2">{{ $message }}</p>@enderror
            </div>
            <div class="form-group">
              <label>{{ __('QR Code') }} *</label>
              <input class="form-control" type="file" name="qr_code" accept="image/png,image/jpeg,image/webp" required>
              @error('qr_code')<p class="text-danger mt-2">{{ $message }}</p>@enderror
            </div>
            <div class="form-group">
              <label>{{ __('Verified E-wallet Profile Screenshot') }} *</label>
              <input class="form-control" type="file" name="profile_screenshot" accept="image/png,image/jpeg,image/webp" required>
              <small class="form-text text-muted">{{ __('Upload the profile interface showing the fully verified name. Maximum 5 MB.') }}</small>
              @error('profile_screenshot')<p class="text-danger mt-2">{{ $message }}</p>@enderror
            </div>
            <div class="form-group">
              <label>{{ __('DTI/SEC Registration') }} *</label>
              <input class="form-control" type="file" name="business_registration_document" accept="application/pdf,image/png,image/jpeg,image/webp" required>
              <small class="form-text text-muted">{{ __('PDF or image, maximum 10 MB.') }}</small>
              @error('business_registration_document')<p class="text-danger mt-2">{{ $message }}</p>@enderror
            </div>
            <div class="form-group">
              <label>{{ __("Mayor's Permit") }} *</label>
              <input class="form-control" type="file" name="mayors_permit_document" accept="application/pdf,image/png,image/jpeg,image/webp" required>
              <small class="form-text text-muted">{{ __('PDF or image, maximum 10 MB.') }}</small>
              @error('mayors_permit_document')<p class="text-danger mt-2">{{ $message }}</p>@enderror
            </div>
            <div class="form-group">
              <label><input type="checkbox" name="status" value="1" checked> {{ __('Available at checkout') }}</label>
            </div>
            <div class="card-action"><button class="btn btn-primary" type="submit">{{ __('Save') }}</button></div>
          </form>
        </div>
      </div>
    </div>

    <div class="col-lg-7">
      @forelse ($wallets as $wallet)
        <div class="card mb-3">
          <div class="card-body">
            <form action="{{ route('vendor.ewallets.update', $wallet->id) }}" method="POST" enctype="multipart/form-data">
              @csrf
              @method('PUT')
              <div class="row align-items-center">
                <div class="col-md-3 text-center mb-3">
                  <img src="{{ asset('assets/admin/img/vendor-ewallet-qr/' . $wallet->qr_code) }}"
                    alt="{{ $wallet->name }} QR Code" class="img-fluid" style="max-height: 150px">
                </div>
                <div class="col-md-9">
                  <div class="form-group">
                    <label>{{ __('E-wallet Name') }}</label>
                    <select class="form-control" name="name" required>
                      @if (!in_array($wallet->name, $ewalletProviders, true))
                        <option value="{{ $wallet->name }}" selected>{{ $wallet->name }}</option>
                      @endif
                      @foreach ($ewalletProviders as $provider)
                        <option value="{{ $provider }}" @selected($wallet->name === $provider)>{{ $provider }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="form-group">
                    <label>{{ __('Account Number') }}</label>
                    <input class="form-control" name="account_number" value="{{ $wallet->account_number }}" required>
                  </div>
                  <div class="form-group">
                    <label>{{ __('Fully Verified E-wallet Name') }}</label>
                    <input class="form-control" name="verified_name" value="{{ $wallet->verified_name }}" maxlength="150" required>
                    @error('verified_name')<p class="text-danger mt-2">{{ $message }}</p>@enderror
                  </div>
                  <div class="form-group">
                    <label>{{ __('TIN Number') }}</label>
                    <input class="form-control" name="tin_number" value="{{ $wallet->tin_number }}" maxlength="30" pattern="[0-9-]{9,20}" required>
                    @error('tin_number')<p class="text-danger mt-2">{{ $message }}</p>@enderror
                  </div>
                  <div class="form-group">
                    <label>{{ __('DTI Business Name Number') }}</label>
                    <input class="form-control" name="dti_business_name_number" value="{{ $wallet->dti_business_name_number }}" maxlength="100" required>
                    @error('dti_business_name_number')<p class="text-danger mt-2">{{ $message }}</p>@enderror
                  </div>
                  <div class="form-group">
                    <label>{{ __('Replace QR Code') }}</label>
                    <input class="form-control" type="file" name="qr_code" accept="image/png,image/jpeg,image/webp">
                  </div>
                  @foreach ([
                    'profile_screenshot' => __('Verified E-wallet Profile Screenshot'),
                    'business_registration_document' => __('DTI/SEC Registration'),
                    'mayors_permit_document' => __("Mayor's Permit"),
                  ] as $documentField => $documentLabel)
                    <div class="form-group">
                      <label>{{ __('Replace') }} {{ $documentLabel }}</label>
                      @if ($wallet->{$documentField})
                        <a class="d-block mb-2" href="{{ route('vendor.ewallets.verification.download', [$wallet->id, $documentField]) }}">
                          <i class="fas fa-lock"></i> {{ __('View current private document') }}
                        </a>
                      @else
                        <p class="text-danger mb-2">{{ __('Required before this e-wallet can be available at checkout.') }}</p>
                      @endif
                      <input class="form-control" type="file" name="{{ $documentField }}"
                        accept="{{ $documentField === 'profile_screenshot' ? 'image/png,image/jpeg,image/webp' : 'application/pdf,image/png,image/jpeg,image/webp' }}"
                        @required(!$wallet->{$documentField})>
                      @error($documentField)<p class="text-danger mt-2">{{ $message }}</p>@enderror
                    </div>
                  @endforeach
                  <div class="form-group">
                    <label>
                      <input type="checkbox" name="status" value="1" @checked($wallet->status)> {{ __('Available at checkout') }}
                    </label>
                  </div>
                  <button class="btn btn-primary" type="submit">{{ __('Update') }}</button>
                </div>
              </div>
            </form>
            <form class="mt-2 text-right" action="{{ route('vendor.ewallets.destroy', $wallet->id) }}" method="POST" onsubmit="return confirm('{{ __('Are you sure') }}?')">
              @csrf
              @method('DELETE')
              <button class="btn btn-danger btn-sm" type="submit">{{ __('Delete') }}</button>
            </form>
          </div>
        </div>
      @empty
        <div class="card"><div class="card-body text-center">{{ __('No e-wallet payment methods found.') }}</div></div>
      @endforelse
    </div>
  </div>
@endsection
