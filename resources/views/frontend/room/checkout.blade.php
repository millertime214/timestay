@extends('frontend.layout')

@section('pageHeading')
  {{ !empty($pageHeading->room_checkout_page_title) ? $pageHeading->room_checkout_page_title : __('Checkout') }}
@endsection

@section('content')
  <!-- Breadcrumb start -->
  @includeIf('frontend.partials.breadcrumb', [
      'breadcrumb' => $bgImg->breadcrumb,
      'title' => !empty($pageHeading->room_checkout_page_title)
          ? $pageHeading->room_checkout_page_title
          : __('Checkout'),
  ])
  <!-- Breadcrumb end -->
  @php
    $none = 'none';
  @endphp
  <!-- Checkout-area start -->
  <div class="shopping-area pt-100 pb-60">
    <div class="container">
      <form id="payment-form" class="modal-form" action="{{ route('frontend.room.room_booking') }}" method="post"
        enctype="multipart/form-data">
        @csrf
        <div class="row gx-xl-5">
          <div class="col-lg-8">
            <div class="billing-details pb-40">
              <h5 class="mb-20">{{ __('Booking Details') }}</h5>
              <div class="row">
                <div class="col-lg-6">
                  <div class="form-group mb-20">
                    <label for="firstName">{{ __('Name') . '*' }}</label>
                    <input type="text" class="form-control" name="booking_name"
                      placeholder="{{ __('Enter Full Name') }}"
                      value="{{ !empty($authUser) ? $authUser->name : old('booking_name') }}">
                    @error('booking_name')
                      <p class="mt-2 text-danger">{{ $message }}</p>
                    @enderror
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="form-group mb-20">
                    <label for="">{{ __('Phone Number') . '*' }}</label>
                    <input id="" type="text" class="form-control" name="booking_phone"
                      placeholder="{{ __('Phone Number') }}"
                      value="{{ !empty($authUser) ? $authUser->phone : old('booking_phone') }}">
                    @error('booking_phone')
                      <p class="mt-2 text-danger">{{ $message }}</p>
                    @enderror
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="form-group mb-20">
                    <label for="">{{ __('Email Address') . '*' }}</label>
                    <input type="email" class="form-control" name="booking_email"
                      placeholder="{{ __('Email Address') }}"
                      value="{{ !empty($authUser) ? $authUser->email : old('booking_email') }}">
                    @error('booking_email')
                      <p class="mt-2 text-danger">{{ $message }}</p>
                    @enderror
                  </div>
                </div>

                <div class="col-lg-6">
                  <div class="form-group mb-20">
                    <label for="">{{ __('Address') . '*' }}</label>
                    <input type="text" class="form-control" name="booking_address" placeholder="{{ __('Address') }}"
                      value="{{ !empty($authUser) ? $authUser->address : old('booking_address') }}">
                    @error('booking_address')
                      <p class="mt-2 text-danger">{{ $message }}</p>
                    @enderror
                  </div>
                </div>
                <div class="col-lg-6 ">
                   <label for="">{{ __('Classification') . '*' }}</label> 
                  <div class="form-group mb-20">                 
                    @if ($openPlaySlot)
                      <input type="hidden" name="classification" value="open_play">
                      <input type="text" class="form-control" value="{{ __('Open Play') }}" readonly>
                    @else
                    <select name="classification" id="classification" class="form-control">                    
                      <option value="" selected disabled>{{ __('Select Classification') }}</option>
                      @foreach ($classifications as $key => $classification)
                        <option value="{{ $key }}" @selected(old('classification') == $key)>{{ __($classification) }}</option>
                      @endforeach
                      </select>
                    @endif
                              
                  </div>
                </div>   
              </div>
            </div>
            @if ($additionalServices)
              <div class="ship-details additional-service mb-10">
                <h5 class="mb-20">{{ __('Additional Service') }}</h5>
                <table class="shopping-table shadow-none shopping-table-style-1">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th>{{ __('Service') }}</th>
                      <th>{{ __('Charge') }}</th>
                    </tr>
                  </thead>
                  <tbody class="shopping-table-body">
                    @php
                      $takeServices = Session::get('takeService');
                      $takeService = explode(',', $takeServices);
                    @endphp
                    @foreach ($additionalServices as $id => $charge)
                      @if (in_array($id, $takeService))
                        @php
                          $serviceTitile = App\Models\AdditionalServiceContent::Where([
                              ['language_id', $language->id],
                              ['additional_service_id', $id],
                          ])->get();
                        @endphp
                        <tr>
                          <td>
                            <input type="checkbox" id="additional_service_{{ $id }}"
                              name="additional_service[]" value="{{ $id }}"
                              data-shipping_charge="{{ $charge }}" checked>
                          </td>
                          <td>

                            <label for="additional_service_{{ $id }}">{{ @$serviceTitile[0]->title }}</label>
                              <br>

                          </td>
                          <td>{{ symbolPrice(number_format($charge, 2)) }}</td>
                        </tr>
                      @else
                        @php
                          $serviceTitile = App\Models\AdditionalServiceContent::Where([
                              ['language_id', $language->id],
                              ['additional_service_id', $id],
                          ])->get();
                        @endphp
                        <tr>
                          <td>
                            <input type="checkbox" id="additional_service_{{ $id }}"
                              name="additional_service[]" value="{{ $id }}"
                              data-shipping_charge="{{ $charge }}">
                          </td>
                          <td>
                            <label for="additional_service_{{ $id }}">{{ @$serviceTitile[0]->title }}
                              <br>
                          </td>
                          <td>{{ symbolPrice(number_format($charge, 2)) }}</td>
                        </tr>
                      @endif
                    @endforeach
                  </tbody>
                </table>
              </div>
            @endif


          </div>
          <div class="col-lg-4">
            <div class="widget-area aos-init aos-animate">
              {{--  Booking Details  --}}
              <div class="widget widget-select border radius-md mb-30">
                <div class="p-20">
                  <h5 class="title">
                    {{ __('Booking Details') }}
                  </h5>
                </div>
                <ul class="list-group">
                  @php
                    $players = max(1, (int) $adult);
                    $roomPrice = $openPlaySlot
                        ? (object) ['price' => (float) $openPlaySlot->price_per_player * $players]
                        : App\Models\HourlyRoomPrice::find($price);
                    $basePrice = (float) $roomPrice->price;
                    $taxData = App\Models\BasicSettings\Basic::select('hotel_tax_amount')->first();
                  @endphp
                  <li>
                    <a href="{{ route('frontend.room.details', ['slug' => $room->slug, 'id' => $room->id]) }}">
                      <h4>{{ $room->title }}</h4>
                    </a>

                    <a href="{{ route('frontend.rooms', ['category' => $room->room_category_slug]) }}">
                      <h6>{{ $room->room_category }}</h6>
                    </a>
                    <ul class="list-unstyled">
                      <li>
                        <i class="fal fa-clock"></i>
                        <span>{{ $openPlaySlot ? $openPlaySlot->starts_at->floatDiffInHours($openPlaySlot->ends_at) : number_format($roomPrice->hour, 2) }} {{ __('Hr/s') }}</span>
                      </li>
                      <li>
                        <i class="fal fa-usd-circle"></i>
                        <span>{{ symbolPrice(number_format($openPlaySlot ? $openPlaySlot->price_per_player : number_format($roomPrice->price, 2), 2)) }}
                          @if ($openPlaySlot)
                           {{ __('per player') }} × {{ $players }} 
                           @endif
                        </span>
                      </li>
                    </ul>
                    <ul class="list-unstyled">
                      <li>
                        <i class="fal fa-calendar"></i>
                        <?php $dateObject = date_create($checkInDate);
                        ?>
                        <span>{{  date_format($dateObject, 'M d, Y') }} </span>
                      </li>
                      <li>
                        <i class="fal fa-business-time"></i>
                        <span>{{ \Carbon\Carbon::parse($checkInTime)->format('h:i A') }}</span>
                      </li>

                    </ul>
                  </li>
                </ul>
              </div>

              {{--  Payment Details  --}}
              <div id="couponReload">
                <div class="order-summary form-block border radius-md mb-30">
                  <h5 class="pb-15 mb-15 border-bottom">{{ __('Payment Details') }}</h5>
                  @php
                    $position = $currencyInfo->base_currency_symbol_position;
                    $symbol = $currencyInfo->base_currency_symbol;
                    $roomDiscount = Session::get('roomDiscount');
                    $serviceCharge = Session::get('serviceCharge');
                    $totalAmount = $basePrice + $serviceCharge;
                    $tax_amount = ($totalAmount - $roomDiscount) * ($taxData->hotel_tax_amount / 100);
                  @endphp

                  <ul class="service-charge-list">

                    <li class="d-flex justify-content-between">
                      <p class="mb-0 font-medium color-dark fw-medium">{{ $openPlaySlot ? __('Open Play Registration') : __('Court Rent') }} </p>
                      <p class="mb-0 price">₱ {{ number_format($roomPrice->price,2) }}</p>
                    </li>
                    <li class="d-flex justify-content-between">
                      <p class="mb-0 font-medium color-dark fw-medium">{{ __('Additional Service Charge') }} </p>
                      <p class="mb-0 price"><span class="operator color-green">+</span>{{ symbolPrice(number_format($serviceCharge, 2)  ) }}
                      </p>
                    </li>
                    <li class="d-flex justify-content-between">
                      <p class="mb-0 font-medium color-dark fw-medium ">{{ __('Discount') }}</p>
                      <p class="mb-0 price"><span
                          class="operator minus color-red">-</span>{{ symbolPrice(number_format($roomDiscount, 2)) }}</p>
                    </li>
                    <li class="d-flex justify-content-between">
                      <p class="mb-0 font-medium color-dark fw-medium">{{ __('Subtotal') }}</p>
                      <p class="mb-0 price">{{ symbolPrice(number_format($totalAmount - $roomDiscount, 2)) }}</p>
                    </li>

                    <li class="d-flex justify-content-between d-none">
                      <p class="mb-0 font-medium color-dark fw-medium">
                        {{ __('Tax') }}({{ $taxData->hotel_tax_amount }}%)
                      </p>
                      <p class="mb-0 price"><span class="operator color-green">+</span>{{ symbolPrice($tax_amount) }}</p>
                    </li>
                  </ul>
                  <hr>
                  <div class="total d-flex justify-content-between">
                    <h5>{{ __('Total') }}</h5>
                    <h5 class="mb-0 price">₱ {{ number_format($totalAmount - $roomDiscount,2) }}</h5>
                  </div>
                </div>
              </div>
              {{--  order-payment Coupon  --}}
              @unless ($openPlaySlot)
              <div class="order-payment mb-30">
                <div class="input-group radius-sm border">
                  <input type="text" class="form-control" id="coupon-code"
                    placeholder="{{ __('Enter Your Coupon') }}">
                  <button type="button" class="btn btn-lg btn-primary coupon-btn-padding radius-sm"
                    onclick="applyCoupon(event)">
                    {{ __('Apply') }}
                  </button>
                </div>
              </div>
              @endunless

              {{--  order-payment online  --}}
              <div class="order-payment form-block border radius-md mb-30">
                <h5 class="mb-20">{{ __('Payment Method') }}</h5>
                <div class="form-group mb-20">
                  <select name="gateway" id="gateway" class="select2 form-control payment-gateway">
                    <option value="" selected="" disabled>{{ __('Choose a Payment Method') }}</option>
                    <!-- @foreach ($onlineGateways as $onlineGateway)
                      <option @selected(old('gateway') == $onlineGateway->keyword) value="{{ $onlineGateway->keyword }}">
                        {{ __($onlineGateway->name) }}</option>
                    @endforeach

                    @if (count($offline_gateways) > 0)
                      @foreach ($offline_gateways as $offlineGateway)
                        <option @selected(old('gateway') == $offlineGateway->id) value="{{ $offlineGateway->id }}">
                          {{ __($offlineGateway->name) }}</option>
                      @endforeach
                    @endif -->

                    @if ($vendorEwallets->isNotEmpty())
                      <optgroup label="{{ __('Vendor E-wallets') }}">
                        @foreach ($vendorEwallets as $wallet)
                          <option @selected(old('gateway') == 'vendor_ewallet_' . $wallet->id)
                            value="vendor_ewallet_{{ $wallet->id }}">
                            {{ $wallet->name }} — {{ $wallet->account_number }}
                          </option>
                        @endforeach
                      </optgroup>
                    @endif

                  </select>
                  @if (Session::has('error'))
                    <p class="mt-2 text-danger">{{ Session::get('error') }}</p>
                  @endif
                </div>
                <div class="iyzico-element {{ old('gateway') == 'iyzico' ? '' : 'd-none' }}">
                  <div class="form-group mb-20">
                    <input type="text" name="identity_number" value="{{ old('identity_number') }}"
                      class="form-control" placeholder="Identity Number">
                    @error('identity_number')
                      <p class="text-danger">{{ $message }}</p>
                    @enderror
                  </div>
                  <div class="form-group mb-20">
                    <input type="text" name="zip_code" value="{{ old('zip_code') }}" class="form-control"
                      placeholder="Zip Code">
                    @error('zip_code')
                      <p class="text-danger">{{ $message }}</p>
                    @enderror
                  </div>
                </div>

                <div id="stripe-element" class="mb-2">
                  <!-- A Stripe Element will be inserted here. -->
                </div>
                <!-- Used to display form errors -->
                <div id="stripe-errors" role="alert"></div>

                {{-- START: Authorize.net Card Details Form --}}
                <div class="row gateway-details pt-3" id="tab-anet" style="display: {{ $none }};">
                  <div class="col-lg-6">
                    <div class="form-group mb-3">
                      <input class="form-control" type="text" id="anetCardNumber"
                        placeholder="{{ __('Card Number') }}" disabled />
                    </div>
                  </div>
                  <div class="col-lg-6 mb-3">
                    <div class="form-group">
                      <input class="form-control" type="text" id="anetExpMonth"
                        placeholder="{{ __('Expire Month') }}" disabled />
                    </div>
                  </div>
                  <div class="col-lg-6  mb-3 ">
                    <div class="form-group">
                      <input class="form-control" type="text" id="anetExpYear"
                        placeholder="{{ __('Expire Year') }}" disabled />
                    </div>
                  </div>
                  <div class="col-lg-6  mb-3">
                    <div class="form-group">
                      <input class="form-control" type="text" id="anetCardCode"
                        placeholder="{{ __('Card Code') }}" disabled />
                    </div>
                  </div>
                  <input type="hidden" name="opaqueDataValue" id="opaqueDataValue" disabled />
                  <input type="hidden" name="opaqueDataDescriptor" id="opaqueDataDescriptor" disabled />
                  <ul id="anetErrors" style="display:  {{ $none }};"></ul>
                </div>
                {{-- END: Authorize.net Card Details Form --}}

                @foreach ($offline_gateways as $offlineGateway)
                  <div class="@if ($errors->has('attachment') && request()->session()->get('gatewayId') == $offlineGateway->id) d-block @else d-none @endif offline-gateway-info"
                    id="{{ 'offline-gateway-' . $offlineGateway->id }}">
                    @if (!is_null($offlineGateway->short_description))
                      <div class="form-group mb-3">
                        <label>{{ __('Description') }}</label>
                        <p>{{ $offlineGateway->short_description }}</p>
                      </div>
                    @endif

                    @if (!is_null($offlineGateway->instructions))
                      <div class="form-group mb-3">
                        <label>{{ __('Instructions') }}</label>
                        {!! replaceBaseUrl($offlineGateway->instructions, 'summernote') !!}
                      </div>
                    @endif

                    @if ($offlineGateway->has_attachment == 1)
                      <div class="form-group mb-3">
                        <label>{{ __('Attachment') . '*' }}</label>
                        <br>
                        <input type="file" name="attachment">
                        @error('attachment')
                          <p class="text-danger">{{ $message }}</p>
                        @enderror
                      </div>
                    @endif

                  </div>
                @endforeach

                @foreach ($vendorEwallets as $wallet)
                  @php($walletSelected = old('gateway') == 'vendor_ewallet_' . $wallet->id)
                  <div class="vendor-ewallet-info {{ $walletSelected ? 'd-block' : 'd-none' }}"
                    id="vendor-ewallet-{{ $wallet->id }}">
                    <div class="alert alert-warning mb-3">
                      <div class="form-check">
                        <input class="form-check-input ewallet-input" type="checkbox"
                          name="vendor_payment_terms_accepted" value="1"
                          id="vendor-payment-terms-{{ $wallet->id }}"
                          @checked($walletSelected && old('vendor_payment_terms_accepted'))
                          {{ $walletSelected ? '' : 'disabled' }} required>
                        <label class="form-check-label" for="vendor-payment-terms-{{ $wallet->id }}">
                          {{ __('By paying the vendor directly, you agree to our') }}
                          <a href="{{ route('terms_and_conditions') }}" target="_blank" rel="noopener">
                            {{ __('Terms and Conditions') }}
                          </a>.
                          {{ $websiteInfo->website_title }}
                          {{ __('is not liable for payment disputes or scams.') }}
                        </label>
                      </div>
                      @if ($walletSelected)
                        @error('vendor_payment_terms_accepted')
                          <p class="text-danger mb-0 mt-2">{{ $message }}</p>
                        @enderror
                      @endif
                    </div>
                    <div class="form-group mb-3 text-center">
                      <label class="d-block">{{ __('Scan to Pay') }} — {{ $wallet->name }}</label>
                      <img class="img-fluid border radius-sm p-2 my-2"
                        style="max-height: 280px"
                        src="{{ asset('assets/admin/img/vendor-ewallet-qr/' . $wallet->qr_code) }}"
                        alt="{{ $wallet->name }} QR Code">
                      <p class="mb-0"><strong>{{ __('Account Number') }}:</strong> {{ $wallet->account_number }}</p>
                    </div>
                    <div class="form-group mb-3">
                      <label>{{ __('Transaction ID') }} *</label>
                      <input class="form-control ewallet-input" name="payment_transaction_id"
                        value="{{ $walletSelected ? old('payment_transaction_id') : '' }}"
                        maxlength="255" {{ $walletSelected ? '' : 'disabled' }}>
                      @if ($walletSelected) @error('payment_transaction_id')<p class="text-danger">{{ $message }}</p>@enderror @endif
                    </div>
                    <div class="form-group mb-3">
                      <label>{{ __('Payer Phone Number') }} *</label>
                      <input class="form-control ewallet-input" name="payer_phone"
                        value="{{ $walletSelected ? old('payer_phone') : '' }}"
                        maxlength="50" {{ $walletSelected ? '' : 'disabled' }}>
                      @if ($walletSelected) 
                      @error('payer_phone')<p class="text-danger">{{ $message }}</p>
                      @enderror 
                      @endif
                    </div>
                    <p class="text-muted">{{ __('Your booking will remain pending until the vendor verifies the payment.') }}</p>
                  </div>
                @endforeach

                <div class="text-center">
                  <button class="btn btn-lg btn-primary radius-sm w-100" type="submit">{{ __('Book Now') }}
                  </button>
                </div>
              </div>

            </div>

          </div>

        </div>
      </form>
    </div>
  </div>
  <!-- Checkout-area end -->
@endsection
@section('script')
  <script src="https://js.stripe.com/v3/"></script>
  <script src="{{ $anetSource }}"></script>
  <script>
    let stripe_key = "{{ $stripe_key }}";
    let public_key = "{{ $anetClientKey }}";
    let login_id = "{{ $anetLoginId }}";
  </script>
  <script src="{{ asset('assets/front/js/room-checkout.js') }}"></script>
  <script>
    @if (old('gateway') == 'autorize.net')
      $(document).ready(function() {
        $('#stripe-element').removeClass('d-none');
      });
    @endif
  </script>
@endsection
