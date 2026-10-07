@extends('frontend.layout')

@section('pageHeading', __('Vendor Site Agreement'))
@section('metaDescription', __('Agreement governing vendor Site listings, verification, bookings, payments, safety, and customer obligations.'))

@section('content')
  @includeIf('frontend.partials.breadcrumb', ['breadcrumb' => $bgImg->breadcrumb, 'title' => __('Vendor Site Agreement')])
  <section class="pt-100 pb-100">
    <div class="container"><div class="row justify-content-center"><div class="col-lg-9">
      <div class="card border-0 shadow-sm"><div class="card-body p-4 p-md-5">
        <h1>{{ __('Vendor Site Agreement') }}</h1>
        <p class="text-muted">{{ __('Version') }} {{ $agreementVersion }}</p>
        <p>{{ __('This agreement applies when a vendor creates or manages a Site on') }} <strong>{{ $websiteInfo->website_title }}</strong> {{ __('(the “Platform”). By accepting it, the vendor confirms that they are authorized to list and operate the Site.') }}</p>

        <h3 class="mt-4">{{ __('1. Authority and truthful information') }}</h3>
        <p>{{ __('The vendor confirms that all business, identity, permit, Site, court, pricing, schedule, capacity, and contact information supplied to the Platform is truthful, current, and not misleading. The vendor must promptly correct information that becomes inaccurate.') }}</p>

        <h3 class="mt-4">{{ __('2. Verification documents') }}</h3>
        <p>{{ __('The vendor may save a Site before uploading verification documents, but the Site cannot be activated or accept frontend bookings until the required Business Permit, DTI/SEC Registration, and Mayor’s Permit are uploaded and approved. The Platform may request updated or additional proof and may reject, suspend, or revoke verification when documents are missing, expired, altered, or inconsistent.') }}</p>

        <h3 class="mt-4">{{ __('3. Legal compliance and facility safety') }}</h3>
        <p>{{ __('The vendor is responsible for maintaining all registrations, permits, licenses, tax obligations, insurance, safety measures, and operating approvals required for the Site. The vendor must keep courts, equipment, access areas, and emergency procedures reasonably safe and suitable for the services advertised.') }}</p>

        <h3 class="mt-4">{{ __('4. Bookings, pricing, and customer fulfillment') }}</h3>
        <p>{{ __('The vendor must publish accurate prices, availability, inclusions, restrictions, cancellation terms, and capacity. The vendor must honor confirmed bookings, prevent double-booking, provide the advertised court or a customer-approved alternative, and handle vendor-caused cancellations, disputes, and refunds fairly and promptly.') }}</p>

        <h3 class="mt-4">{{ __('5. Payments and records') }}</h3>
        <p>{{ __('The vendor is responsible for the accuracy and security of its configured payment accounts, for validating direct-payment references, and for issuing any receipt or record required by law. The vendor must not request payment through unapproved or misleading account details.') }}</p>

        <h3 class="mt-4">{{ __('6. Customer data and conduct') }}</h3>
        <p>{{ __('Customer names, contact information, booking details, and other personal data may be used only to fulfill bookings, provide support, meet lawful obligations, and resolve disputes. The vendor must protect this information and must not sell, misuse, or disclose it without a lawful basis.') }}</p>

        <h3 class="mt-4">{{ __('7. Platform review and enforcement') }}</h3>
        <p>{{ __('The Platform may review listings and documents, investigate complaints, hide or deactivate a Site, pause bookings, or remove access when necessary to protect customers, enforce platform rules, or comply with law. Vendor verification indicates completion of the Platform review process; it is not a guarantee of the vendor, facility, or future performance.') }}</p>

        <h3 class="mt-4">{{ __('8. Vendor responsibility') }}</h3>
        <p>{{ __('The vendor remains responsible for its Site operations, staff, representations, customer service, taxes, regulatory compliance, injuries or property issues attributable to its operations, and fulfillment of bookings. Continued use of the Platform is also subject to the general Terms and Conditions and applicable policies.') }}</p>

        <div class="alert alert-warning mt-4 mb-0">{{ __('This operational agreement should be reviewed by qualified Philippine legal counsel before production use, particularly for refund, liability, tax, privacy, and dispute-resolution provisions.') }}</div>
      </div></div>
    </div></div></div>
  </section>
@endsection
