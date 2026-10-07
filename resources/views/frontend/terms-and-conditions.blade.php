@extends('frontend.layout')

@section('pageHeading', __('Terms and Conditions & Disclaimer'))
@section('metaDescription', __('Terms, payment disclaimer, vendor verification limitations, and safety information for court bookings.'))

@section('content')
  @includeIf('frontend.partials.breadcrumb', [
      'breadcrumb' => $bgImg->breadcrumb,
      'title' => __('Terms and Conditions & Disclaimer'),
  ])

  <section class="pt-100 pb-100">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-9">
          <div class="card border-0 shadow-sm">
            <div class="card-body p-4 p-md-5">
              <h1 class="mb-4">{{ __('Terms and Conditions & Disclaimer') }}</h1>
              <p class="mb-4">
                {{ __('Welcome to') }} <strong>{{ $websiteInfo->website_title }}</strong>
                {{ __('(the "Platform"). By using our website to find or book sports courts, you agree to the following terms.') }}
              </p>

              <h3 class="mt-4 mb-3">{{ __('1. Platform Role Only') }}</h3>
              <ul class="mb-4">
                <li class="mb-2">
                  <strong>{{ __('Informational Directory:') }}</strong>
                  {{ __('This Platform serves strictly as a directory to connect players with private sports court vendors.') }}
                </li>
                <li>
                  <strong>{{ __('No Contractual Party:') }}</strong>
                  {{ __('We do not own, operate, or manage any of the listed courts. Any booking made is a direct agreement solely between the user (player) and the vendor (court owner).') }}
                </li>
              </ul>

              <h3 class="mt-4 mb-3">{{ __('2. Direct Payment Warning & Risks') }}</h3>
              <ul class="mb-4">
                <li class="mb-2">
                  <strong>{{ __('Direct Transactions:') }}</strong>
                  {{ __('All payments are sent directly to the vendor via their displayed e-wallet (e.g., GCash, Maya) or offline methods.') }}
                </li>
                <li class="mb-2">
                  <strong>{{ __('User Responsibility:') }}</strong>
                  {{ __('The Platform does not process, handle, or hold your money. You are responsible for verifying the recipient\'s name in your e-wallet app before sending funds.') }}
                </li>
                <li>
                  <strong>{{ __('No Refunds from Us:') }}</strong>
                  {{ __('The Platform is not liable for any financial losses, scams, double-bookings, or failure of the vendor to provide the court. Any refund requests must be handled directly with the vendor.') }}
                </li>
              </ul>

              <h3 class="mt-4 mb-3">{{ __('3. Verification and Accuracy') }}</h3>
              <ul class="mb-4">
                <li class="mb-2">
                  <strong>{{ __('Limitation of Checks:') }}</strong>
                  {{ __('While we manually review vendor business permits during onboarding to promote safety, we do not guarantee the current real-time condition, availability, or safety of the physical courts.') }}
                </li>
                <li>
                  <strong>{{ __('Report Violations:') }}</strong>
                  {{ __('If you encounter a fake listing or a fraudulent vendor, please use our "Report" tool immediately so we can investigate and remove the listing.') }}
                </li>
              </ul>

              <h3 class="mt-4 mb-3">{{ __('4. Safety and Liability') }}</h3>
              <ul class="mb-0">
                <li>
                  <strong>{{ __('Play at Your Own Risk:') }}</strong>
                  {{ __('The Platform is not responsible for any physical injuries, accidents, property damage, or theft that may occur during your rental time at any vendor\'s facility.') }}
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
@endsection
