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
      background: linear-gradient(180deg, #f7fbff 0%, #ffffff 100%);
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

    .frontend-package-card {
      height: calc(100% - 30px);
      background: #fff;
      box-shadow: 0 18px 45px rgba(16, 24, 40, .08);
      transition: transform .25s ease, box-shadow .25s ease;
    }

    .frontend-package-card:hover {
      transform: translateY(-6px);
      box-shadow: 0 24px 55px rgba(16, 24, 40, .13);
    }

    .frontend-package-card.active {
      border-width: 2px !important;
      box-shadow: 0 24px 60px rgba(47, 111, 237, .16);
    }

    .frontend-package-card .card_subtitle h4 {
      font-size: 30px;
      font-weight: 800;
    }

    .frontend-package-card .card_list li {
      gap: 15px;
      padding: 10px 0;
      border-bottom: 1px solid #eef2f6;
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
          <div class="section-title title-center mb-50" data-aos="fade-up">
            <span class="pricing-kicker">{{ __('Vendor Packages') }}</span>
            <h2 class="title mb-15">{{ __('Choose the right plan for your pickleball business') }}</h2>
            <p class="pricing-intro mb-30">{{ __('Publish your sites and courts, manage reservations, and unlock vendor tools based on your selected package.') }}</p>
            <div class="tabs-navigation tabs-navigation_v3">
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
          </div>
          <div class="tab-content" data-aos="fade-up">

            @foreach ($terms as $term)
              <div class="tab-pane slide {{ $loop->first ? 'active show' : '' }}" id="{{ strtolower($term) }}">
                <div class="row justify-content-center">
                  @php
                    $packages = \App\Models\Package::where('status', '1')->get();
                  @endphp
                  @foreach ($packages as $package)
                    @php
                      $permissions = $package->features;
                      if (!empty($package->features)) {
                          $permissions = json_decode($permissions, true);
                      }
                    @endphp
                    <div class="col-md-6 col-lg-4 item">
                      <div class="card frontend-package-card p-30 mb-30 radius-lg border {{ $package->recommended ? 'active' : '' }}">
                        <div class="card_top">
                          <div class="card_icon">
                            <i class="{{ $package->icon }}"></i>
                          </div>
                          <div class="pricing_card_header d-flex flex-column">
                            <h3 class="card_title mb-0">{{ __($package->title) }}</h3>
                            @if ($package->recommended == '1')
                              <span>{{ __('Popular') }}</span>
                            @endif
                          </div>
                        </div>
                        <div class="card_subtitle mt-15">
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
                          @foreach ($termDiscounts as $termKey => [$discountTerm, $discount])
                            @if ((float) $package->price > 0 && $package->isBillingTermEnabled($termKey) && (float) $discount > 0)
                              <span class="badge badge-success mt-2 mr-1">{{ $discountTerm }}: {{ rtrim(rtrim(number_format($discount, 2), '0'), '.') }}% {{ __('off') }}</span>
                            @endif
                          @endforeach
                        </div>
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
                              target="_self">{{ __('Purchase') }}</a>
                          @endauth
                          @guest('vendor')
                            <a href="{{ route('vendor.login', ['redirectPath' => 'buy_plan', 'package' => $package->id]) }}"
                              class="btn btn-lg btn-primary radius-sm w-100" title="{{ __('Apply Now') }}"
                              target="_self">{{ __('Purchase') }}</a>
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
        </div>
      </div>
    </div>
  </section>
@endsection
