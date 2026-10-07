@extends('vendors.layout')
@php
  Config::set('app.timezone', App\Models\BasicSettings\Basic::first()->timezone);
  $currentBillingTerm = !empty($membership)
      ? ($membership->billing_term ?: optional($membership->package)->term)
      : null;
  $replaceImmediately = !empty($membership) && ($currentBillingTerm === 'lifetime' || $membership->is_trial == 1);
  $startDate = $replaceImmediately
      ? \Illuminate\Support\Carbon::today()
      : \Illuminate\Support\Carbon::parse($membership->expire_date ?? \Carbon\Carbon::yesterday())->addDay();
  $billingOptions = collect(\App\Support\PackageBilling::TERMS)->filter(function ($details, $term) use ($package) {
      return $package->isBillingTermEnabled($term);
  })->map(function ($details, $term) use ($package, $startDate) {
      $discount = $package->discountForBillingTerm($term);
      return [
          'term' => $term,
          'label' => __($details['label']),
          'months' => $details['months'],
          'subtotal' => \App\Support\PackageBilling::subtotal($package->price, $term),
          'discount' => $discount,
          'price' => \App\Support\PackageBilling::total($package->price, $term, $discount),
          'expire_date' => \App\Support\PackageBilling::expiresAt($startDate, $term)->format('d-m-Y'),
      ];
  });
  $selectedBillingTerm = old('billing_term');
  if (!$billingOptions->has($selectedBillingTerm)) $selectedBillingTerm = $billingOptions->keys()->first();
