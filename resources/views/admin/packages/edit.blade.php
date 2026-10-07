@extends('admin.layout')

@section('content')
  <div class="page-header">
    <h4 class="page-title">{{ __('Edit package') }}</h4>
    <ul class="breadcrumbs">
      <li class="nav-home">
        <a href="{{ route('admin.dashboard') }}">
          <i class="flaticon-home"></i>
        </a>
      </li>
      <li class="separator">
        <i class="flaticon-right-arrow"></i>
      </li>
      <li class="nav-item">
        <a href="#">{{ __('Packages') }}</a>
      </li>
      <li class="separator">
        <i class="flaticon-right-arrow"></i>
      </li>
      <li class="nav-item">
        <a href="#">{{ __('Edit package') }}</a>
      </li>
    </ul>
  </div>
  <div class="row">
    <div class="col-md-12">
      <div class="card">
        <div class="card-header">
          <div class="card-title d-inline-block">{{ __('Edit package') }}</div>
          <a class="btn btn-info btn-sm float-right d-inline-block" href="{{ route('admin.package.index') }}">
            <span class="btn-label">
              <i class="fas fa-backward"></i>
            </span>
            {{ __('Back') }}
          </a>
        </div>
        <div class="card-body pt-5 pb-5">
          <div class="row">
            <div class="col-lg-6 offset-lg-3">
              <form id="ajaxForm" class="" action="{{ route('admin.package.update') }}" method="post"
                enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="package_id" value="{{ $package->id }}">
                <div class="form-group">
                  <label for="title">{{ __('Package title') . '*' }}</label>
                  <input id="title" type="text" class="form-control" name="title" value="{{ $package->title }}"
                    placeholder="{{ __('Enter name') }}">
                  <p id="err_title" class="mt-2 mb-0 text-danger em"></p>
                </div>

                <div class="form-group">
                  <label for="price">{{ __('Price') }} ({{ $settings->base_currency_text }})*</label>
                  <input id="price" type="number" class="form-control" name="price"
                    placeholder="{{ __('Enter Package price') }}" value="{{ $package->price }}">
                  <p class="text-warning">
                    <small>{{ __('If price is 0 , than it will appear as free') }}</small>
                  </p>
                  <p id="err_price" class="mt-2 mb-0 text-danger em"></p>
                </div>

                <div class="form-group">
                  <label for="">{{ __('Icon') . '*' }}</label>
                  <div class="btn-group d-block">
                    <button type="button" class="btn btn-primary iconpicker-component">
                      <i class="{{ $package->icon }}"></i>
                    </button>
                    <button type="button" class="icp icp-dd btn btn-primary dropdown-toggle" data-selected="fa-car"
                      data-toggle="dropdown"></button>
                    <div class="dropdown-menu"></div>
                  </div>
                  <input type="hidden" id="inputIcon" name="icon">
                </div>

                <input type="hidden" name="term" value="monthly">
                <div class="alert alert-info">{{ __('The package price is monthly. Vendors choose monthly, semi-annual, or annual billing during purchase.') }}</div>
                <div class="form-group">
                  <label class="form-label">{{ __('Payment Term Discounts') }} (%)</label>
                  <div class="row">
                    <div class="col-md-4"><label class="d-block"><input type="checkbox" name="payment_terms[]" value="monthly" @checked($package->monthly_term_active)> {{ __('Monthly Active') }}</label><input type="number" name="monthly_discount" class="form-control" min="0" max="100" step="0.01" value="{{ $package->monthly_discount ?? 0 }}"><p id="err_monthly_discount" class="mb-0 text-danger em"></p></div>
                    <div class="col-md-4"><label class="d-block"><input type="checkbox" name="payment_terms[]" value="semi-annual" @checked($package->semi_annual_term_active)> {{ __('Semi-Annual Active') }}</label><input type="number" name="semi_annual_discount" class="form-control" min="0" max="100" step="0.01" value="{{ $package->semi_annual_discount ?? 0 }}"><p id="err_semi_annual_discount" class="mb-0 text-danger em"></p></div>
                    <div class="col-md-4"><label class="d-block"><input type="checkbox" name="payment_terms[]" value="yearly" @checked($package->annual_term_active)> {{ __('Annual Active') }}</label><input type="number" name="annual_discount" class="form-control" min="0" max="100" step="0.01" value="{{ $package->annual_discount ?? 0 }}"><p id="err_annual_discount" class="mb-0 text-danger em"></p></div>
                  </div>
                  <p id="err_payment_terms" class="mb-0 text-danger em"></p>
                  <small class="text-muted">{{ __('Use 0 for payment terms without a discount.') }}</small>
                </div>


                @php
                  $permissions = $package->features;
                  if (!empty($package->features)) {
                      $permissions = json_decode($permissions, true);
                  }
                @endphp

                <div class="form-group">
                  <label class="form-label">{{ __('Package Features') }}</label>
                  <div class="selectgroup selectgroup-pills">

                    <label class="selectgroup-item">
                      <input type="checkbox" name="features[]" value="Add Booking From Dashboard"
                        class="selectgroup-input"@if (is_array($permissions) && in_array('Add Booking From Dashboard', $permissions)) checked @endif>
                      <span class="selectgroup-button">{{ __('Add Booking From Dashboard') }}</span>
                    </label>
                    <label class="selectgroup-item">
                      <input type="checkbox" name="features[]" value="Edit Booking From Dashboard"
                        class="selectgroup-input"@if (is_array($permissions) && in_array('Edit Booking From Dashboard', $permissions)) checked @endif>
                      <span class="selectgroup-button">{{ __('Edit Booking From Dashboard') }}</span>
                    </label>

                    <label class="selectgroup-item">
                      <input type="checkbox" name="features[]" value="Support Tickets"
                        class="selectgroup-input"@if (is_array($permissions) && in_array('Support Tickets', $permissions)) checked @endif>
                      <span class="selectgroup-button">{{ __('Support Tickets') }}</span>
                    </label>
                    <label class="selectgroup-item">
                      <input type="checkbox" name="features[]" value="Live Court Display"
                        class="selectgroup-input"@if (is_array($permissions) && in_array('Live Court Display', $permissions)) checked @endif>
                      <span class="selectgroup-button">{{ __('Live Court Display') }}</span>
                    </label>
                    <label class="selectgroup-item">
                      <input type="checkbox" name="features[]" value="Open Play"
                        class="selectgroup-input"@if (is_array($permissions) && in_array('Open Play', $permissions)) checked @endif>
                      <span class="selectgroup-button">{{ __('Open Play') }}</span>
                    </label>
                  </div>
                  <p id="err_features" class="mb-0 text-danger em"></p>
                </div>


                <div class="row">
                  <div class="col-lg-6">
                    <div class="form-group">
                      <label class="form-label">{{ __('Number of Hotels') . '*' }}</label>
                      <input type="number" class="form-control" name="number_of_hotel"
                        placeholder="{{ __('Enter Number of Hotels') }}"value="{{ $package->number_of_hotel }}">
                      <p class="text-warning">{{ __('Enter 999999 , then it will appear as unlimited') }}</p>
                      <p id="err_number_of_hotel" class="mb-0 text-danger em"></p>
                    </div>
                  </div>
                  <div class="col-lg-6">
                    <div class="form-group">
                      <label class="form-label">{{ __('Number of images per Hotel') . '*' }}</label>
                      <input type="number" class="form-control" name="number_of_images_per_hotel"
                        placeholder="{{ __('Enter Number of images per Hotel') }}"value="{{ $package->number_of_images_per_hotel }}">
                      <p class="text-warning">{{ __('Enter 999999 , then it will appear as unlimited') }}</p>
                      <p id="err_number_of_images_per_hotel" class="mb-0 text-danger em"></p>
                    </div>
                  </div>
                  <div class="col-lg-6">
                    <div class="form-group">
                      <label for="">{{ __('Number of Amenities Per Hotel') . '*' }} </label>
                      <input type="number" class="form-control" name="number_of_amenities_per_hotel"
                        placeholder="{{ __('Enter Number of Amenities Per Hotel') }}"value="{{ $package->number_of_amenities_per_hotel }}">
                      <p class="text-warning">{{ __('Enter 999999 , then it will appear as unlimited') }}</p>
                      <p id="err_number_of_amenities_per_hotel" class="mb-0 text-danger em"></p>
                    </div>
                  </div>
                  <div class="col-lg-6">
                    <div class="form-group">
                      <label class="form-label">{{ __('Number of Rooms') . '*' }}</label>
                      <input type="number" class="form-control" name="number_of_room"
                        placeholder="{{ __('Enter Number of Rooms') }}"value="{{ $package->number_of_room }}">
                      <p class="text-warning">{{ __('Enter 999999 , then it will appear as unlimited') }}</p>
                      <p id="err_number_of_room" class="mb-0 text-danger em"></p>
                    </div>
                  </div>

                  <div class="col-lg-6">
                    <div class="form-group">
                      <label class="form-label">{{ __('Number of images per Room') . '*' }}</label>
                      <input type="number" class="form-control" name="number_of_images_per_room"
                        placeholder="{{ __('Enter Number of images per Room') }}"value="{{ $package->number_of_images_per_room }}">
                      <p class="text-warning">{{ __('Enter 999999 , then it will appear as unlimited') }}</p>
                      <p id="err_number_of_images_per_room" class="mb-0 text-danger em"></p>
                    </div>
                  </div>


                  <div class="col-lg-6">
                    <div class="form-group">
                      <label for="">{{ __('Number of Amenities Per Room') . '*' }} </label>
                      <input type="number" class="form-control"
                        name="number_of_amenities_per_room"placeholder="{{ __('Enter Number of Amenities Per Room') }}"
                        value="{{ $package->number_of_amenities_per_room }}">
                      <p class="text-warning">{{ __('Enter 999999 , then it will appear as unlimited') }}</p>
                      <p id="err_number_of_amenities_per_room" class="mb-0 text-danger em"></p>
                    </div>
                  </div>
                  <div class="col-lg-6">
                    <div class="form-group">
                      <label class="form-label">{{ __('Number of Bookings') . '*' }}</label>
                      <input type="number" class="form-control" name="number_of_bookings"
                        placeholder="{{ __('Enter Number of image pers Room') }}"value="{{ $package->number_of_bookings }}">
                      <p class="text-warning">{{ __('Enter 999999 , then it will appear as unlimited') }}</p>
                      <p id="err_number_of_bookings" class="mb-0 text-danger em"></p>
                    </div>
                  </div>

                </div>

                <div class="form-group">
                  <label for="status">{{ __('Status') . '*' }}</label>
                  <select id="status" class="form-control ltr" name="status">
                    <option value="" selected disabled>{{ __('Select a status') }}</option>
                    <option value="1" {{ $package->status == '1' ? 'selected' : '' }}>
                      {{ __('Active') }}</option>
                    <option value="0" {{ $package->status == '0' ? 'selected' : '' }}>
                      {{ __('Deactive') }}</option>
                  </select>
                  <p id="err_status" class="mb-0 text-danger em"></p>
                </div>
                <div class="form-group">
                  <label class="form-label">{{ __('Popular') }} </label>
                  <div class="selectgroup w-100">
                    <label class="selectgroup-item">
                      <input type="radio" name="recommended" value="1"
                        class="selectgroup-input"@if ($package->recommended == '1') checked @endif>
                      <span class="selectgroup-button">{{ __('Yes') }}</span>
                    </label>
                    <label class="selectgroup-item">
                      <input type="radio" name="recommended" value="0" class="selectgroup-input"
                        @if ($package->recommended == '0') checked @endif>
                      <span class="selectgroup-button">{{ __('No') }}</span>
                    </label>
                  </div>
                </div>
                <div class="form-group">
                  <label>{{ __('Custom Features') }}</label>
                  <textarea class="form-control" name="custom_features" rows="5"
                    placeholder="{{ __('Enter Custom Features') }}">{{ $package->custom_features }}</textarea>
                  <p class="text-warning">
                    <small>{{ __('Enter new line to seperate features') }}</small>
                  </p>
                </div>

              </form>
            </div>
          </div>
        </div>
        <div class="card-footer">
          <div class="form">
            <div class="form-group from-show-notify row">
              <div class="col-12 text-center">
                <button type="submit" id="submitBtn" class="btn btn-success">{{ __('Update') }}</button>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
@endsection

@section('script')
  <script src="{{ asset('assets/admin/js/packages-edit.js') }}"></script>
@endsection
