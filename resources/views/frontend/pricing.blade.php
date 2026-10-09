@extends('frontend.layout')

@section('pageHeading')
  @if (!empty($pageHeading))
    {{ $pageHeading->pricing_page_title }}
  @else
    {{ __('Pricing') }}
  @endif
@endsection

@section('style')
  <style>
    .pricing-area_v1 {
      position: relative;
      overflow: hidden;
      background:
        radial-gradient(circle at 8% 8%, rgba(47, 111, 237, .10), transparent 28%),
        radial-gradient(circle at 92% 18%, rgba(20, 184, 166, .08), transparent 24%),
        linear-gradient(180deg, #f6f9fd 0%, #ffffff 55%);
    }

    .pricing-hero {
      position: relative;
      max-width: 900px;
      margin: 0 auto 45px;
      padding: 36px 30px;
      border: 1px solid #e4eaf1;
      border-radius: 24px;
      background: rgba(255, 255, 255, .88);
      box-shadow: 0 22px 55px rgba(31, 55, 88, .08);
    }

    .pricing-kicker {
      display: inline-block;
      margin-bottom: 10px;
      color: var(--color-primary, #2f6fed);
      font-size: 13px;
      font-weight: 700;
      letter-spacing: .12em;
      text-transform: uppercase;
    }

    .pricing-intro {
      max-width: 720px;
      margin-right: auto;
      margin-left: auto;
      color: #667085;
    }

    .pricing-benefits {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 10px;
      margin-top: 24px;
    }

    .pricing-benefits span {
      display: inline-flex;
      align-items: center;
      gap: 7px;
      padding: 8px 13px;
      border: 1px solid #e2e8f0;
      border-radius: 50px;
      background: #fff;
      color: #344054;
      font-size: 13px;
      font-weight: 600;
    }

    .pricing-benefits i {
      color: #16a36a;
    }

    .frontend-package-card {
      position: relative;
      height: calc(100% - 30px);
      padding: 30px !important;
      border: 1px solid #e3e9f0 !important;
      background: #fff;
      box-shadow: 0 14px 38px rgba(16, 24, 40, .07);
      transition: transform .25s ease, box-shadow .25s ease;
    }

    .frontend-package-card::after {
      display: none !important;
    }

    .frontend-package-card:hover {
      border-color: rgba(47, 111, 237, .4) !important;
      background: #fff !important;
      transform: translateY(-7px) !important;
      box-shadow: 0 24px 55px rgba(16, 24, 40, .12) !important;
    }

    .frontend-package-card.active {
      border: 2px solid var(--color-primary, #2f6fed) !important;
      box-shadow: 0 24px 60px rgba(47, 111, 237, .16);
    }

    .frontend-package-card :is(.card_title, .card_text, .price, .period, .card_list *, .show-more),
    .frontend-package-card:hover :is(.card_title, .card_text, .price, .period, .card_list *, .show-more),
    .frontend-package-card.active :is(.card_title, .card_text, .price, .period, .card_list *, .show-more) {
      color: #243447 !important;
    }

    .frontend-package-card .card_top {
      padding-bottom: 18px;
      border-bottom: 1px solid #edf1f5;
    }

    .frontend-package-card .card_icon,
    .frontend-package-card:hover .card_icon,
    .frontend-package-card.active .card_icon {
      --size: 58px;
      color: var(--color-primary, #2f6fed);
      background: rgba(47, 111, 237, .10);
    }

    .popular-ribbon {
      position: absolute;
      top: 16px;
      right: 16px;
      padding: 5px 10px;
      border-radius: 50px;
      color: #fff;
      background: var(--color-primary, #2f6fed);
      font-size: 10px;
      font-weight: 700;
      letter-spacing: .06em;
      text-transform: uppercase;
    }

    .plan-price-box,
    .frontend-package-card:hover .plan-price-box,
    .frontend-package-card.active .plan-price-box {
      padding: 18px;
      border: 1px solid #e5eaf0;
      border-radius: 14px;
      background: #f8fafc !important;
    }

    .price-label {
      display: block;
      margin-bottom: 3px;
      color: #667085;
      font-size: 11px;
      font-weight: 700;
      letter-spacing: .05em;
      text-transform: uppercase;
    }

    .frontend-package-card .card_subtitle h4 {
      font-size: 30px;
      font-weight: 800;
    }

    .discount-list {
      display: flex;
      flex-wrap: wrap;
      gap: 6px;
      margin-top: 12px;
    }

    .discount-chip {
      padding: 5px 9px;
      border-radius: 6px;
      color: #087443;
      background: #dcfae6;
      font-size: 11px;
      font-weight: 700;
    }

    .features-heading {
      color: #667085;
      font-size: 11px;
      font-weight: 700;
      letter-spacing: .06em;
      text-transform: uppercase;
    }

    .frontend-package-card .card_list li {
      gap: 15px;
      padding: 10px 0;
      border-bottom: 1px solid #eef2f6;
    }

    .frontend-package-card .card_list i,
    .frontend-package-card:hover .card_list i,
    .frontend-package-card.active .card_list i {
      color: #fff !important;
      background: #16a36a !important;
    }

    .frontend-package-card .card_list i.not-active,
    .frontend-package-card:hover .card_list i.not-active,
    .frontend-package-card.active .card_list i.not-active {
      color: #98a2b3 !important;
      background: #f2f4f7 !important;
    }

    .frontend-package-card .btn-primary,
    .frontend-package-card:hover .btn-primary,
    .frontend-package-card.active .btn-primary {
      color: #fff !important;
      background: var(--color-primary, #2f6fed) !important;
      box-shadow: 0 9px 22px rgba(47, 111, 237, .2) !important;
    }

    .pricing-note {
      color: #667085;
      font-size: 13px;
      text-align: center;
    }

    @media (max-width: 767px) {
      .pricing-hero {
        padding: 28px 18px;
      }

      .pricing-benefits {
        align-items: stretch;
        flex-direction: column;
      }

      .pricing-benefits span {
        justify-content: center;
      }
    }

    .pricing-empty-state {
      padding: 42px 24px;
      border: 1px dashed #cfd8e3;
      border-radius: 14px;
      background: #fff;
      color: #667085;
      text-align: center;
    }
  </style>
@endsection

@section('metaKeywords')
  @if (!empty($seoInfo))
    {{ $seoInfo->meta_keyword_pricing }}
  @endif
@endsection

@section('metaDescription')
  @if (!empty($seoInfo))
    {{ $seoInfo->meta_description_pricing }}
  @endif
@endsection

@section('content')
  <!-- Page title start-->
  @includeIf('frontend.partials.breadcrumb', [
      'breadcrumb' => $bgImg->breadcrumb,
      'title' => !empty($pageHeading) ? $pageHeading->pricing_page_title : __('Pricing'),
  ])
  <!-- Page title end-->

  <section class="pricing-area pricing-area_v1 pt-100 pb-70">
    <div class="container">
      <div class="row">
        <div class="col-12">
          <div class="section-title title-center pricing-hero" data-aos="fade-up">
            <span class="pricing-kicker">{{ __('Vendor Packages') }}</span>
            <h2 class="title mb-15">{{ __('Choose the right plan for your pickleball business') }}</h2>
            <p class="pricing-intro mb-30">{{ __('Publish your sites and courts, manage reservations, and unlock vendor tools based on your selected package.') }}</p>
            <div class="pricing-benefits">
              <span><i class="fas fa-check-circle"></i>{{ __('Flexible billing terms') }}</span>
              <span><i class="fas fa-check-circle"></i>{{ __('Secure vendor checkout') }}</span>
              <span><i class="fas fa-check-circle"></i>{{ __('Upgrade as you grow') }}</span>
            </div>
            @if (count($terms) > 1)
            <div class="tabs-navigation tabs-navigation_v3 mt-25">
              <ul class="nav nav-tabs radius-md" data-hover="fancyHover">

                @foreach ($terms as $term)
                  <li class="nav-item {{ $loop->first ? 'active' : '' }}">
                    <button class="nav-link hover-effect btn-md radius-sm {{ $loop->first ? 'active' : '' }}"
                      data-bs-toggle="tab" data-bs-target="#{{ strtolower($term) }}" type="button">
                      {{ __($term) }}
                    </button>
                  </li>
                @endforeach
              </ul>
            </div>
            @endif
          </div>
          <div class="tab-content" data-aos="fade-up">

            @php
              $packages = \App\Models\Package::where('status', '1')->get();
            @endphp
            @foreach ($terms as $term)
              <div class="tab-pane slide {{ $loop->first ? 'active show' : '' }}" id="{{ strtolower($term) }}">
                <div class="row justify-content-center">
                  @foreach ($packages as $package)
                    @php
                      $permissions = $package->features;
                      if (!empty($package->features)) {
                          $permissions = json_decode($permissions, true);
                      }
                    @endphp
                    <div class="col-md-6 col-lg-4 item">
                      <div class="card frontend-package-card p-30 mb-30 radius-lg border {{ $package->recommended ? 'active' : '' }}">
                        @if ($package->recommended == '1')
                          <span class="popular-ribbon">{{ __('Most Popular') }}</span>
                        @endif
                        <div class="card_top">
                          <div class="card_icon">
                            <i class="{{ $package->icon }}"></i>
                          </div>
                          <div class="pricing_card_header d-flex flex-column">
                            <h3 class="card_title mb-0">{{ __($package->title) }}</h3>
                          </div>
                        </div>
                        <div class="card_subtitle plan-price-box mt-20">
                          <span class="price-label">{{ (float) $package->price > 0 ? __('Starting at') : __('Plan price') }}</span>
                          <h4 class="mb-0">{{ $package->price == 0 ? __('Free') : symbolPrice($package->price) }}
                            @if ((float) $package->price > 0) / {{ __('Month') }} @endif
                          </h4>
                          @if ((float) $package->price > 0)
                            <small>{{ __('Choose from the available payment terms during purchase.') }}</small>
                          @else
                            <small>{{ __('No payment or payment-term selection required.') }}</small>
                          @endif
                          @php
                            $termDiscounts = [
                                'monthly' => [__('Monthly'), $package->monthly_discount],
                                'semi-annual' => [__('Semi-Annual'), $package->semi_annual_discount],
                                'yearly' => [__('Annual'), $package->annual_discount],
                            ];
                          @endphp
                          <div class="discount-list">
                            @foreach ($termDiscounts as $termKey => [$discountTerm, $discount])
                              @if ((float) $package->price > 0 && $package->isBillingTermEnabled($termKey) && (float) $discount > 0)
                                <span class="discount-chip">{{ $discountTerm }}: {{ rtrim(rtrim(number_format($discount, 2), '0'), '.') }}% {{ __('off') }}</span>
                              @endif
                            @endforeach
                          </div>
                        </div>
                        <div class="features-heading mt-25">{{ __('Included with this plan') }}</div>
                        <ul class="card_list toggle-list list-unstyled mt-25" data-toggle-list="pricingToggle"
                          data-toggle-show="5">
                          <li>
                            <span><i class="fal fa-check"></i>
                              @if ($package->number_of_hotel == 999999)
                                {{ __('Sites') }}
                              @elseif($package->number_of_hotel == 1)
                                {{ __('Site') }}
                              @else
                                {{ __('Sites') }}
                              @endif
                            </span>
                            <span>
                              @if ($package->number_of_hotel == 999999)
                                {{ __('(Unlimited)') }}
                              @else
                                ({{ $package->number_of_hotel }})
                              @endif
                            </span>
                          </li>

                          <li>
                            <span><i class="fal fa-check"></i>
                              @if ($package->number_of_room == 999999)
                                {{ __('Courts') }}
                              @elseif($package->number_of_room == 1)
                                {{ __('Court') }}
                              @else
                                {{ __('Courts') }}
                              @endif
                            </span>
                            <span>
                              @if ($package->number_of_room == 999999)
                                {{ __('(Unlimited)') }}
                              @else
                                ({{ $package->number_of_room }})
                              @endif
                            </span>
                          </li>

                          <li>
                            <span><i class="fal fa-check"></i>
                              @if ($package->number_of_amenities_per_hotel == 999999)
                                {{ __('Amenities Per Site') }}
                              @elseif($package->number_of_amenities_per_hotel == 1)
                                {{ __('Amenity Per Site') }}
                              @else
                                {{ __('Amenities Per Site') }}
                              @endif
                            </span>
                            <span>
                              @if ($package->number_of_amenities_per_hotel == 999999)
                                {{ __('(Unlimited)') }}
                              @else
                                ({{ $package->number_of_amenities_per_hotel }})
                              @endif
                            </span>
                          </li>

                          <li>
                            <span><i class="fal fa-check"></i>
                              @if ($package->number_of_amenities_per_room == 999999)
                                {{ __('Amenities Per Court') }}
                              @elseif($package->number_of_amenities_per_room == 1)
                                {{ __('Amenity Per Court') }}
                              @else
                                {{ __('Amenities Per Court') }}
                              @endif
                            </span>
                            <span>
                              @if ($package->number_of_amenities_per_room == 999999)
                                {{ __('(Unlimited)') }}
                              @else
                                ({{ $package->number_of_amenities_per_room }})
                              @endif
                            </span>
                          </li>

                          <li>
                            <span><i class="fal fa-check"></i>
                              @if ($package->number_of_images_per_hotel == 999999)
                                {{ __('Images Per Site') }}
                              @elseif($package->number_of_images_per_hotel == 1)
                                {{ __('Image Per Site') }}
                              @else
                                {{ __('Images Per Site') }}
                              @endif
                            </span>
                            <span>
                              @if ($package->number_of_images_per_hotel == 999999)
                                {{ __('(Unlimited)') }}
                              @else
                                ({{ $package->number_of_images_per_hotel }})
                              @endif
                            </span>
                          </li>

                          <li>
                            <span><i class="fal fa-check"></i>
                              @if ($package->number_of_images_per_room == 999999)
                                {{ __('Images Per Court') }}
                              @elseif($package->number_of_images_per_room == 1)
                                {{ __('Image Per Court') }}
                              @else
                                {{ __('Images Per Court') }}
                              @endif
                            </span>
                            <span>
                              @if ($package->number_of_images_per_room == 999999)
                                {{ __('(Unlimited)') }}
                              @else
                                ({{ $package->number_of_images_per_room }})
                              @endif
                            </span>
                          </li>

                          <li>
                            <span><i class="fal fa-check"></i>
                              @if ($package->number_of_bookings == 999999)
                                {{ __('Number of Bookings') }}
                              @elseif($package->number_of_bookings == 1)
                                {{ __('Number of Booking') }}
                              @else
                                {{ __('Number of Bookings') }}
                              @endif
                            </span>
                            <span>
                              @if ($package->number_of_bookings == 999999)
                                {{ __('(Unlimited)') }}
                              @else
                                ({{ $package->number_of_bookings }})
                              @endif
                            </span>
                          </li>

                          <li>
                            <span><i
                                class=" @if (is_array($permissions) && in_array('Add Booking From Dashboard', $permissions)) fal fa-check @else fal fa-times not-active @endif"></i>
                              {{ __('Add Booking From Dashboard') }}</span>
                          </li>
                          <li>
                            <span><i
                                class=" @if (is_array($permissions) && in_array('Edit Booking From Dashboard', $permissions)) fal fa-check @else fal fa-times not-active @endif"></i>
                              {{ __('Edit Booking From Dashboard') }}</span>
                          </li>
                          <li>
                            <span><i
                                class=" @if (is_array($permissions) && in_array('Support Tickets', $permissions)) fal fa-check @else fal fa-times not-active @endif"></i>
                              {{ __('Support Tickets') }}</span>
                          </li>
                          <li>
                            <span><i
                                class=" @if (is_array($permissions) && in_array('Live Court Display', $permissions)) fal fa-check @else fal fa-times not-active @endif"></i>
                              {{ __('Live Court Display') }}</span>
                          </li>
                          <li>
                            <span><i
                                class=" @if (is_array($permissions) && in_array('Open Play', $permissions)) fal fa-check @else fal fa-times not-active @endif"></i>
                              {{ __('Open Play') }}</span>
                          </li>


                          @if (!is_null($package->custom_features))
                            @php
                              $features = explode("\n", $package->custom_features);
                            @endphp
                            @if (count($features) > 0)
                              @foreach ($features as $key => $value)
                                <li><span><i class="fal fa-check"></i>{{ __($value) }}</span>
                                </li>
                              @endforeach
                            @endif
                          @endif
                        </ul>
                        <span class="show-more mt-15" data-toggle-btn="toggleListBtn">
                          {{ __('Show More') . '+' }}
                        </span>
                        <div class="card_action mt-25">
                          @auth('vendor')
                            <a href="{{ route('vendor.plan.extend.checkout', $package->id) }}"
                              class="btn btn-lg btn-primary radius-sm w-100" title="{{ __('Apply Now') }}"
                              target="_self">{{ __('Choose Plan') }}</a>
                          @endauth
                          @guest('vendor')
                            <a href="{{ route('vendor.login', ['redirectPath' => 'buy_plan', 'package' => $package->id]) }}"
                              class="btn btn-lg btn-primary radius-sm w-100" title="{{ __('Apply Now') }}"
                              target="_self">{{ __('Choose Plan') }}</a>
                          @endguest
                        </div>
                      </div>
                    </div>
                  @endforeach
                  @if ($packages->isEmpty())
                    <div class="col-12">
                      <div class="pricing-empty-state">{{ __('No packages are currently available for this payment term.') }}</div>
                    </div>
                  @endif
                </div>
              </div>
            @endforeach
          </div>
          <p class="pricing-note mt-20 mb-0"><i class="fal fa-info-circle mr-1"></i>{{ __('Billing term and final discounted total are confirmed securely during checkout.') }}</p>
        </div>
      </div>
    </div>
  </section>
@endsection
