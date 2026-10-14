@extends('vendors.layout')
@php
  Config::set('app.timezone', App\Models\BasicSettings\Basic::first()->timezone);
  $vendor = Auth::guard('vendor')->user();
  $activePackage = App\Http\Helpers\VendorPermissionHelper::currentPackagePermission($vendor->id);
  $hasPendingMembership = App\Http\Helpers\VendorPermissionHelper::hasPendingMembership($vendor->id);
  $pendingMembership = is_null($activePackage) ? App\Models\Membership::query()->where('vendor_id', $vendor->id)->where('status', 0)->whereYear('start_date', '<>', '9999')->latest('id')->first() : null;
  $pendingPackage = $pendingMembership ? App\Models\Package::query()->find($pendingMembership->package_id) : null;
@endphp

@section('style')
<style>
  .package-page-hero{background:linear-gradient(135deg,#14532d 0%,#15803d 55%,#22c55e 100%);border-radius:18px;color:#fff;overflow:hidden;padding:28px 30px;position:relative}.package-page-hero:after{background:rgba(255,255,255,.09);border-radius:50%;content:'';height:220px;position:absolute;right:-60px;top:-95px;width:220px}.package-page-hero h2{color:#fff;font-weight:700;margin-bottom:6px}.package-page-hero p{color:rgba(255,255,255,.86);margin:0;max-width:650px}.membership-summary{align-items:center;background:#fff;border:1px solid #e5e7eb;border-left:4px solid #16a34a;border-radius:12px;display:flex;flex-wrap:wrap;gap:18px;justify-content:space-between;padding:18px 20px}.membership-summary.is-pending{border-left-color:#f59e0b}.membership-summary__label{color:#6b7280;display:block;font-size:12px;font-weight:700;letter-spacing:.06em;text-transform:uppercase}.membership-summary__title{color:#111827;font-size:18px;font-weight:700}.package-grid>div{display:flex}.package-card{background:#fff;border:1px solid #e5e7eb;border-radius:16px;box-shadow:0 8px 28px rgba(15,23,42,.06);display:flex;flex-direction:column;overflow:hidden;position:relative;transition:transform .2s ease,box-shadow .2s ease;width:100%}.package-card:hover{box-shadow:0 16px 38px rgba(15,23,42,.11);transform:translateY(-4px)}.package-card.is-current{border:2px solid #16a34a}.package-card__accent{background:#16a34a;height:5px}.package-card__body{display:flex;flex:1;flex-direction:column;padding:24px}.package-card__badges{min-height:25px}.package-card__title{color:#111827;font-size:21px;font-weight:700;margin:12px 0 6px}.package-card__price{color:#15803d;font-size:30px;font-weight:800;line-height:1.2}.package-card__term{color:#6b7280;font-size:14px;font-weight:500}.package-card__divider{border-top:1px solid #eef0f3;margin:20px 0}.package-feature-list{list-style:none;margin:0 0 22px;padding:0}.package-feature-list li{align-items:flex-start;color:#374151;display:flex;gap:10px;padding:7px 0}.package-feature-list i{color:#16a34a;margin-top:3px;width:16px}.package-feature-list li.is-disabled{color:#9ca3af;text-decoration:line-through}.package-feature-list li.is-disabled i{color:#d1d5db}.package-card__action{margin-top:auto}.package-card__action .btn{border-radius:9px;font-weight:700;padding:11px 16px;width:100%}.package-unavailable{background:#f3f4f6;border-radius:9px;color:#6b7280;font-weight:600;padding:11px;text-align:center}@media(max-width:575.98px){.package-page-hero{padding:22px 20px}.membership-summary{align-items:flex-start;flex-direction:column}}
</style>
@endsection

@section('content')
  <div class="package-page-hero mb-4">
    <h2>{{ __('Choose Your Package') }}</h2>
    <p>{{ __('Select the plan that fits your pickleball business. Review limits and included management tools before checkout.') }}</p>
  </div>

  @if (is_null($activePackage))
    <div class="membership-summary {{ $pendingPackage ? 'is-pending' : '' }} mb-4">
      <div><span class="membership-summary__label">{{ $pendingPackage ? __('Pending Package') : __('Membership Status') }}</span><span class="membership-summary__title">{{ $pendingPackage ? $pendingPackage->title : __('No active package') }}</span></div>
      <div>@if ($pendingPackage)<span class="badge badge-warning px-3 py-2">{{ __('Waiting for admin approval') }}</span>@else<span class="text-muted">{{ __('Purchase a package to activate vendor features.') }}</span>@endif</div>
    </div>
  @else
    <div class="membership-summary mb-4">
      <div><span class="membership-summary__label">{{ __('Current Package') }}</span><span class="membership-summary__title">{{ $current_package->title }}</span> <span class="badge badge-success ml-2">{{ __(ucwords(str_replace('-', ' ', $current_membership->billing_term ?: $current_package->term))) }}</span></div>
      <div><span class="membership-summary__label">{{ __('Expiration') }}</span><strong>{{ ($current_membership->billing_term ?: $current_package->term) === 'lifetime' ? __('Lifetime') : Carbon\Carbon::parse($current_membership->expire_date)->format('M d, Y') }}</strong>@if ($current_membership->is_trial == 1)<span class="badge badge-primary ml-2">{{ __('Trial') }}</span>@endif</div>
      @if ($package_count >= 2 && $next_package)<div><span class="membership-summary__label">{{ __('Next Package') }}</span><strong>{{ $next_package->title }}</strong> <span class="badge badge-{{ $next_membership->status == 0 ? 'warning' : 'info' }} ml-2">{{ $next_membership->status == 0 ? __('Pending') : __('Scheduled') }}</span></div>@endif
    </div>
  @endif

  <div class="d-flex align-items-end justify-content-between flex-wrap mb-3">
    <div><h4 class="mb-1 font-weight-bold">{{ __('Available Packages') }}</h4><p class="text-muted mb-0">{{ __('Compare package limits and features.') }}</p></div>
    @if ($hasPendingMembership)<small class="text-warning font-weight-bold mt-2"><i class="fas fa-clock mr-1"></i>{{ __('A payment request is awaiting review.') }}</small>@endif
  </div>

  <div class="row package-grid mb-5">
    @forelse ($packages as $plan)
      @php
        $permissions = !empty($plan->features) ? json_decode($plan->features, true) : [];
        $permissions = is_array($permissions) ? $permissions : [];
        $isCurrent = isset($current_package->id) && $current_package->id === $plan->id;
        $isNext = $package_count >= 2 && isset($next_package->id) && $next_package->id === $plan->id;
        $limitLabel = function ($value, $single, $plural) { return $value == 999999 ? __($plural) . ': ' . __('Unlimited') : __($value == 1 ? $single : $plural) . ': ' . $value; };
      @endphp
      <div class="col-xl-4 col-md-6 mb-4">
        <article class="package-card {{ $isCurrent ? 'is-current' : '' }}">
          <div class="package-card__accent"></div>
          <div class="package-card__body">
            <div class="package-card__badges">@if ($isCurrent)<span class="badge badge-success">{{ __('Current Package') }}</span>@endif @if ($isNext)<span class="badge badge-warning">{{ __('Next Package') }}</span>@endif</div>
            <h3 class="package-card__title">{{ $plan->title }}</h3>
            <div class="package-card__price">{{ $plan->price == 0 ? __('Free') : format_price($plan->price) }} @if ((float) $plan->price > 0)<span class="package-card__term">/ {{ __('Month') }}</span>@endif</div>
            <div class="package-card__divider"></div>
            <ul class="package-feature-list">
              <li><i class="fas fa-check-circle"></i><span>{{ $limitLabel($plan->number_of_hotel, 'Site', 'Sites') }}</span></li>
              <li><i class="fas fa-check-circle"></i><span>{{ $limitLabel($plan->number_of_room, 'Court', 'Courts') }}</span></li>
              <li><i class="fas fa-check-circle"></i><span>{{ $limitLabel($plan->number_of_amenities_per_hotel, 'Amenity per Site', 'Amenities per Site') }}</span></li>
              <li><i class="fas fa-check-circle"></i><span>{{ $limitLabel($plan->number_of_amenities_per_room, 'Amenity per Court', 'Amenities per Court') }}</span></li>
              <li><i class="fas fa-check-circle"></i><span>{{ $limitLabel($plan->number_of_images_per_hotel, 'Image per Site', 'Images per Site') }}</span></li>
              <li><i class="fas fa-check-circle"></i><span>{{ $limitLabel($plan->number_of_images_per_room, 'Image per Court', 'Images per Court') }}</span></li>
              <li><i class="fas fa-check-circle"></i><span>{{ $limitLabel($plan->number_of_bookings, 'Booking', 'Bookings') }}</span></li>
              @foreach (['Add Booking From Dashboard' => 'Add Booking From Dashboard', 'Edit Booking From Dashboard' => 'Edit Booking From Dashboard', 'Support Tickets' => 'Support Tickets', 'Live Court Display' => 'Live Court Display', 'Open Play' => 'Open Play', 'Pickleball Match Maker' => 'Automated Match Maker'] as $feature => $label)
                @php $included = in_array($feature, $permissions); @endphp
                <li class="{{ $included ? '' : 'is-disabled' }}"><i class="fas fa-{{ $included ? 'check-circle' : 'times-circle' }}"></i><span>{{ __($label) }}</span></li>
              @endforeach
              @if (!is_null($plan->custom_features))
                @foreach (array_filter(array_map('trim', explode("\n", $plan->custom_features))) as $feature)<li><i class="fas fa-check-circle"></i><span>{{ $feature }}</span></li>@endforeach
              @endif
            </ul>
            <div class="package-card__action">
              @if ($package_count < 2 && !$hasPendingMembership)
                @if ($isCurrent)
                  @if ($plan->term != 'lifetime' || $current_membership->is_trial == 1)<a href="{{ route('vendor.plan.extend.checkout', $plan->id) }}" class="btn btn-success">{{ __('Extend Package') }}</a>@else<div class="package-unavailable">{{ __('Lifetime package active') }}</div>@endif
                @else<a href="{{ route('vendor.plan.extend.checkout', $plan->id) }}" class="btn btn-primary">{{ __('Choose Package') }}</a>@endif
              @else<div class="package-unavailable"><i class="fas fa-lock mr-1"></i>{{ __('Purchase currently unavailable') }}</div>@endif
            </div>
          </div>
        </article>
      </div>
    @empty
      <div class="col-12"><div class="card"><div class="card-body text-center py-5 text-muted">{{ __('No packages are currently available.') }}</div></div></div>
    @endforelse
  </div>
@endsection
