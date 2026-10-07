@extends('frontend.layout')

@section('pageHeading', __('Open Play Registration'))


@section('content')
  <div class="shopping-area mt-200 pb-100" style="padding-top: 200px;">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-7">
          <div class="card border radius-md">
            <div class="card-body p-4">
              @if ($slot->poster)
                <img src="{{ asset('assets/img/open-play/' . $slot->poster) }}"
                  alt="{{ $slot->title ?: __('Open Play') }}" class="img-fluid w-100 radius-md mb-4">
              @endif
              <h3 class="mb-1">{{ $slot->title ?: __('Register for Open Play') }}</h3>
              @if ($slot->category)<span class="badge bg-primary mb-3">{{ $slot->category }}</span>@endif
              <h5>{{ optional($roomContent)->title ?: __('Court') . ' #' . $slot->room_id }}</h5>
              <p class="mb-1"><strong>{{ __('Schedule') }}:</strong>
                {{ $slot->starts_at->format('M d, Y h:i A') }} {{ __('to') }} {{ $slot->ends_at->format('M d, Y h:i A') }}
              </p>
              <p><strong>{{ __('Amount Per Player') }}:</strong> {{ symbolPrice($slot->price_per_player) }}</p>
              <p><strong>{{ __('Available Player Spots') }}:</strong> {{ $remainingPlayers }} / {{ $slot->max_players }}</p>
              @if ($slot->reason)<p>{{ $slot->reason }}</p>@endif

              @if ($registrationUnavailable)
                <div class="alert alert-warning"><i class="fas fa-exclamation-triangle mr-1"></i>{{ $registrationUnavailable }}</div>
              @endif
              @error('registration')<div class="alert alert-danger">{{ $message }}</div>@enderror

              @if (!$registrationUnavailable)
                <form method="POST" action="{{ route('frontend.open_play.checkout', $slot->id) }}">
                @csrf
                <div class="form-group mb-3">
                  <label for="players">{{ __('Number of Players') }} *</label>
                  <input type="number" class="form-control" id="players" name="players" min="1"
                    max="{{ $registrationLimit }}" value="{{ old('players', 1) }}" {{ $remainingPlayers > 0 ? 'required' : 'disabled' }}>
                  @error('players')<p class="text-danger mt-2">{{ $message }}</p>@enderror
                </div>
                <div id="player-details" class="mb-3"></div>
                <div class="alert alert-info">
                  {{ __('The checkout total is the amount per player multiplied by the number of players.') }}
                </div>
                @if ($remainingPlayers > 0)
                  <button class="btn btn-primary" type="submit">{{ __('Continue to Checkout') }}</button>
                @else
                  <div class="alert alert-warning mb-0">{{ __('This Open Play session is already full.') }}</div>
                @endif
                </form>
              @endif
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection

@section('script')
  @if (!$registrationUnavailable)
    <script>
    (() => {
      const countInput = document.getElementById('players');
      const container = document.getElementById('player-details');
      const oldPlayers = @json(old('player_details', []));

      const escapeHtml = (value) => String(value ?? '').replace(/[&<>'"]/g, character => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;'
      })[character]);

      const renderPlayers = () => {
        const existing = Array.from(container.querySelectorAll('[data-player-row]')).map(row => ({
          name: row.querySelector('[data-player-name]').value,
          gender: row.querySelector('[data-player-gender]').value,
          category: row.querySelector('[data-player-category]').value,
          skill_level: row.querySelector('[data-player-skill]').value
        }));
        const count = Math.min(100, Math.max(1, Number.parseInt(countInput.value, 10) || 1));

        container.innerHTML = `<h5 class="mb-3">{{ __('Player Information') }}</h5>`;
        for (let index = 0; index < count; index += 1) {
          const player = existing[index] || oldPlayers[index] || {};
          container.insertAdjacentHTML('beforeend', `
            <div class="border radius-sm p-3 mb-3" data-player-row>
              <strong class="d-block mb-2">{{ __('Player') }} ${index + 1}</strong>
              <div class="row">
                <div class="col-md-4 form-group mb-2">
                  <label>{{ __('Name') }} *</label>
                  <input type="text" class="form-control" name="player_details[${index}][name]"
                    value="${escapeHtml(player.name)}" maxlength="100" data-player-name required>
                </div>
                <div class="col-md-2 form-group mb-2">
                  <label>{{ __('Gender') }} *</label>
                  <select class="form-control" name="player_details[${index}][gender]" data-player-gender required>
                    <option value="">{{ __('Select Gender') }}</option>
                    ${['Male', 'Female', 'Other', 'Prefer not to say'].map(gender =>
                      '<option value="' + gender + '" ' + (player.gender === gender ? 'selected' : '') + '>' + gender + '</option>'
                    ).join('')}
                  </select>
                </div>
                <div class="col-md-3 form-group mb-2">
                  <label>{{ __('Category') }} *</label>
                  <select class="form-control" name="player_details[${index}][category]" data-player-category required>
                    <option value="">{{ __('Select Category') }}</option>
                    ${['Beginner', 'Intermediate', 'Advance'].map(category =>
                      '<option value="' + category + '" ' + (player.category === category ? 'selected' : '') + '>' + category + '</option>'
                    ).join('')}
                  </select>
                </div>
                <div class="col-md-3 form-group mb-2">
                  <label>{{ __('Rating (1-10)') }} *</label>
                  <input type="number" class="form-control" name="player_details[${index}][skill_level]"
                    value="${escapeHtml(player.skill_level)}" min="1" max="10" step="1"
                    placeholder="{{ __('1-10') }}" data-player-skill required>
                </div>
              </div>
            </div>`);
        }
      };

      countInput.addEventListener('input', renderPlayers);
      renderPlayers();
    })();
    </script>
  @endif
@endsection
