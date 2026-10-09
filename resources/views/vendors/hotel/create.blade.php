@extends('vendors.layout')

@section('content')
  <div class="page-header">
    <h4 class="page-title">{{ __('Add Site') }}</h4>
    <ul class="breadcrumbs">
      <li class="nav-home">
        <a href="{{ route('vendor.dashboard') }}">
          <i class="flaticon-home"></i>
        </a>
      </li>
      <li class="separator">
        <i class="flaticon-right-arrow"></i>
      </li>
      <li class="nav-item">
        <a href="#">{{ __('Venue Management') }}</a>
      </li>
      <li class="separator">
        <i class="flaticon-right-arrow"></i>
      </li>
      <li class="nav-item">
        <a href="#">{{ __('Add Site') }}</a>
      </li>
    </ul>
  </div>

  @php
    $vendorId = Auth::guard('vendor')->user()->id;

    if ($vendorId) {
        $current_package = App\Http\Helpers\VendorPermissionHelper::packagePermission($vendorId);
        $hasPackage = $current_package instanceof App\Models\Package;

        if ($hasPackage) {
            $numberoffImages = $current_package->number_of_images_per_hotel;
        } else {
            $numberoffImages = 10;
        }
    }
  @endphp


  <div class="row">
    <div class="col-md-12">
      @if ($hasPackage)
        @if (vendorTotalAddedHotel($vendorId) >= $current_package->number_of_hotel)
          <div class="alert alert-warning">
            {{ __("You can't add more Hotel. Please buy/extend a plan to add Hotel") }}
          </div>
          @php
            $can_hotel_add = 2;
          @endphp
        @else
          @php
            $can_hotel_add = 1;
          @endphp
        @endif
      @else
        @if (vendorTotalAddedHotel($vendorId) >= 1)
          <div class="alert alert-warning">
            {{ __('The no-plan Site allowance has been used. Purchase a plan to add more Sites.') }}
          </div>
          @php
            $can_hotel_add = 2;
          @endphp
        @else
          <div class="alert alert-info">
            {{ __('You can add one Site without a plan. A plan is required to add additional Sites and use package features.') }}
          </div>
          @php
            $can_hotel_add = 1;
          @endphp
        @endif
      @endif
      <div class="card">
        <div class="card-header">
          <div class="card-title d-inline-block">{{ __('Add Hotel') }}</div>
        </div>
        <div class="card-body">

          <div class="row">
            <div class="col-lg-10 offset-lg-1">


              <div class="alert alert-danger pb-1 dis-none" id="hotelErrors">
                <ul></ul>
              </div>
              <div class="col-lg-12">
                <label for="" class="mb-2"><strong>{{ __('Gallery Images') . '*' }}</strong></label>
                <form action="{{ route('vendor.hotel_management.hotel.imagesstore') }}" id="my-dropzone"
                  enctype="multipart/formdata" class="dropzone create">
                  @csrf
                  <div class="fallback">
                    <input name="file" type="file" multiple />
                  </div>
                </form>
                <p class="em text-danger mb-0" id="errslider_images"></p>
                @if ($hasPackage)
                  @if (vendorTotalAddedHotel($vendorId) <= $current_package->number_of_hotel)
                    <p class="text-warning">
                      {{ __('You can upload maximum') }}{{ __(' ') }}
                      {{ $current_package->number_of_images_per_hotel }}{{ __(' ') }}{{ __('images under one hotel') }}
                    </p>
                  @endif
                @endif
              </div>

              <form id="hotelForm" action="{{ route('vendor.hotel_management.store_hotel') }}" method="POST"
                enctype="multipart/form-data">
                @csrf
                <div class="row">

                  <div class="col-lg-4">
                    <div class="form-group">
                      <label for="">{{ __('Hotel Logo') . '*' }}</label>
                      <br>
                      <div class="thumb-preview">
                        <img src="{{ asset('assets/img/noimage.jpg') }}" alt="..." class="uploaded-img2">
                      </div>

                      <div class="mt-3">
                        <div role="button" class="btn btn-primary btn-sm upload-btn">
                          {{ __('Choose Image') }}
                          <input type="file" class="img-input2" name="logo">
                        </div>
                      </div>
                      <p class="mt-2 mb-0 text-warning">{{ __('Image dimensions: minimum 100x100 pixels, maximum 350x350 pixels.') }}</p>
                    </div>
                  </div>
                  <div class="col-lg-4">
                    <div class="form-group">
                      <label>{{ __('Status') . '*' }} </label>
                      <select name="status" id="status" class="form-control">
                        <option value="1" disabled>{{ __('Active') }} ({{ __('Requires verification') }})</option>
                        <option selected value="0">{{ __('Deactive') }} </option>
                      </select>
                    </div>
                  </div>
                  <div class="col-lg-4">
                    <div class="form-group">
                      <label>{{ __('Stars') . '*' }} </label>
                      <select name="stars" class="form-control">
                        <option selected disabled>{{ __('Select a star') }}</option>
                        <option value="1">{{ __('1 ★') }}</option>
                        <option value="2">{{ __('2 ★★') }}</option>
                        <option value="3">{{ __('3 ★★★') }}</option>
                        <option value="4">{{ __('4 ★★★★') }}</option>
                        <option value="5">{{ __('5 ★★★★★') }}</option>
                      </select>
                    </div>
                  </div>
                  @if ($settings->google_map_api_key_status == 0)
                    <div class="col-lg-6">
                      <div class="form-group">
                        <label>{{ __('Latitude' . '*') }}</label>
                        <input type="text" class="form-control" id="latitude" name="latitude"
                          placeholder="{{ __('Enter Latitude') }}">
                      </div>
                      <p class="text-warning pl-10">
                        {{ __('The Latitude must be between -90 to 90. Ex:49.43453') }}
                      </p>
                    </div>

                    <div class="col-lg-6">
                      <div class="form-group">
                        <label>{{ __('Longitude' . '*') }}</label>
                        <input type="text" id="longitude" class="form-control" name="longitude"
                          placeholder="{{ __('Enter Longitude') }}">
                      </div>
                      <p class="text-warning pl-10">
                        {{ __('The Longitude must be between -180 to 180. Ex:149.91553') }}
                      </p>
                    </div>
                  @endif

                  <input type="hidden" name="vendor_id" id=""value="{{ $vendorId }}">
                  <input type="hidden" name="can_hotel_add" value="{{ $can_hotel_add }}">
                </div>

                <div class="card border mt-3 mb-4">
                  <div class="card-header bg-light"><strong>{{ __('Site Verification Documents') }}</strong></div>
                  <div class="card-body">
                    <p class="text-muted">{{ __('You may save the Site now and upload the documents later. The Site cannot be activated or accept frontend bookings until all documents are uploaded and approved. Accepted formats: PDF, JPG, JPEG, or PNG. Maximum 10 MB per document.') }}</p>
                    <div class="row">
                      <div class="col-lg-4"><div class="form-group"><label>{{ __('Business Permit') }} <small>({{ __('Optional for now') }})</small></label><input type="file" name="business_permit_document" class="form-control" accept=".pdf,.jpg,.jpeg,.png"><p id="errbusiness_permit_document" class="mb-0 text-danger em"></p></div></div>
                      <div class="col-lg-4"><div class="form-group"><label>{{ __('DTI/SEC Registration') }} <small>({{ __('Optional for now') }})</small></label><input type="file" name="business_registration_document" class="form-control" accept=".pdf,.jpg,.jpeg,.png"><p id="errbusiness_registration_document" class="mb-0 text-danger em"></p></div></div>
                      <div class="col-lg-4"><div class="form-group"><label>{{ __("Mayor's Permit") }} <small>({{ __('Optional for now') }})</small></label><input type="file" name="mayors_permit_document" class="form-control" accept=".pdf,.jpg,.jpeg,.png"><p id="errmayors_permit_document" class="mb-0 text-danger em"></p></div></div>
                    </div>
                  </div>
                </div>

                <div id="accordion" class="mt-3">
                  @foreach ($languages as $language)
                    <div class="version">
                      <div class="version-header" id="heading{{ $language->id }}">
                        <h5 class="mb-0">
                          <button type="button" class="btn btn-link" data-toggle="collapse"
                            data-target="#collapse{{ $language->id }}"
                            aria-expanded="{{ $language->is_default == 1 ? 'true' : 'false' }}"
                            aria-controls="collapse{{ $language->id }}">
                            {{ $language->name . __(' Language') }}
                            {{ $language->is_default == 1 ? '(Default)' : '' }}
                          </button>
                        </h5>
                      </div>

                      <div id="collapse{{ $language->id }}"
                        class="collapse {{ $language->is_default == 1 ? 'show' : '' }}"
                        aria-labelledby="heading{{ $language->id }}" data-parent="#accordion">
                        <div class="version-body {{ $language->direction == 1 ? 'rtl text-right' : '' }}">
                          <div class="row">
                            <div class="col-lg-6">
                              <div class="form-group {{ $language->direction == 1 ? 'rtl text-right' : '' }}">
                                <label>{{ __('Title') . '*' }}</label>
                                <input type="text" class="form-control" name="{{ $language->code }}_title"
                                  placeholder="{{ __('Enter Title') }}">
                              </div>
                            </div>
                            <div class="col-lg-6">
                              <div class="form-group {{ $language->direction == 1 ? 'rtl text-right' : '' }}">
                                @php
                                  $categories = App\Models\HotelCategory::where('language_id', $language->id)
                                      ->where('status', 1)
                                      ->orderBy('serial_number', 'asc')
                                      ->get();
                                @endphp

                                <label>{{ __('Category') . '*' }} </label>
                                <select name="{{ $language->code }}_category_id"
                                  class="form-control js-example-basic-single2">
                                  <option selected disabled>{{ __('Select a Category') }}</option>

                                  @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                  @endforeach
                                </select>
                              </div>
                            </div>
                            @php
                              $Countries = App\Models\Location\Country::where('language_id', $language->id)->get();
                              $totalCountry = $Countries->count();
                            @endphp

                            @if ($totalCountry > 0)
                              <div class="col-lg-4">
                                <div class="form-group {{ $language->direction == 1 ? 'rtl text-right' : '' }}">

                                  <label>{{ __('Country') . '*' }}</label>
                                  <select name="{{ $language->code }}_country_id"
                                    class="form-control js-example-basic-single3" data-code="{{ $language->code }}">
                                    <option selected disabled>{{ __('Select Country') }}</option>
                                    @foreach ($Countries as $Country)
                                      <option value="{{ $Country->id }}" @if($Country->id  == 63) selected @endif>{{ $Country->name }}</option>
                                    @endforeach
                                  </select>
                                </div>
                              </div>
                            @endif

                            @php
                              // Keep the state control available for every country in this language.
                              // The selected country's states are loaded by admin-hotel.js.
                              $States = App\Models\Location\State::where('language_id', $language->id)->get();
                              $totalState = $States->count();
                            @endphp

                            @if ($totalState > 0)
                              <div class="col-lg-4 {{ $language->code }}_hide_state">
                                <div class="form-group {{ $language->direction == 1 ? 'rtl text-right' : '' }}">

                                  <label>{{ __('State') . '*' }} </label>
                                  <select name="{{ $language->code }}_state_id"
                                    class="form-control js-example-basic-single4 {{ $language->code }}_country_state_id"data-code="{{ $language->code }}">
                                    <option selected disabled>{{ __('Select State') }}</option>

                                    @foreach ($States as $State)
                                      <option value="{{ $State->id }}">{{ $State->name }}</option>
                                    @endforeach
                                  </select>
                                </div>
                              </div>
                            @endif

                            @php
                              $cities = App\Models\Location\City::where('language_id', $language->id)->get();
                            @endphp

                            <div class="col-lg-4">
                              <div class="form-group {{ $language->direction == 1 ? 'rtl text-right' : '' }}">

                                <label>{{ __('City') . '*' }}</label>
                                <select name="{{ $language->code }}_city_id"
                                  class="form-control js-example-basic-single5 {{ $language->code }}_state_city_id">
                                  <option selected disabled>{{ __('Select a City') }}</option>
                                  @foreach ($cities as $City)
                                    <option value="{{ $City->id }}">{{ $City->name }}</option>
                                  @endforeach
                                </select>
                              </div>
                            </div>

                            <div class="col-lg-12">
                              <div class="form-group">
                                <label>{{ __('Address') . '*' }}</label>
                                <input type="text" class="form-control"
                                  value="{{ old($language->code . '_address') }}" name="{{ $language->code }}_address"
                                  placeholder="{{ __('Enter Address') }}" id="search-address">
                                @if ($language->is_default == 1 && $settings->google_map_api_key_status == 1)
                                  <a href="" class="btn btn-secondary mt-2 btn-sm" data-toggle="modal"
                                    data-target="#GoogleMapModal">
                                    <i class="fas fa-eye"></i> {{ __('Show Map') }}
                                  </a>
                                @endif
                              </div>
                            </div>

                            @if ($language->is_default == 1 && $settings->google_map_api_key_status == 1)
                              <div class="col-lg-6">
                                <div class="form-group">
                                  <label>{{ __('Latitude' . '*') }}</label>
                                  <input type="text" class="form-control" id="latitude" name="latitude"
                                    placeholder="{{ __('Enter Latitude') }}">
                                </div>
                                <p class="text-warning pl-10">
                                  {{ __('The Latitude must be between -90 to 90. Ex:49.43453') }}
                                </p>
                              </div>

                              <div class="col-lg-6">
                                <div class="form-group">
                                  <label>{{ __('Longitude' . '*') }}</label>
                                  <input type="text" id="longitude" class="form-control" name="longitude"
                                    placeholder="{{ __('Enter Longitude') }}">
                                </div>
                                <p class="text-warning pl-10">
                                  {{ __('The Longitude must be between -180 to 180. Ex:149.91553') }}
                                </p>
                              </div>
                            @endif
                            @php
                              $aminities = App\Models\Amenitie::where('language_id', $language->id)->get();
                            @endphp
                            @if (count($aminities) > 0)
                              <div class="col-lg-12">
                                <div class="form-group {{ $language->direction == 1 ? 'rtl text-right' : '' }}">


                                  <label>{{ __('Select Amenities') . '*' }} </label>
                                  <div class="dropdown-content" id="checkboxes">
                                    @foreach ($aminities as $amenity)
                                      <input type="checkbox" id="{{ $amenity->id }}"
                                        name="{{ $language->code }}_aminities[]" value="{{ $amenity->id }}">
                                      <label
                                        class="amenities-label {{ $language->direction == 1 ? 'ml-2 mr-0' : 'mr-2' }}"
                                        for="{{ $amenity->id }}">{{ $amenity->title }}</label>
                                    @endforeach
                                  </div>
                                </div>
                              </div>
                            @endif
                          </div>

                          <div class="row">
                            <div class="col-lg-12">
                              <div class="form-group {{ $language->direction == 1 ? 'rtl text-right' : '' }}">
                                <label>{{ __('Description') . '*' }}</label>
                                <textarea id="{{ $language->code }}_description" class="form-control summernote"
                                  name="{{ $language->code }}_description" data-height="300"></textarea>
                              </div>
                            </div>
                          </div>

                          <div class="row">
                            <div class="col-lg-12">
                              <div class="form-group {{ $language->direction == 1 ? 'rtl text-right' : '' }}">
                                <label>{{ __('Meta Keywords') }}</label>
                                <input class="form-control" name="{{ $language->code }}_meta_keyword"
                                  placeholder="{{ __('Enter Meta Keywords') }}" data-role="tagsinput">
                              </div>
                            </div>
                          </div>

                          <div class="row">
                            <div class="col-lg-12">
                              <div class="form-group {{ $language->direction == 1 ? 'rtl text-right' : '' }}">
                                <label>{{ __('Meta Description') }}</label>
                                <textarea class="form-control" name="{{ $language->code }}_meta_description" rows="5"
                                  placeholder="{{ __('Enter Meta Description') }}"></textarea>
                              </div>
                            </div>
                          </div>

                          <div class="row">
                            <div class="col">
                              @php $currLang = $language; @endphp

                              @foreach ($languages as $language)
                                @continue($language->id == $currLang->id)

                                <div class="form-check py-0">
                                  <label class="form-check-label">
                                    <input class="form-check-input" type="checkbox"
                                      onchange="cloneInput('collapse{{ $currLang->id }}', 'collapse{{ $language->id }}', event)">
                                    <span class="form-check-sign">{{ __('Clone for') }} <strong
                                        class="text-capitalize text-secondary">{{ $language->name }}</strong>
                                      {{ __('language') }}</span>
                                  </label>
                                </div>
                              @endforeach
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  @endforeach
                </div>

                <div id="sliders">
                </div>
                <div class="alert alert-light border mt-4">
                  <div class="form-check-inline">
                    <input class="form-check-input form-check-inline" type="checkbox" name="site_agreement_accepted" value="1" id="site-agreement-accepted" required>
                    <label class="form-check-label" for="site-agreement-accepted">
                      {{ __('I confirm that I am authorized to list this Site and agree to the') }}
                      <a href="{{ route('vendor_site_agreement') }}" target="_blank" rel="noopener"><strong>{{ __('Vendor Site Agreement') }}</strong></a>.
                    </label>
                  </div>
                  <p id="errsite_agreement_accepted" class="mb-0 mt-2 text-danger em"></p>
                </div>
              </form>
            </div>
          </div>
        </div>

        <div class="card-footer">
          <div class="row">
            <div class="col-12 text-center">
              <button type="submit" form="hotelForm" data-can_hotel_add="{{ $can_hotel_add }}"
                class="btn btn-success">
                {{ __('Save') }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Google map modal --}}
  <!-- Modal -->
  <div class="modal fade" id="GoogleMapModal" tabindex="-1" role="dialog"
    aria-labelledby="GoogleMapModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="GoogleMapModalLongTitle">Google Map</h5>
          <div>
            <button type="button" class="btn btn-secondary btn-xs" data-dismiss="modal">Choose</button>
            <button type="button" class="btn btn-danger btn-xs" data-dismiss="modal">X</button>
          </div>
        </div>
        <div class="modal-body">
          <div id="map"></div>
        </div>
      </div>
    </div>
  </div>
@endsection

@section('script')
  @if ($settings->google_map_api_key_status == 1)
    <script
      src="https://maps.googleapis.com/maps/api/js?key={{ $settings->google_map_api_key }}&libraries=places&callback=initMap"
      async defer></script>
    <script src="{{ asset('assets/admin/js/map-init2.js') }}"></script>
  @endif

  <script>
    'use strict';

    var storeUrl = "{{ route('vendor.hotel_management.hotel.imagesstore') }}";
    var removeUrl = "{{ route('vendor.hotel_management.hotel.imagermv') }}";
    var getStateUrl = "{{ route('vendor.hotel_management.get-state') }}";
    var getCityUrl = "{{ route('vendor.hotel_management.get-city') }}";
    var galleryImages = {{ $numberoffImages }};
    var languages = {!! json_encode($languages) !!};
  </script>

  <script type="text/javascript" src="{{ asset('assets/admin/js/admin-hotel.js') }}"></script>
  <script type="text/javascript" src="{{ asset('assets/admin/js/admin-dropzone.js') }}"></script>
@endsection
