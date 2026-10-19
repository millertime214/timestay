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
        <form class="court-availability__checkout-form" action="{{ route('frontend.room.go.checkout') }}" method="POST"
            data-multi-slot-form data-price-options="{{ json_encode($availability['price_options'] ?? []) }}">
            @csrf
            <input type="hidden" name="room_id" value="{{ $room->id }}">
            <input type="hidden" name="checkInDate" value="">
            <input type="hidden" name="checkInTime" value="">
            <input type="hidden" name="check_out_time" value="">
            <input type="hidden" name="hour" value="">
            <input type="hidden" name="price" value="">
            <input type="hidden" name="adult" value="1">
            <input type="hidden" name="children" value="0">
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
                    @php($canSelectSlot = !$slot['occupied'] && !empty($availability['price_options']))
                    <div class="court-availability__slot" role="row">
                        <div class="court-availability__time" role="cell">
                            <strong>{{ $slot['start'] }}</strong>
                            <span>{{ $slot['end'] }}</span>
                        </div>
                        @if ($canSelectSlot)
                            <button class="court-availability__event is-vacant" type="button"
                                data-availability-entry
                                data-availability-slot
                                data-date="{{ $slot['date'] }}"
                                data-start="{{ $slot['start_time'] }}"
                                data-end="{{ $slot['end_time'] }}"
                                data-hours="{{ $slot['hours'] }}"
                                aria-pressed="false"
                                title="{{ __('Select') }} {{ $slot['label'] }}">
                                <i class="fas fa-calendar-check" aria-hidden="true"></i>
                                <span>{{ __('Select time') }}</span>
                                <small>{{ $slot['label'] }}</small>
                            </button>
                        @else
                            <div class="court-availability__event {{ $slot['occupied'] ? 'is-occupied' : 'is-unpriced' }}"
                                data-availability-entry
                                role="cell" title="{{ $slot['label'] }}: {{ $slot['occupied'] ? __('Occupied') : __('Booking price unavailable') }}">
                                <i class="fas {{ $slot['occupied'] ? 'fa-ban' : 'fa-calendar-times' }}" aria-hidden="true"></i>
                                <span>{{ $slot['occupied'] ? __('Occupied') : __('Unavailable') }}</span>
                                <small>{{ $slot['label'] }}</small>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
            <div class="court-availability__hours">
                <i class="fal fa-clock" aria-hidden="true"></i>
                Open {{ $availability['open'] }} &ndash; Close {{ $availability['close'] }}
            </div>
        </div>
        <div class="court-availability__selection" data-selection-summary aria-live="polite">
            {{ __('Select one or more consecutive vacant hours.') }}
        </div>
        <button class="btn btn-sm btn-primary radius-sm court-availability__continue" type="submit" disabled
            data-selection-submit>
            <i class="fal fa-calendar-check me-1" aria-hidden="true"></i><span>{{ __('Continue to Checkout') }}</span>
        </button>
        </form>
    @else
        <div class="court-availability__empty">
            {{ $availability && $availability['open'] && $availability['close']
                ? __('No remaining time slots are available for this date.')
                : __('Operating hours are not configured.') }}
        </div>
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
