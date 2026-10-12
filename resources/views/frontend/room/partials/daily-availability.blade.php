@php
    $availability = $dailyAvailability[$room->id] ?? null;
    $eligibilityStatus = $courtAvailabilityEligibility[$room->id]['status'] ?? 'owner_registration_required';
@endphp

<div class="court-availability">
    @if ($eligibilityStatus === 'available')
    <div class="court-availability__heading">
        <span class="court-availability__title">
            <i class="fal fa-calendar-alt" aria-hidden="true"></i>
            Daily availability
        </span>
        @if ($availability)
            <span class="court-availability__date">{{ $availability['date'] }}</span>
        @endif
    </div>

    @if ($availability && count($availability['slots']))
        <div class="court-availability__calendar">
            <div class="court-availability__calendar-head" role="row">
                <span>Time</span>
                <span>
                    <i class="fas fa-circle is-vacant" aria-hidden="true"></i> Vacant
                    <i class="fas fa-circle is-occupied" aria-hidden="true"></i> Occupied
                </span>
            </div>
            <div class="court-availability__slots" role="table"
                aria-label="Hourly court availability from {{ $availability['open'] }} to {{ $availability['close'] }}">
                @foreach ($availability['slots'] as $slot)
                    <div class="court-availability__slot" role="row">
                        <div class="court-availability__time" role="cell">
                            <strong>{{ $slot['start'] }}</strong>
                            <span>{{ $slot['end'] }}</span>
                        </div>
                        <div class="court-availability__event {{ $slot['occupied'] ? 'is-occupied' : 'is-vacant' }}"
                            role="cell" title="{{ $slot['label'] }}: {{ $slot['occupied'] ? 'Occupied' : 'Vacant' }}">
                            <i class="fas {{ $slot['occupied'] ? 'fa-ban' : 'fa-calendar-check' }}" aria-hidden="true"></i>
                            <span>{{ $slot['occupied'] ? 'Occupied' : 'Vacant' }}</span>
                            <small>{{ $slot['label'] }}</small>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="court-availability__hours">
                <i class="fal fa-clock" aria-hidden="true"></i>
                Open {{ $availability['open'] }} &ndash; Close {{ $availability['close'] }}
            </div>
        </div>
    @else
        <div class="court-availability__empty">Operating hours are not configured.</div>
    @endif
    @else
        <div class="court-availability__notice is-{{ str_replace('_required', '', $eligibilityStatus) }}">
            @if ($eligibilityStatus === 'subscription_required')
                <span class="court-availability__notice-icon"><i class="fas fa-box-open" aria-hidden="true"></i></span>
                <strong>{{ __('Subscription Required') }}</strong>
                <p>{{ __('The Site owner must purchase an active subscription package before court availability can be displayed.') }}</p>
                <a class="btn btn-sm btn-primary radius-sm" href="{{ route('frontend.pricing') }}">
                    <i class="fal fa-tags me-1" aria-hidden="true"></i>{{ __('View Subscription Packages') }}
                </a>
            @elseif ($eligibilityStatus === 'verification_required')
                <span class="court-availability__notice-icon"><i class="fas fa-exclamation-triangle" aria-hidden="true"></i></span>
                <strong>{{ __('Booking Unavailable') }}</strong>
                <p>{{ __('This Site is active and visible, but it is unverified and cannot accept bookings until an administrator approves its verification.') }}</p>
            @else
                <span class="court-availability__notice-icon"><i class="fas fa-store" aria-hidden="true"></i></span>
                <strong>{{ __('List This Site') }}</strong>
                <p>{{ __('This Site does not have a registered owner who can accept bookings.') }}</p>
                <a class="btn btn-sm btn-primary radius-sm" href="{{ route('vendor.signup') }}">
                    {{ __('Are you the Site owner? Register now') }}
                </a>
            @endif
        </div>
    @endif
</div>
