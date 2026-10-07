@extends('vendors.layout')

@section('content')
  <div class="page-header">
    <h4 class="page-title">{{ __('Court Schedule') }}</h4>
    <ul class="breadcrumbs">
      <li class="nav-home"><a href="{{ route('vendor.dashboard') }}"><i class="flaticon-home"></i></a></li>
      <li class="separator"><i class="flaticon-right-arrow"></i></li>
      <li class="nav-item"><a href="{{ route('vendor.room_bookings.all_bookings', ['language' => $defaultLang->code]) }}">{{ __('Court Bookings') }}</a></li>
      <li class="separator"><i class="flaticon-right-arrow"></i></li>
      <li class="nav-item"><a href="#">{{ __('Court Schedule') }}</a></li>
    </ul>
  </div>

  <div class="row">
    <div class="{{ $canManageOpenPlay ? 'col-lg-6' : 'col-lg-12' }}">
      <div class="card">
        <div class="card-header">
          <div class="card-title">{{ __('Block a Court Timeslot') }}</div>
        </div>
        <div class="card-body">
          <p class="text-muted">{{ __('Use blocks for maintenance, private events, closures, or other unavailable periods.') }}</p>
          <form method="POST" action="{{ route('vendor.court_schedule.store') }}">
            @csrf
            <div class="form-group">
              <label>{{ __('Court') }} *</label>
              <select name="room_id" class="form-control" required>
                <option value="" disabled @selected(!old('room_id'))>{{ __('Select Court') }}</option>
                @foreach ($courts as $court)
                  <option value="{{ $court->id }}" @selected(old('room_id') == $court->id)>
                    {{ optional($court->room_content->first())->title ?: __('Court') . ' #' . $court->id }}
                  </option>
                @endforeach
              </select>
              @error('room_id')
              <p class="text-danger mt-2">{{ $message }}</p>
              @enderror
            </div>
            <div class="form-group">
              <label>{{ __('Starts At') }} *</label>
              <input type="datetime-local" name="starts_at" class="form-control" value="{{ old('starts_at') }}" required>
              @error('starts_at')
              <p class="text-danger mt-2">{{ $message }}</p>
              @enderror
            </div>
            <div class="form-group">
              <label>{{ __('Ends At') }} *</label>
              <input type="datetime-local" name="ends_at" class="form-control" value="{{ old('ends_at') }}" required>
              @error('ends_at')
              <p class="text-danger mt-2">{{ $message }}</p>
              @enderror
            </div>
            <div class="form-group">
              <label>{{ __('Reason') }}</label>
              <input name="reason" class="form-control" maxlength="255" value="{{ old('reason') }}" placeholder="{{ __('Maintenance, private event, closure...') }}">
            </div>
            <button class="btn btn-warning" type="submit"><i class="fas fa-ban"></i> {{ __('Block Timeslot') }}</button>
          </form>
        </div>
      </div>
    </div>

    @if ($canManageOpenPlay)
    <div class="col-lg-6">
      <div class="card">
        <div class="card-header">
          <div class="card-title">{{ __('Add Open Play Hours') }}</div>
        </div>
        <div class="card-body">
          <p class="text-muted">{{ __('Publish an Open Play session of any length and set the fee charged to each player.') }}</p>
          <form method="POST" action="{{ route('vendor.court_schedule.open_play.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
              <label>{{ __('Title') }} *</label>
              <input type="text" name="title" class="form-control" maxlength="255" value="{{ old('title') }}"
                placeholder="{{ __('e.g. Friday Night Open Play') }}" required>
              @error('title')<p class="text-danger mt-2">{{ $message }}</p>@enderror
            </div>
            <div class="form-group">
              <label>{{ __('Category') }} *</label>
              <input type="text" name="category" class="form-control" maxlength="100" value="{{ old('category') }}"
                placeholder="{{ __('e.g. Beginner, Intermediate, Competitive') }}" required>
              @error('category')<p class="text-danger mt-2">{{ $message }}</p>@enderror
            </div>
            <div class="form-group">
              <label>{{ __('Court') }} *</label>
              <select name="room_id" class="form-control" required>
                <option value="" disabled selected>{{ __('Select Court') }}</option>
                @foreach ($courts as $court)
                  <option value="{{ $court->id }}" @selected(old('room_id') == $court->id)>
                    {{ optional($court->room_content->first())->title ?: __('Court') . ' #' . $court->id }}
                  </option>
                @endforeach
              </select>
            </div>
            <div class="form-group">
              <label>{{ __('Starts At') }} *</label>
              <input type="datetime-local" name="starts_at" class="form-control" value="{{ old('starts_at') }}" required>
              @error('starts_at')
              <p class="text-danger mt-2">{{ $message }}</p>
              @enderror
              @error('open_play_starts_at')
              <p class="text-danger mt-2">{{ $message }}</p>
              @enderror
            </div>
            <div class="form-group">
              <label>{{ __('Ends At') }} *</label>
              <input type="datetime-local" name="ends_at" class="form-control" value="{{ old('ends_at') }}" required>
              @error('ends_at')
              <p class="text-danger mt-2">{{ $message }}</p>
              @enderror
            </div>
            <div class="form-group">
              <label>{{ __('Amount Per Player') . ' (' . $currencyInfo->base_currency_text . ')' }} *</label>
              <input type="number" name="price_per_player" class="form-control" min="0.01" max="999999.99" step="0.01"
                value="{{ old('price_per_player') }}" required>
              @error('price_per_player')
              <p class="text-danger mt-2">{{ $message }}</p>
              @enderror
            </div>
            <div class="form-group">
              <label>{{ __('Maximum Players') }} *</label>
              <input type="number" name="max_players" class="form-control" min="1" max="1000"
                value="{{ old('max_players') }}" required>
              @error('max_players')
              <p class="text-danger mt-2">{{ $message }}</p>
              @enderror
            </div>
            <div class="form-group">
              <label>{{ __('Poster or Banner') }}</label>
              <input type="file" name="poster" class="form-control" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
              <small class="form-text text-muted">{{ __('Optional. JPG, JPEG, PNG, or WEBP up to 5 MB.') }}</small>
              @error('poster')
              <p class="text-danger mt-2">{{ $message }}</p>
              @enderror
            </div>
            <div class="form-group">
              <label>{{ __('Description') }}</label>
              <input name="reason" class="form-control" maxlength="255" value="{{ old('reason') }}"
                placeholder="{{ __('Skill level, organizer, or session note') }}">
            </div>
            <button class="btn btn-success" type="submit">
              <i class="fas fa-users"></i> {{ __('Add Open Play Hours') }}
            </button>
          </form>
        </div>
      </div>
    </div>
    @else
      <div class="col-12">
        <div class="alert alert-info">
          <strong>{{ __('Open Play is a package feature.') }}</strong>
          {{ __('Upgrade to a package that includes Open Play to publish sessions and accept player registrations.') }}
          <a href="{{ route('vendor.plan.extend.index') }}" class="alert-link">{{ __('View Packages') }}</a>
        </div>
      </div>
    @endif
  </div>

  <div class="row">
    <div class="{{ $canManageOpenPlay ? 'col-lg-6' : 'col-lg-12' }}">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <div class="card-title">{{ __('Upcoming Blocked Timeslots') }}</div>
          <a href="{{ route('vendor.room_bookings.all_bookings', ['language' => $defaultLang->code]) }}" class="btn btn-primary btn-sm">{{ __('Add Booking') }}</a>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>{{ __('Court') }}</th>
                  <th>{{ __('Blocked Time') }}</th>
                  <th>{{ __('Reason') }}</th>
                  <th>{{ __('Action') }}</th>
                </tr>
              </thead>
              <tbody>
                @forelse ($blocks as $block)
                  <tr>
                    <td>{{ optional($block->court->room_content->where('language_id', optional($language)->id)->first())->title ?: __('Court') . ' #' . $block->room_id }}</td>
                    <td>{{ $block->starts_at->format('M d, Y h:i A') }}<br>{{ __('to') }} {{ $block->ends_at->format('M d, Y h:i A') }}</td>
                    <td>{{ $block->reason ?: '--' }}</td>
                    <td>
                      <form method="POST" action="{{ route('vendor.court_schedule.destroy', $block->id) }}" onsubmit="return confirm('{{ __('Are you sure') }}?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-danger btn-sm" type="submit">{{ __('Remove Block') }}</button>
                      </form>
                    </td>
                  </tr>
                @empty
                  <tr><td colspan="4" class="text-center">{{ __('No blocked timeslots found.') }}</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
    @if ($canManageOpenPlay)
    <div class="col-lg-6">
      <div class="card">
        <div class="card-header">
          <div class="card-title">{{ __('Upcoming Open Play Sessions') }}</div>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-striped">
              <thead><tr><th>{{ __('Open Play') }}</th><th>{{ __('Court') }}</th><th>{{ __('Hours') }}</th><th>{{ __('Per Player') }}</th><th>{{ __('Capacity') }}</th><th>{{ __('Action') }}</th></tr></thead>
              <tbody>
                @forelse ($openPlaySlots as $slot)
                  <tr>
                    <td>
                      @if ($slot->poster)
                        <img src="{{ asset('assets/img/open-play/' . $slot->poster) }}"
                          alt="{{ $slot->title ?: __('Open Play') }}" class="rounded mb-2" style="width: 90px; height: 60px; object-fit: cover;">
                        <br>
                      @endif
                      <strong>{{ $slot->title ?: __('Open Play') }}</strong>@if ($slot->category)<br><span class="badge badge-info">{{ $slot->category }}</span>@endif
                    </td>
                    <td>{{ optional($slot->court->room_content->where('language_id', optional($language)->id)->first())->title ?: __('Court') . ' #' . $slot->room_id }}</td>
                    <td>{{ $slot->starts_at->format('M d, Y h:i A') }}<br>{{ __('to') }} {{ $slot->ends_at->format('M d, Y h:i A') }}
                      @if ($slot->reason)<br><small>{{ $slot->reason }}</small>@endif
                    </td>
                    <td>{{ symbolPrice($slot->price_per_player) }}</td>
                    <td>{{ $slot->registeredPlayerCount() }} / {{ $slot->max_players }}</td>
                    <td>
                      <a class="btn btn-primary btn-sm mb-1" href="{{ route('vendor.court_schedule.open_play.show', ['id' => $slot->id, 'language' => optional($language)->code]) }}">
                        {{ __('View Players') }}
                      </a>
                      <form method="POST" action="{{ route('vendor.court_schedule.destroy', $slot->id) }}" onsubmit="return confirm('{{ __('Are you sure') }}?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-danger btn-sm" type="submit">{{ __('Remove') }}</button>
                      </form>
                    </td>
                  </tr>
                @empty
                  <tr><td colspan="6" class="text-center">{{ __('No Open Play sessions found.') }}</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
    @endif
  </div>
@endsection
