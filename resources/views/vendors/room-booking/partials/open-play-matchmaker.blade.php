<div class="card mt-4">
  <div class="card-header d-flex align-items-center justify-content-between">
    <div>
      <div class="card-title mb-1">{{ __('Pickleball Match Maker & Play Rotation') }}</div>
      <small class="text-muted">{{ $slot->play_format === 'single_elimination' ? __('Creates a doubles bracket and advances winners through the final.') : __('Balances teams, prioritizes waiting players, separates skill queues, and reduces repeated partners.') }}</small>
    </div>
  </div>
  <div class="card-body">
    @if (!$canUseMatchmaker)
      <div class="alert alert-warning mb-0">
        <strong>{{ __('Package Feature Required') }}</strong>
        <div>{{ __('Add Pickleball Match Maker to the vendor package to generate automated rounds.') }}</div>
        <a href="{{ route('vendor.plan.extend.index') }}" class="btn btn-warning btn-sm mt-2">{{ __('View Packages') }}</a>
      </div>
    @else
      @error('round')<div class="alert alert-danger">{{ $message }}</div>@enderror
      @error('team_one_score')<div class="alert alert-danger">{{ $message }}</div>@enderror
      @error('team_two_score')<div class="alert alert-danger">{{ $message }}</div>@enderror
      <div class="border rounded p-3 mb-4">
        <h5 class="mb-3">{{ __('Add Player Manually') }}</h5>
        <form method="POST" action="{{ route('vendor.court_schedule.open_play.players.store', $slot->id) }}">
          @csrf
          <input type="hidden" name="language" value="{{ optional($language)->code }}">
          <div class="row align-items-end">
            <div class="col-lg-3 col-md-6 mb-2">
              <label>{{ __('Player Name') }}</label>
              <input type="text" name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" maxlength="255" required>
              @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-lg-2 col-md-6 mb-2">
              <label>{{ __('Gender') }}</label>
              <select name="gender" class="form-control @error('gender') is-invalid @enderror">
                <option value="">{{ __('Not specified') }}</option>
                <option value="Male" @selected(old('gender') === 'Male')>{{ __('Male') }}</option>
                <option value="Female" @selected(old('gender') === 'Female')>{{ __('Female') }}</option>
                <option value="Other" @selected(old('gender') === 'Other')>{{ __('Other') }}</option>
              </select>
            </div>
            <div class="col-lg-3 col-md-6 mb-2">
              <label>{{ __('Skill Queue') }}</label>
              <input type="text" name="category" value="{{ old('category', $slot->category ?: 'Intermediate') }}" list="openPlaySkillQueues" class="form-control @error('category') is-invalid @enderror" maxlength="40" required>
              <datalist id="openPlaySkillQueues"><option value="Beginner"><option value="Intermediate"><option value="Advance"></datalist>
              @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-lg-2 col-md-6 mb-2">
              <label>{{ __('Skill Rating') }}</label>
              <input type="number" name="skill_rating" value="{{ old('skill_rating', 5) }}" min="1" max="10" class="form-control @error('skill_rating') is-invalid @enderror" required>
              @error('skill_rating')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-lg-2 mb-2">
              <button class="btn btn-success btn-block" type="submit"><i class="fas fa-user-plus mr-1"></i>{{ __('Add Player') }}</button>
            </div>
          </div>
        </form>
      </div>

      @if ($slot->play_format === 'single_elimination')
        <div class="d-flex flex-wrap justify-content-between align-items-end mb-4">
          <form method="POST" action="{{ route('vendor.court_schedule.open_play.rounds.generate', $slot->id) }}">
            @csrf
            <input type="hidden" name="language" value="{{ optional($language)->code }}">
            <input type="hidden" name="court_count" value="1">
            <button class="btn btn-primary" type="submit" @disabled($slot->rosterEntries->count() < 4 || ($matches->contains('status', 'scheduled') && $matches->whereNotNull('team_one_score')->isNotEmpty()))>
              <i class="fas fa-sitemap mr-1"></i>{{ $matches->isEmpty() ? __('Generate Tournament Bracket') : __('Regenerate Tournament Bracket') }}
            </button>
          </form>
          @if ($matches->isNotEmpty() && $canUseTournamentDisplay)
            <a class="btn btn-dark mt-2 mt-md-0" target="_blank" rel="noopener" href="{{ route('vendor.court_schedule.open_play.tournament_display', $slot->id) }}">
              <i class="fas fa-tv mr-1"></i>{{ __('Open Tournament Monitor') }}
            </a>
          @endif
        </div>
      @else
      <div class="d-flex flex-wrap justify-content-between align-items-end mb-4">
      <form method="POST" action="{{ route('vendor.court_schedule.open_play.rounds.generate', $slot->id) }}" class="mb-4">
        @csrf
        <input type="hidden" name="language" value="{{ optional($language)->code }}">
        <div class="row align-items-end">
          <div class="col-md-3 mb-2">
            <label>{{ __('Courts in This Round') }}</label>
            <input type="number" min="1" max="20" name="court_count" value="{{ old('court_count', 1) }}" class="form-control" required>
          </div>
          <div class="col-md-5 mb-2">
            <div class="custom-control custom-checkbox mt-4">
              <input type="hidden" name="skill_separated" value="0">
              <input type="checkbox" class="custom-control-input" id="skillSeparated" name="skill_separated" value="1" @checked(old('skill_separated', true))>
              <label class="custom-control-label" for="skillSeparated">{{ __('Use skill-separated queues') }}</label>
            </div>
          </div>
          <div class="col-md-4 mb-2 text-md-right">
            <button class="btn btn-primary" type="submit" @disabled($slot->rosterEntries->count() < 4)>
              <i class="fas fa-random mr-1"></i>{{ __('Generate Next Round') }}
            </button>
          </div>
        </div>
      </form>
        @if ($matches->isNotEmpty() && $canUseRotationDisplay)
          <a class="btn btn-dark mb-4" target="_blank" rel="noopener" href="{{ route('vendor.court_schedule.open_play.rotation_display', $slot->id) }}">
            <i class="fas fa-tv mr-1"></i>{{ __('Open Play Rotation Monitor') }}
          </a>
        @endif
      </div>
      @endif

      <div class="row mb-4">
        <div class="col-md-4"><div class="border rounded p-3"><strong>{{ $slot->rosterEntries->count() }}</strong><br><small>{{ __('Active Players') }}</small></div></div>
        <div class="col-md-4"><div class="border rounded p-3"><strong>{{ $slot->rosterEntries->sum('matches_played') }}</strong><br><small>{{ __('Player Match Assignments') }}</small></div></div>
        <div class="col-md-4"><div class="border rounded p-3"><strong>{{ $slot->rosterEntries->where('wait_rounds', '>', 0)->count() }}</strong><br><small>{{ __('Waiting Players') }}</small></div></div>
      </div>

      <h5>{{ __('Rotation Queue') }}</h5>
      <div class="table-responsive mb-4">
        <table class="table table-sm table-striped">
          <thead><tr><th>#</th><th>{{ __('Player') }}</th><th>{{ __('Skill Queue') }}</th><th>{{ __('Rating') }}</th><th>{{ __('Matches') }}</th><th>{{ __('Wait Rounds') }}</th></tr></thead>
          <tbody>
            @forelse ($slot->rosterEntries as $player)
              <tr><td>{{ $player->queue_position }}</td><td>{{ $player->name }} @include('shared.open-play.gender-badge', ['gender' => $player->gender]) @if(!$player->booking_id)<span class="badge badge-info ml-1">{{ __('Manual') }}</span>@endif</td><td>{{ $player->category ?: __('Unrated') }}</td><td>{{ $player->skill_rating }}/10</td><td>{{ $player->matches_played }}</td><td><span class="badge badge-{{ $player->wait_rounds ? 'warning' : 'secondary' }}">{{ $player->wait_rounds }}</span></td></tr>
            @empty<tr><td colspan="6" class="text-center">{{ __('No active players are available for matching.') }}</td></tr>@endforelse
          </tbody>
        </table>
      </div>

      <h5 id="generated-rounds">{{ $slot->play_format === 'single_elimination' ? __('Tournament Bracket') : __('Generated Rounds') }}</h5>
      @forelse ($matches->groupBy('round_number') as $roundNumber => $roundMatches)
        <div class="border rounded p-3 mb-3">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <strong>{{ $slot->play_format === 'single_elimination' && $roundNumber === $matches->max('round_number') ? __('Final') : __('Round') . ' ' . $roundNumber }}</strong>
            <span class="badge badge-{{ $roundMatches->contains('status', 'scheduled') ? 'primary' : 'success' }}">
              {{ $roundMatches->contains('status', 'scheduled') ? __('In Progress') : __('Completed') }}
            </span>
          </div>
          <div class="row">
            @foreach ($roundMatches as $match)
              @php
                $teamOne = $match->players->where('pivot.team', 1);
                $teamTwo = $match->players->where('pivot.team', 2);
              @endphp
              <div class="col-lg-6 mb-3">
                <div class="card mb-0">
                  <div class="card-header py-2"><strong>{{ __('Court') }} {{ $match->court_number }}</strong>@if($match->skill_group)<span class="badge badge-info ml-2">{{ $match->skill_group }}</span>@endif</div>
                  <div class="card-body py-3">
                    <div class="row">
                      <div class="col-5"><strong>{{ __('Team 1') }} @if($match->winner_team === 1)<span class="badge badge-success">{{ __('Winner') }}</span>@endif</strong>@foreach($teamOne as $player)<div>{{ $player->name }} @include('shared.open-play.gender-badge', ['gender' => $player->gender]) <small>({{ $player->skill_rating }})</small></div>@endforeach</div>
                      <div class="col-2 text-center align-self-center"><strong>{{ __('VS') }}</strong></div>
                      <div class="col-5"><strong>{{ __('Team 2') }} @if($match->winner_team === 2)<span class="badge badge-success">{{ __('Winner') }}</span>@endif</strong>@foreach($teamTwo as $player)<div>{{ $player->name }} @include('shared.open-play.gender-badge', ['gender' => $player->gender]) <small>({{ $player->skill_rating }})</small></div>@endforeach</div>
                    </div>
                    @if ($match->status === 'scheduled')
                      <form method="POST" action="{{ route('vendor.court_schedule.open_play.matches.complete', $match->id) }}" class="mt-3">
                        @csrf<input type="hidden" name="language" value="{{ optional($language)->code }}">
                        <div class="row align-items-end">
                          <div class="col-4"><label>{{ __('Team 1 Score') }}</label><input type="number" name="team_one_score" min="0" max="999" class="form-control form-control-sm" required></div>
                          <div class="col-4"><label>{{ __('Team 2 Score') }}</label><input type="number" name="team_two_score" min="0" max="999" class="form-control form-control-sm" required></div>
                          <div class="col-4 text-right"><button class="btn btn-success btn-sm" type="submit"><i class="fas fa-trophy mr-1"></i>{{ __('Save Result') }}</button></div>
                        </div>
                      </form>
                    @elseif (!is_null($match->team_one_score) && !is_null($match->team_two_score))
                      <div class="alert alert-light border mt-3 mb-0 text-center"><strong>{{ __('Final Score') }}: {{ $match->team_one_score }} - {{ $match->team_two_score }}</strong></div>
                    @else
                      <div class="alert alert-light border mt-3 mb-0 text-center"><strong>{{ $match->winner_team ? __('Advanced by bye') : __('No teams assigned') }}</strong></div>
                    @endif
                  </div>
                </div>
              </div>
            @endforeach
          </div>
        </div>
      @empty
        <div class="alert alert-info">{{ __('No rounds have been generated yet.') }}</div>
      @endforelse

    @endif
  </div>
</div>
