@if (($site->verification_status ?? null) !== 'approved')
  <span class="badge bg-warning text-danger" title="{{ __('This Site cannot accept bookings until it is verified.') }}">
    <i class="fas fa-exclamation-triangle"></i> {{ __('Unverified') }}
  </span>
  @else
  <span class="badge bg-success text-light" title="{{ __('This Site is verified and can accept bookings.') }}">
    <i class="fas fa-check-circle"></i> {{ __('Verified') }}
@endif
