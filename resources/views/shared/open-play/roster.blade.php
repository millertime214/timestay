@php
  $courtContents = optional($slot->court)->room_content;
  $courtContent = $courtContents
      ? $courtContents->where('language_id', optional($language)->id)->first() ?: $courtContents->first()
      : null;
  $playerCount = $slot->registrations->sum(function ($registration) {
      return (int) $registration->payment_status !== 2 ? max((int) $registration->adult, 0) : 0;
  });
@endphp

<div class="row mb-4">
  <div class="col-lg-8">
    <div class="card">
      <div class="card-header"><div class="card-title">{{ $slot->title }}</div></div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6 mb-3">
            <strong>{{ __('Title') }}</strong>
            <div>{{ $slot->title ?: __('Open Play') }}</div>
          </div>
          <div class="col-md-6 mb-3">
            <strong>{{ __('Category') }}</strong>
            <div>{{ $slot->category ?: '--' }}</div>
          </div>
          <div class="col-md-6 mb-3">
            <strong>{{ __('Court') }}</strong>
            <div>{{ optional($courtContent)->title ?: __('Court') . ' #' . $slot->room_id }}</div>
          </div>
          <div class="col-md-6 mb-3">
            <strong>{{ __('Schedule') }}</strong>
            <div>{{ $slot->starts_at->format('M d, Y h:i A') }} {{ __('to') }} {{ $slot->ends_at->format('M d, Y h:i A') }}</div>
          </div>
          <div class="col-md-6 mb-3">
            <strong>{{ __('Amount Per Player') }}</strong>
            <div>{{ symbolPrice($slot->price_per_player) }}</div>
          </div>
          <div class="col-md-6 mb-3">
            <strong>{{ __('Registrations / Players / Capacity') }}</strong>
            <div>{{ $slot->registrations->count() }} / {{ $playerCount }} / {{ $slot->max_players }}</div>
          </div>
          @if ($slot->reason)
            <div class="col-12">
              <strong>{{ __('Description') }}</strong>
              <div>{{ $slot->reason }}</div>
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-header"><div class="card-title">{{ __('Registered Players') }}</div></div>
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-striped">
        <thead>
          <tr>
            <th>{{ __('Booking') }}</th>
            <th>{{ __('Player / Contact') }}</th>
            <th>{{ __('Players / Gender / Category / Skill Rating') }}</th>
            <th>{{ __('Total Amount') }}</th>
            <th>{{ __('Payment Status') }}</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($slot->registrations as $registration)
            <tr>
              <td>#{{ $registration->order_number }}</td>
              <td>
                <strong>{{ $registration->booking_name ?: optional($registration->user)->name ?: __('Guest') }}</strong>
                @if ($registration->booking_email)<br><a href="mailto:{{ $registration->booking_email }}">{{ $registration->booking_email }}</a>@endif
                @if ($registration->booking_phone)<br>{{ $registration->booking_phone }}@endif
              </td>
              <td>
                @forelse ($registration->open_play_players ?: [] as $player)
                  <div class="mb-1">
                    <strong>{{ $player['name'] ?? __('Player') }}</strong>
                    <span class="text-muted">
                      &mdash; {{ $player['gender'] ?? '--' }} / {{ $player['category'] ?? '--' }} / {{ $player['skill_level'] ?? '--' }}{{ isset($player['skill_level']) ? '/10' : '' }}
                    </span>
                  </div>
                @empty
                  {{ max((int) $registration->adult, 0) }}
                @endforelse
              </td>
              <td>{{ symbolPrice($registration->grand_total) }}</td>
              <td>
                @php
                  $status = (int) $registration->payment_status;
                  $statusLabel = $status === 1 ? __('Completed') : ($status === 0 ? __('Pending') : __('Rejected'));
                  $statusClass = $status === 1 ? 'success' : ($status === 0 ? 'warning' : 'danger');
                @endphp
                <span class="badge badge-{{ $statusClass }}">{{ $statusLabel }}</span>
              </td>
            </tr>
          @empty
            <tr><td colspan="5" class="text-center">{{ __('No players have registered for this session yet.') }}</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
