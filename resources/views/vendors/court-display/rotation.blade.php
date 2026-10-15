<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>{{ $slot->title ?: __('Open Play Rotation') }}</title>
  <style>
    :root{color-scheme:dark;--bg:#07111e;--panel:#102238;--panel2:#162c45;--line:#294762;--text:#f4f8fc;--muted:#91a8bd;--green:#42df96;--blue:#54a8ff;--gold:#ffc85a;--red:#ff7b82}*{box-sizing:border-box;
  }
  body{
    background:radial-gradient(circle at 20% 0,#163553 0,var(--bg) 58%);color:var(--text);
    font-family:Arial,sans-serif;
    margin:0;
    min-height:100vh;
  }
    .header{
      align-items:center;border-bottom:1px solid var(--line);
      display:flex;
      gap:28px;
      justify-content:space-between;
      padding:20px 28px;
    }
    .live{
      color:var(--green);
      font-size:.78rem;
      font-weight:900;
      letter-spacing:.13em;
    }
    .header h1{font-size:clamp(1.7rem,3vw,3rem);margin:3px 0;}
    .meta{color:var(--muted);text-align:right;}
    .clock{color:var(--gold);font-size:1.7rem;font-variant-numeric:tabular-nums;font-weight:900;}
    .stats{display:grid;gap:12px;grid-template-columns:repeat(5,1fr);padding:16px 28px 0;}
    .stat{background:#0d1d2e;border:1px solid var(--line);border-radius:12px;padding:12px 15px;}
    .stat strong{display:block;font-size:1.65rem;}
    .stat span{color:var(--muted);font-size:.74rem;font-weight:800;letter-spacing:.07em;text-transform:uppercase;}
    .layout{display:grid;gap:18px;grid-template-columns:minmax(0,2fr) minmax(320px,1fr);padding:18px 28px 28px}
    .section-title{align-items:center;display:flex;justify-content:space-between;margin:0 0 12px;}
    .section-title h2{font-size:1.1rem;letter-spacing:.08em;margin:0;text-transform:uppercase;}
    .badge{
      background:#203d5b;
      border-radius:20px;
      color:var(--blue);
      font-size:.72rem;
      font-weight:800;
      padding:6px 10px;
  }
  .courts{
    display:grid;
    gap:16px;
    grid-template-columns:repeat(2,minmax(0,1fr));
  }
  .court{
    background:var(--panel);border:1px solid var(--line);
    border-radius:15px;
    box-shadow:0 12px 28px #0004;
    overflow:hidden;
  }
  .court-head{
    align-items:center;
    background:#17324d;
    display:flex;
    justify-content:space-between;
    padding:11px 15px;
  }
  .court-head strong{
    color:var(--gold);
    font-size:1.15rem;
  }
  .score-form{align-items:end;
  background:#0b1b2b;
  border-top:1px solid var(--line);
  display:grid;gap:10px;
  grid-template-columns:1fr 1fr auto;
  padding:13px;
}
.score-field label,.round-controls label{
  color:var(--muted);
  display:block;
  font-size:.68rem;
  font-weight:800;
  letter-spacing:.06em;
  margin-bottom:5px;
  text-transform:uppercase;
}
.score-field input,.round-controls input{background:#071522;border:1px solid #3b5871;border-radius:8px;color:#fff;font-size:1rem;font-weight:800;padding:9px;width:100%}.action-button{background:var(--green);border:0;border-radius:8px;color:#062116;cursor:pointer;font-weight:900;padding:11px 15px}.action-button:hover{filter:brightness(1.08)}.action-button:disabled{cursor:not-allowed;filter:grayscale(.7);opacity:.55}.round-tools{align-items:end;display:flex;gap:10px}.round-controls{width:90px}.feedback{border-radius:8px;display:none;font-size:.85rem;font-weight:700;margin-bottom:12px;padding:10px 13px}.feedback.success{background:#123c30;color:var(--green);display:block}.feedback.error{background:#48252d;color:#ffabb0;display:block}
  .versus{
    align-items:stretch;
    display:grid;
    grid-template-columns:1fr auto 1fr;
    min-height:140px;
  }
  .team{
    padding:17px;
}
  .team-label{
    color:var(--muted);
    font-size:.7rem;
    font-weight:900;
    letter-spacing:.1em;
    margin-bottom:10px;
    text-transform:uppercase;
  }
  .player{
    background:rgba(255,255,255,.035);
    border-radius:8px;margin:7px 0;
    padding:9px;
  }
  .player strong{
    display:block;
  }
  .player small{
    color:var(--muted);
}
  .vs{
    align-self:center;color:var(--blue);
    font-weight:900;padding:10px;
  }
  .queue-panel{
    background:var(--panel);
    border:1px solid var(--line);
  border-radius:15px;
  padding:16px;
      max-height:55dvh;
      overflow-y:auto;
  }
.queue{
  display:grid;gap:8px;
}
.queue-player
{
  align-items:center;
  background:var(--panel2);
  border-left:4px solid var(--blue);
border-radius:9px;
display:grid;
gap:10px;
grid-template-columns:34px 1fr auto;
padding:10px;
}
.queue-player.waiting{
  border-left-color:var(--gold);
}
.position{
  background:#0b1a29;border-radius:50%;
display:grid;font-weight:900;height:28px;place-items:center;width:28px;
}
.games{
  color:var(--green);font-size:.78rem;font-weight:800;text-align:right;
}
.games span{
  color:var(--muted);display:block;font-weight:400;
}
.empty{
  color:var(--muted);padding:30px;text-align:center;
}
.recent{grid-column:1/-1;}
.results{
  display:grid;
  gap:10px;
  grid-template-columns:repeat(4,1fr);
}
.result{
  background:#0d1d2e;
  border:1px solid var(--line);
  border-radius:10px;
  padding:11px;
}
.result small{
  color:var(--muted);
}
.result strong{
  display:block;margin-top:5px;}
.winner{
  color:var(--green);
}
.updated
font-size:.75rem;margin-top:5px;
}
@media(max-width:1100px)
{
  .stats{grid-template-columns:repeat(3,1fr);
}
.layout{
  grid-template-columns:1fr;
}
.results{
  grid-template-columns:repeat(2,1fr);
}
}
@media(max-width:700px){
  .header{align-items:flex-start;flex-direction:column;padding:18px}
  .meta{text-align:left;
  }
  .stats{
    grid-template-columns:repeat(2,1fr);padding:14px 18px 0;
}
.layout{padding:14px 18px;}
.courts,
.results{
  grid-template-columns:1fr;
}
  .versus{
    grid-template-columns:1fr;}
    .vs{text-align:center;padding:2px;
  }
  .section-title{align-items:flex-start;flex-direction:column;gap:10px}.round-tools{flex-wrap:wrap;width:100%}.score-form{grid-template-columns:1fr 1fr}.score-form .action-button{grid-column:1/-1}.round-controls{flex:1}.round-tools>.action-button{flex:1;white-space:nowrap}
}
  </style>
</head>
<body>
  <header class="header">
    <div>
      <div class="live" id="live-state">LIVE PLAY ROTATION</div>
      <h1 id="title">{{ $slot->title ?: __('Open Play Rotation') }}</h1>
      <div id="session"></div>
    </div>
      <div class="meta">
        <div class="clock" id="clock">

        </div>
        <div id="court-name"></div>
        <div class="updated" id="updated"></div>
      </div>
    </header>
  <section class="stats">
    <div class="stat">
      <strong id="round">0</strong>
      <span>{{ __('Current Round') }}</span>
    </div>
    <div class="stat">
      <strong id="active">0</strong><span>{{ __('Active Players') }}</span>
    </div>
    <div class="stat">
      <strong id="on-court">0</strong><span>{{ __('On Court') }}</span>
    </div>
    <div class="stat">
      <strong id="waiting-count">0</strong><span>{{ __('Waiting') }}</span>
    </div>
    <div class="stat">
      <strong id="games">0</strong><span>{{ __('Games Completed') }}</span>
    </div>
  </section>
  <main class="layout" id="monitor" data-endpoint="{{ route('vendor.court_schedule.open_play.rotation_data', $slot->id) }}" data-generate-url="{{ route('vendor.court_schedule.open_play.rounds.generate', $slot->id) }}">
    <section>
      <div class="feedback" id="feedback" role="status"></div>
      <div class="section-title">
        <h2>{{ __('Current Generated Round') }}</h2>
        <div class="round-tools"><span class="badge" id="round-state"></span><div class="round-controls"><label for="court-count">{{ __('Courts') }}</label><input id="court-count" type="number" min="1" max="20" value="1"></div><button class="action-button" id="generate-round" type="button">{{ __('Generate Next Round') }}</button></div>
      </div>
      <div class="courts" id="courts"></div>
    </section>
    <aside class="queue-panel">
      <div class="section-title">
        <h2>{{ __('Next Up & Waiting') }}</h2>
        <span class="badge" id="average"></span>
      </div>
      <div class="queue" id="queue"></div>
    </aside>
    <section class="recent">
      <div class="section-title">
        <h2>{{ __('Recent Results') }}</h2>
      </div>
      <div class="results" id="results"></div>
    </section>
  </main>
  <script>
    (() => {
      const root = document.getElementById('monitor');
      const csrf = document.querySelector('meta[name="csrf-token"]').content;
      const generateButton = document.getElementById('generate-round');
      const feedback = document.getElementById('feedback');
      const esc = value => String(value ?? '').replace(/[&<>"']/g, character => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[character]));
      const player = item => `<div class="player"><strong>${esc(item.name)}</strong><small>${esc(item.category)} · ${item.rating}/10 · ${item.games_completed} game${item.games_completed === 1 ? '' : 's'}</small></div>`;

      function showFeedback(message, type) {
        feedback.textContent = message;
        feedback.className = `feedback ${type}`;
      }

      async function post(url, values) {
        const response = await fetch(url, {
          method: 'POST', credentials: 'same-origin',
          headers: {'Accept':'application/json', 'X-Requested-With':'XMLHttpRequest', 'X-CSRF-TOKEN':csrf, 'Content-Type':'application/json'},
          body: JSON.stringify(values)
        });
        const data = await response.json().catch(() => ({}));
        if (!response.ok) {
          const errors = data.errors ? Object.values(data.errors).flat().join(' ') : data.message;
          throw new Error(errors || @json(__('The action could not be completed.')));
        }
        return data;
      }

      function renderQueue(next, waiting) {
        const rows = [...next.map((item,index) => ({...item,label:'NEXT',position:index+1,next:true})), ...waiting.map((item,index) => ({...item,label:'WAIT',position:next.length+index+1,next:false}))];
        document.getElementById('queue').innerHTML = rows.length ? rows.map(item => `<div class="queue-player ${item.next ? '' : 'waiting'}"><span class="position">${item.position}</span><div><strong>${esc(item.name)}</strong> - <small>${esc(item.category)} · Rating ${item.rating}${item.wait_rounds ? ' · Wait ' + item.wait_rounds : ''}</small></div><div class="games">${item.games_completed} games<span>${item.label}</span></div></div>`).join('') : `<div class="empty">${@json(__('No players are waiting.'))}</div>`;
      }

      function renderMatch(match) {
        console.log('Rendering match:', match);
        return `<article class="court"><div class="court-head"><strong>${@json(__('Court'))} ${match.court_number}</strong><span>${esc(match.skill_group || 'Open')}</span></div><div class="versus"><div class="team"><div class="team-label">${@json(__('Team 1'))}</div>${match.team_one.map(player).join('')}</div><div class="vs">VS</div><div class="team"><div class="team-label">${@json(__('Team 2'))}</div>${match.team_two.map(player).join('')}</div></div><form class="score-form" data-url="${esc(match.complete_url)}"><div class="score-field"><label>${@json(__('Team 1 Score'))}</label><input name="team_one_score" type="number" min="0" max="999" required></div><div class="score-field"><label>${@json(__('Team 2 Score'))}</label><input name="team_two_score" type="number" min="0" max="999" required></div><button class="action-button" type="submit">${@json(__('Save Result'))}</button></form></article>`;
      }

      async function refresh(force = false) {
        if (!force && [...document.querySelectorAll('.score-form input')].some(input => input.value !== '')) return;
        try {
          const response = await fetch(root.dataset.endpoint, {headers:{Accept:'application/json'},credentials:'same-origin'});
          if (!response.ok) throw new Error();
          const data = await response.json();
          document.getElementById('title').textContent = data.title;
          document.getElementById('court-name').textContent = data.court;
          document.getElementById('session').textContent = data.starts_at + ' - ' + data.ends_at;
          document.getElementById('round').textContent = data.current_round || '-';
          document.getElementById('active').textContent = data.active_players;
          document.getElementById('on-court').textContent = data.players_on_court;
          document.getElementById('waiting-count').textContent = data.waiting_count;
          document.getElementById('games').textContent = data.completed_matches;
          document.getElementById('average').textContent = @json(__('Average')) + ' ' + data.average_games + ' ' + @json(__('games/player'));
          document.getElementById('round-state').textContent = data.current_matches.length ? @json(__('In Progress')) : @json(__('Awaiting Next Round'));
          document.getElementById('live-state').textContent = (data.session_status === 'live' ? '● ' : '') + data.session_status.toUpperCase() + ' PLAY ROTATION';
          document.getElementById('courts').innerHTML = data.current_matches.length ? data.current_matches.map(renderMatch).join('') : `<div class="court empty">${@json(__('No active round. Generate the next round below.'))}</div>`;
          generateButton.disabled = data.current_matches.length > 0 || data.active_players < 4;
          if (data.current_matches.length) document.getElementById('court-count').value = data.current_matches.length;
          renderQueue(data.next_up, data.waiting);
          document.getElementById('results').innerHTML = data.recent_results.length ? data.recent_results.map(result => { const one=result.team_one.join(' / '), two=result.team_two.join(' / '); return `<div class="result"><small>${@json(__('Round'))} ${result.round} · ${@json(__('Court'))} ${result.court_number}</small><strong class="${result.winner_team===1?'winner':''}">${esc(one)} ${result.team_one_score ?? '-'}</strong><strong class="${result.winner_team===2?'winner':''}">${esc(two)} ${result.team_two_score ?? '-'}</strong></div>`; }).join('') : `<div class="empty">${@json(__('No completed games yet.'))}</div>`;
          document.getElementById('updated').textContent = @json(__('Updated')) + ' ' + new Date().toLocaleTimeString();
        } catch (error) {
          document.getElementById('updated').textContent = @json(__('Unable to refresh'));
        }
      }

      document.getElementById('courts').addEventListener('submit', async event => {
        if (!event.target.matches('.score-form')) return;
        event.preventDefault();
        const form = event.target;
        const button = form.querySelector('button');
        button.disabled = true;
        try {
          const data = await post(form.dataset.url, {team_one_score:form.team_one_score.value,team_two_score:form.team_two_score.value});
          showFeedback(data.message, 'success');
          await refresh(true);
        } catch (error) {
          showFeedback(error.message, 'error');
          button.disabled = false;
        }
      });

      generateButton.addEventListener('click', async () => {
        generateButton.disabled = true;
        try {
          const data = await post(root.dataset.generateUrl, {court_count:document.getElementById('court-count').value,skill_separated:true});
          showFeedback(data.message, 'success');
          await refresh(true);
        } catch (error) {
          showFeedback(error.message, 'error');
          generateButton.disabled = false;
        }
      });

      setInterval(() => document.getElementById('clock').textContent = new Date().toLocaleTimeString([], {hour:'2-digit',minute:'2-digit',second:'2-digit'}), 1000);
      refresh(); setInterval(refresh, 10000);
    })();
  </script>
</body>
</html>