@endphp
@section('style')
  <style>
    .package-checkout-shell{max-width:1050px;margin:0 auto 40px}.package-checkout-back{color:#64748b;font-weight:600}.package-checkout-card{background:#fff;border:1px solid #e5e7eb!important;border-radius:18px!important;box-shadow:0 18px 45px rgba(15,23,42,.08);overflow:hidden}.package-checkout-card .card-header{background:linear-gradient(135deg,#14532d,#16a34a)!important;border:0;padding:26px 30px}.package-checkout-card .card-title{color:#fff;font-size:25px;font-weight:750;margin-bottom:7px}.package-checkout-card .card-price{color:#fff}.package-checkout-card .card-price .price{font-size:32px;font-weight:800}.package-checkout-card .card-price .text{color:rgba(255,255,255,.78)}.checkout-eyebrow{color:rgba(255,255,255,.74);font-size:12px;font-weight:700;letter-spacing:.1em;text-transform:uppercase}.package-checkout-card .card-body{padding:28px 30px}.package-checkout-card .specification-list{margin:0;padding:0}.package-checkout-card .specification-list>li{border-bottom:1px solid #edf0f3;padding:15px 0}.checkout-section-title{color:#111827;font-size:16px;font-weight:750}.billing-term-option{background:#fff;border:2px solid #e5e7eb!important;border-radius:13px!important;color:#334155;cursor:pointer;position:relative;transition:.18s ease}.billing-term-option:hover,.billing-term-option.is-selected{background:#f0fdf4;border-color:#16a34a!important;box-shadow:0 8px 22px rgba(22,163,74,.1);transform:translateY(-2px)}.billing-term-option input{accent-color:#16a34a;position:absolute;right:12px;top:12px}.billing-term-option strong{color:#111827;font-size:16px}.term-total{color:#15803d;font-size:19px;font-weight:800}.checkout-summary{background:#f8fafc;border:1px solid #e5e7eb;border-radius:13px;margin:18px 0;padding:4px 18px}.checkout-summary__row{align-items:center;display:flex;justify-content:space-between;padding:12px 0}.checkout-summary__row+.checkout-summary__row{border-top:1px solid #e5e7eb}.checkout-summary__label{color:#64748b}.checkout-summary__value{color:#111827;font-weight:750}.checkout-summary__total .checkout-summary__value{color:#15803d;font-size:20px}.checkout-payment-panel{background:#f8fafc;border:1px solid #e5e7eb;border-radius:13px;margin-top:20px;padding:20px}.checkout-payment-panel label,.package-checkout-card .text-white{color:#334155!important;font-weight:650}.package-checkout-card .form-control{background:#fff!important;border:1px solid #dbe1e8!important;color:#111827!important;min-height:45px}.checkout-disclaimer{background:#fffbeb;border:1px solid #fde68a;border-radius:12px;color:#713f12;margin-top:22px;padding:16px 18px}.checkout-disclaimer__title{color:#713f12;font-size:15px;font-weight:750}.checkout-disclaimer p{font-size:13px;line-height:1.6;margin:6px 0 0}.checkout-terms{align-items:flex-start;display:flex;gap:10px;margin-top:16px}.checkout-terms input{accent-color:#16a34a;margin-top:4px;min-height:16px;min-width:16px}.checkout-terms label{color:#334155;line-height:1.55;margin:0}.checkout-terms a{color:#15803d;font-weight:700;text-decoration:underline}.package-checkout-card .card-footer{background:#fff;border-top:1px solid #edf0f3;padding:20px 30px}.package-checkout-card .card-footer .btn{background:#16a34a;border-color:#16a34a;border-radius:10px;color:#fff;font-size:15px;padding:13px}.package-checkout-card .card-footer .btn:hover{background:#15803d;border-color:#15803d}.checkout-secure-note{color:#64748b;font-size:12px;margin-top:10px;text-align:center}@media(max-width:767.98px){.package-checkout-card .card-header,.package-checkout-card .card-body,.package-checkout-card .card-footer{padding-left:20px;padding-right:20px}.package-checkout-card .card-title{font-size:21px}}
  </style>
@endsection
@section('content')
  @if ($message = Session::get('error'))
    <div class="alert alert-danger alert-block">
      <strong>{{ $message }}</strong>
    </div>
  @endif
  @if ($replaceImmediately)
    <div class="alert bg-warning alert-warning text-white text-center">
      <h3>{{ __('If you purchase this package') }} <strong class="text-dark">({{ $package->title }})</strong>,
        {{ __('then your current package') }} <strong class="text-dark">({{ $membership->package->title }}@if ($membership->is_trial == 1)
            <span class="badge badge-secondary">{{ __('Trial') }}</span>
          @endif)</strong>
        {{ __('will be replaced immediately') }}
      </h3>
    </div>
  @endif
  <div class="package-checkout-shell">
    <a class="package-checkout-back d-inline-block mb-3" href="{{ route('vendor.plan.extend.index') }}"><i class="fas fa-arrow-left mr-2"></i>{{ __('Back to Packages') }}</a>
      <div class="card card-pricing package-checkout-card">
        <form id="my-checkout-form" action="{{ route('vendor.plan.checkout') }}" method="post"
          enctype="multipart/form-data">
          @csrf
          <input type="hidden" name="package_id" value="{{ $package->id }}">
          <input type="hidden" name="vendor_id" value="{{ auth()->id() }}">
          <input type="hidden" name="payment_method" id="payment" value="{{ old('payment_method') }}">
          <div class="card-header">
            <div class="checkout-eyebrow">{{ __('Package Checkout') }}</div>
            <h4 class="card-title">{{ $package->title }}</h4>
            <div class="card-price">
              <span class="price">{{ $package->price == 0 ? 'Free' : format_price($package->price) }}</span>
              @if ((float) $package->price > 0)<span class="text">/{{ __('month') }}</span>@endif
            </div>
          </div>
          <div class="card-body">
            <div class="specification-list">
              @if ((float) $package->price > 0)
                <li class="d-block">
                  <label class="checkout-section-title d-block mb-3">{{ __('Choose Payment Term') }}</label>
                  <div class="row">
                    @foreach ($billingOptions as $option)
                      <div class="col-md-4 mb-2">
                        <label class="billing-term-option d-block p-3 text-center h-100 {{ $selectedBillingTerm === $option['term'] ? 'is-selected' : '' }}">
                          <input type="radio" name="billing_term" value="{{ $option['term'] }}"
                            data-price="{{ $option['price'] }}" data-price-label="{{ $option['price'] == 0 ? __('Free') : format_price($option['price']) }}"
                            data-expire-date="{{ $option['expire_date'] }}" {{ $selectedBillingTerm === $option['term'] ? 'checked' : '' }} required>
                          <strong class="d-block mt-2">{{ $option['label'] }}</strong>
                          <small>{{ $option['months'] }} {{ $option['months'] == 1 ? __('month') : __('months') }}</small>
                          <span class="term-total d-block mt-1">{{ $option['price'] == 0 ? __('Free') : format_price($option['price']) }}</span>
                          @if ($option['discount'] > 0)
                            <small class="badge badge-success mt-1">{{ rtrim(rtrim(number_format($option['discount'], 2), '0'), '.') }}% {{ __('discount') }}</small>
                            <small class="d-block"><del>{{ format_price($option['subtotal']) }}</del></small>
                          @endif
                        </label>
                      </div>
                    @endforeach
                  </div>
                  @error('billing_term')
                  <p class="text-warning mt-1">{{ $message }}</p>
                  @enderror
                </li>
              @else
                <input type="hidden" name="billing_term" value="monthly">
              @endif
              <div class="checkout-summary">
                <div class="checkout-summary__row">
                  <span class="checkout-summary__label">{{ __('Membership') }}</span>
                  <span class="checkout-summary__value">{{ $package->title }}</span>
                </div>
                <div class="checkout-summary__row">
                  <span class="checkout-summary__label">{{ __('Start Date') }}</span>
                  <span class="checkout-summary__value">{{ $startDate->format('d-m-Y') }}</span>
                </div>
                <div class="checkout-summary__row">
                  <span class="checkout-summary__label">{{ __('Expire Date') }}</span>
                  <span class="checkout-summary__value" id="billing-expire-date">{{ $billingOptions[$selectedBillingTerm]['expire_date'] }}</span>
                </div>
                <div class="checkout-summary__row checkout-summary__total">
                  <span class="checkout-summary__label">{{ __('Total Cost') }}</span>
                  <span class="checkout-summary__value" id="billing-total">{{ $billingOptions[$selectedBillingTerm]['price'] == 0 ? __('Free') : format_price($billingOptions[$selectedBillingTerm]['price']) }}</span>
                </div>
              </div>
              @if ($package->price != 0)
                <li id="payment-method-row" class="checkout-payment-panel">
                  <div class="form-group px-0">
                    <label class="checkout-section-title pl-4">{{ __('Payment Method') }}</label>
                    <select name="payment_method" class="form-control input-solid select2" id="payment-gateway" required>
                      <option value="" disabled selected>{{ __('Select a Payment Method') }}</option>
                      @foreach ($online_gateways as $payment_method)
                        <option value="{{ $payment_method->name }}"
                          {{ old('payment_method') == $payment_method->name ? 'selected' : '' }}>
                          {{ $payment_method->name }}</option>
                      @endforeach
                      @foreach ($offline_gateways as $payment_method)
                        <option value="{{ $payment_method->name }}"
                          {{ old('payment_method') == $payment_method->name ? 'selected' : '' }}>
                          {{ $payment_method->name }}</option>
                      @endforeach
                    </select>
                  </div>
                </li>
              @endif
              
              <p>When payment is successfully submitted, fill in the Transaction ID / Reference Number below:</p>
              <div id="instructions" class="text-left pl-4"></div>
              <div id="ewallet-payment-details" class="text-left d-none">
                <div class="form-group">
                  <label class="text-white">{{ __('Your E-wallet Account Name') }} *</label>
                  <input type="text" name="payer_account_name" class="form-control input-solid"
                    value="{{ old('payer_account_name') }}" maxlength="255" disabled>
                  @error('payer_account_name')
                    <p class="text-warning mt-1">{{ $message }}</p>
                  @enderror
                </div>
                <div class="form-group">
                  <label class="text-white">{{ __('Transaction ID / Reference Number') }} *</label>
                  <input type="text" name="payment_transaction_id" class="form-control input-solid"
                    value="{{ old('payment_transaction_id') }}" maxlength="255" disabled>
                  @error('payment_transaction_id')
                    <p class="text-warning mt-1">{{ $message }}</p>
                  @enderror
                </div>
              </div>
              <input type="hidden" name="is_receipt" value="0" id="is_receipt">

              <div class="iyzico-element {{ old('payment_method') == 'Iyzico' ? '' : 'd-none' }}">
                <input type="text" name="identity_number" class="form-control" placeholder="Identity Number">
                @error('identity_number')
                  <p class="text-danger text-left">{{ $message }}</p>
                @enderror
              </div>

              <div id="stripe-element">
                <!-- A Stripe Element will be inserted here. -->
              </div>
              <!-- Used to display form errors -->
              @php
                $display = 'none';
              @endphp
              <div id="stripe-errors" class="pb-2 text-danger text-left" role="alert"></div>

              {{-- START: Authorize.net Card Details Form --}}
              <div class="row gateway-details pt-3" id="tab-anet" style="display: {{ $display }};">
                <div class="col-lg-6">
                  <div class="form-group mb-3">
                    <input class="form-control" type="text" id="anetCardNumber" placeholder="Card Number" disabled />
                  </div>
                </div>
                <div class="col-lg-6 mb-3">
                  <div class="form-group">
                    <input class="form-control" type="text" id="anetExpMonth" placeholder="Expire Month" disabled />
                  </div>
                </div>
                <div class="col-lg-6 ">
                  <div class="form-group">
                    <input class="form-control" type="text" id="anetExpYear" placeholder="Expire Year" disabled />
                  </div>
                </div>
                <div class="col-lg-6 ">
                  <div class="form-group">
                    <input class="form-control" type="text" id="anetCardCode" placeholder="Card Code" disabled />
                  </div>
                </div>
                <input type="hidden" name="opaqueDataValue" id="opaqueDataValue" disabled />
                <input type="hidden" name="opaqueDataDescriptor" id="opaqueDataDescriptor" disabled />
                <ul id="anetErrors" style="display: {{ $display }};"></ul>
              </div>
              {{-- END: Authorize.net Card Details Form --}}
              @php
                $none = 'none';
              @endphp
              {{-- START: Iyzico Card Details Form --}}
              <div class="row gateway-details pt-3" id="tab-iyzico" style="display: {{ $none }};">
                <div class="col-lg-6">
                  <div class="form-group mb-3">
                    <input class="form-control" type="text" name="iyzicoCardNumber"
                      placeholder="{{ __('Card Number') }}" disabled />
                  </div>
                </div>
                <div class="col-lg-6 mb-3">
                  <div class="form-group">
                    <input class="form-control" type="text" name="iyzicoExpMonth"
                      placeholder="{{ __('Expire Month') }}" disabled />
                  </div>
                </div>
                <div class="col-lg-6 ">
                  <div class="form-group">
                    <input class="form-control" type="text" name="iyzicoExpYear"
                      placeholder="{{ __('Expire Year') }}" disabled />
                  </div>
                </div>
                <div class="col-lg-6 ">
                  <div class="form-group">
                    <input class="form-control" type="text" name="iyzicoCardCode"
                      placeholder="{{ __('Card Code') }}" disabled />
                  </div>
                </div>
                <div class="col-lg-6 ">
                  <div class="form-group">
                    <input class="form-control" type="text" name="iyzicoIdentityNumber"
                      placeholder="Identity Number" disabled />
                  </div>
                </div>

              </div>
              {{-- END: Iyzico Card Details Form --}}
              <div class="checkout-disclaimer" role="note">
                <div class="checkout-disclaimer__title"><i class="fas fa-exclamation-circle mr-2"></i>{{ __('Subscription and Payment Disclaimer') }}</div>
                <p>{{ __('Please review the selected subscription package,payment term, price, start date, and expiration date before continuing. subscription access is activated only after successful payment or, for payment methods requiring manual review, after payment verification and approval. Available features and limits are based on the selected package.') }}</p>
              </div>
              <div class="checkout-terms">
                <input type="checkbox" name="subscription_terms_accepted" id="subscription-terms-accepted" value="1" @checked(old('subscription_terms_accepted')) required>
                <label for="subscription-terms-accepted">
                  {{ __('I confirm that the package details are correct and I agree to the') }}
                  <a href="{{ route('terms_and_conditions') }}" target="_blank" rel="noopener">{{ __('Terms and Conditions') }}</a>.
                </label>
              </div>
              @error('subscription_terms_accepted')
                <p class="text-danger mt-2 mb-0">{{ $message }}</p>
              @enderror
            </div>
          </div>
          <div class="card-footer">
            <button class="btn btn-success btn-block" type="submit"><b>{{ __('Checkout Now') }}</b></button>
            <div class="checkout-secure-note"><i class="fas fa-lock mr-1"></i>{{ __('Your selected package, term, and total are verified before payment.') }}</div>
          </div>
        </form>
      </div>
  </div>
@endsection
@section('script')
  <script src="https://js.stripe.com/v3/"></script>
  {{-- START: Authorize.net Scripts --}}
  @php
    $anet = App\Models\PaymentGateway\OnlineGateway::find(21);
    $anerInfo = $anet->convertAutoData();
    $anetTest = $anerInfo['sandbox_check'];

    if ($anetTest == 1) {
        $anetSrc = 'https://jstest.authorize.net/v1/Accept.js';
    } else {
        $anetSrc = 'https://js.authorize.net/v1/Accept.js';
    }
  @endphp
  <script type="text/javascript" src="{{ $anetSrc }}" charset="utf-8"></script>
  {{-- END: Authorize.net Scripts --}}

  <script>
    "use strict";
    let stripe_key = "{{ $stripe_key }}";
    let public_key = "{{ $anerInfo['public_key'] }}";
    var paymentInstructionsRoute = "{{ route('vendor.payment.instructions') }}";
    var offlineData = @json($offline_gateways);
    let login_id = "{{ $anerInfo['login_id'] }}";
    document.querySelectorAll('input[name="billing_term"]').forEach((input) => {
      input.addEventListener('change', () => {
        document.querySelectorAll('.billing-term-option').forEach((option) => option.classList.remove('is-selected'));
        input.closest('.billing-term-option')?.classList.add('is-selected');
        document.getElementById('billing-total').textContent = input.dataset.priceLabel;
        document.getElementById('billing-expire-date').textContent = input.dataset.expireDate;
        const paymentRow = document.getElementById('payment-method-row');
        const paymentGateway = document.getElementById('payment-gateway');
        if (paymentRow && paymentGateway) {
          const isFree = Number(input.dataset.price) === 0;
          paymentRow.classList.toggle('d-none', isFree);
          paymentGateway.disabled = isFree;
          paymentGateway.required = !isFree;
        }
      });
    });
    document.querySelector('input[name="billing_term"]:checked')?.dispatchEvent(new Event('change'));
  </script>
  <script src="{{ asset('assets/admin/js/vendor-checkout.js') }}"></script>
@endsection
