<div class="card mb-0">
  <div class="card-header"><div class="card-title">{{ __('Previous Tournament History') }}</div></div>
  <div class="card-body">
    @if ($slot->play_format !== 'single_elimination')
      <div class="alert alert-info mb-0">{{ __('Tournament history is available for Single Elimination Open Play sessions.') }}</div>
    @elseif ($previousTournamentMatches->isEmpty())
      <div class="alert alert-info mb-0">{{ __('No previous tournament results are available yet.') }}</div>
    @else
      <p class="text-muted">{{ __('Previous winners and eliminated teams are retained when a new bracket is generated.') }}</p>
      @foreach ($previousTournamentMatches->groupBy('bracket_number') as $bracketNumber => $historyMatches)
        @php
          $finalRound = $historyMatches->max('round_number');
          $finalMatch = $historyMatches->first(fn ($item) => $item->round_number === $finalRound);
          $champions = $finalMatch && $finalMatch->winner_team
            ? $finalMatch->players->where('pivot.team', $finalMatch->winner_team)->pluck('name')->join(' / ')
            : __('No champion recorded');
        @endphp
        <div class="border rounded p-3 mb-2 d-flex flex-wrap justify-content-between align-items-center">
          <div><strong>{{ __('Tournament') }} #{{ $bracketNumber }}</strong><br><span class="text-success"><i class="fas fa-trophy mr-1"></i>{{ $champions }}</span></div>
          <div class="text-muted">{{ $historyMatches->where('winner_team', '!=', null)->count() }} {{ __('results retained') }}</div>
        </div>
        <div class="table-responsive mb-4">
          <table class="table table-sm table-bordered mb-0">
            <thead><tr><th>{{ __('Round') }}</th><th>{{ __('Winner') }}</th><th>{{ __('Eliminated') }}</th><th>{{ __('Score') }}</th></tr></thead>
            <tbody>
              @foreach ($historyMatches->whereNotNull('winner_team')->sortBy(['round_number', 'bracket_position']) as $historyMatch)
                @php
                  $winnerNames = $historyMatch->players->where('pivot.team', $historyMatch->winner_team)->pluck('name')->join(' / ');
                  $loserTeam = $historyMatch->winner_team === 1 ? 2 : 1;
                  $loserNames = $historyMatch->players->where('pivot.team', $loserTeam)->pluck('name')->join(' / ');
                @endphp
                <tr>
                  <td>{{ $historyMatch->round_number === $finalRound ? __('Final') : __('Round') . ' ' . $historyMatch->round_number }}</td>
                  <td class="text-success">{{ $winnerNames }}</td>
                  <td>{{ $loserNames ?: __('Bye') }}</td>
                  <td>{{ is_null($historyMatch->team_one_score) ? '--' : $historyMatch->team_one_score . ' - ' . $historyMatch->team_two_score }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @endforeach
    @endif
  </div>
</div>
