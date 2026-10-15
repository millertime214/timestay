<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>{{ $slot->title ?: __('Open Play Tournament') }}</title>
   <link rel="shortcut icon" type="image/png" href="{{ asset('assets/img/' . $websiteInfo->favicon) }}">
  <style>
    :root{color-scheme:dark;--bg:#07111f;--panel:#10243a;--line:#2b4967;--gold:#ffc857;--green:#4ee39a;--muted:#91a9c0}*{box-sizing:border-box}body{margin:0;background:radial-gradient(circle at top,#15304c,var(--bg) 60%);color:#f5f8fc;font-family:Arial,sans-serif;min-height:100vh}.head{align-items:center;border-bottom:1px solid var(--line);display:grid;gap:24px;grid-template-columns:minmax(0,1fr) auto minmax(0,1fr);padding:24px 32px}.head__meta{text-align:right}.display-logo{align-items:center;display:flex;justify-content:center;min-width:130px}.display-logo img{display:block;max-height:72px;max-width:210px;object-fit:contain}.display-logo__fallback{color:#fff;font-size:1.2rem;font-weight:900;letter-spacing:.08em;text-align:center;text-transform:uppercase}h1{margin:0;font-size:clamp(1.6rem,3vw,3rem)}.live{color:var(--green);font-weight:800;letter-spacing:.12em}.bracket{display:flex;gap:24px;align-items:stretch;overflow-x:auto;padding:28px;min-height:calc(100vh - 105px)}.round{min-width:290px;flex:1;display:flex;flex-direction:column}.round h2{text-align:center;color:var(--gold);text-transform:uppercase;letter-spacing:.08em}.matches{display:flex;flex:1;flex-direction:column;justify-content:space-around;gap:22px}.match{background:var(--panel);border:1px solid var(--line);border-radius:14px;padding:14px;box-shadow:0 12px 30px #0005}.team{display:grid;grid-template-columns:1fr auto;gap:12px;padding:13px;border-radius:9px;color:var(--muted)}.team+.team{border-top:1px solid var(--line)}.team.winner{background:#143c32;color:#fff;border-left:5px solid var(--green)}.team.loser{opacity:.55;text-decoration:none}.names{font-size:1.05rem;font-weight:700}.score{font-size:1.45rem;font-weight:900}.waiting{color:var(--muted);font-style:italic}.champion{color:var(--gold)!important;border-left-color:var(--gold)!important}.updated{color:var(--muted);font-size:.8rem;margin-top:6px}@media(max-width:700px){.head{grid-template-columns:1fr;padding:18px}.display-logo{grid-row:1}.head>div:first-child{grid-row:2}.head__meta{grid-row:3;text-align:left}.display-logo img{max-height:58px;max-width:180px}.bracket{padding:18px}.round{min-width:260px}}
  </style>
</head>
<body>
  <header class="head">
    <div>
      <div class="live">● {{ __('LIVE TOURNAMENT') }}</div>
      <h1 id="title">{{ $slot->title ?: __('Open Play Tournament') }}</h1>
    </div>
    <div class="display-logo" aria-label="{{ __('Site logo') }}">
      @if(!empty($site->logo))
      <img src="{{ asset('assets/img/hotel/logo/'.$site->logo) }}" alt="{{ optional($site->hotel_contents->first())->title ?: __('Site logo') }}">
      @else
      <span class="display-logo__fallback">{{ optional(optional($site)->hotel_contents?->first())->title ?: ($websiteInfo->website_title ?? config('app.name')) }}</span>
      @endif
    </div>
    <div class="head__meta">
      <strong>{{ __('Single Elimination') }} · <span id="bracket-number"></span></strong>
      <div class="updated" id="updated"></div>
    </div>
  </header>
  <main class="bracket" id="bracket" data-endpoint="{{ route('vendor.court_schedule.open_play.tournament_data', $slot->id) }}"></main>
  <script>
    (() => {
      const root = document.getElementById('bracket');
      const esc = value => String(value ?? '').replace(/[&<>"']/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[char]));
      const team = (names, score, side, match, isFinal) => {
        const won = match.winner_team === side;
        const lost = match.status === 'completed' && match.winner_team && !won;
        const label = names.length ? names.map(esc).join(' / ') : @json(__('Waiting for winner'));
        return `<div class="team ${won ? 'winner' : ''} ${lost ? 'loser' : ''} ${won && isFinal ? 'champion' : ''}"><span class="names">${won && isFinal ? '🏆 ' : ''}${label}${won ? ' · Winner' : (lost ? ' · Eliminated' : '')}</span><span class="score">${score ?? '—'}</span></div>`;
      };
      async function refresh(){
        try {
          const response = await fetch(root.dataset.endpoint, {headers:{'Accept':'application/json'}, credentials:'same-origin'});
          if (!response.ok) throw new Error('request failed');
          const data = await response.json();
          document.getElementById('title').textContent = data.title;
          document.getElementById('bracket-number').textContent = @json(__('Tournament')) + ' #' + data.bracket_number;
          root.innerHTML = data.rounds.map((round, index) => `<section class="round"><h2>${esc(round.label)}</h2><div class="matches">${round.matches.map(match => `<article class="match">${team(match.team_one, match.team_one_score, 1, match, index === data.rounds.length - 1)}${team(match.team_two, match.team_two_score, 2, match, index === data.rounds.length - 1)}</article>`).join('')}</div></section>`).join('');
          document.getElementById('updated').textContent = @json(__('Updated')) + ' ' + new Date().toLocaleTimeString();
        } catch (error) { document.getElementById('updated').textContent = @json(__('Unable to refresh')); }
      }
      refresh(); setInterval(refresh, 10000);
    })();
  </script>
</body>
</html>
