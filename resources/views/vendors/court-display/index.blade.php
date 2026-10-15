@extends('vendors.layout')

@section('content')
  <div class="court-display" id="court-display" data-endpoint="{{ route('vendor.court_display.data') }}">
    <header class="court-display__header">
      <div class="d-flex">
        <div>
        <div class="court-display__eyebrow">{{ __('Live Operations') }}</div>
        <h1>{{ __('Court Display') }}</h1>
        </div>
        <div class="court-display__logo" aria-label="{{ __('Site logo') }}">
        @if (!empty($site->logo))<img src="{{ asset('assets/img/hotel/logo/' . $site->logo) }}" alt="{{ optional($site->hotel_contents->first())->title ?: __('Site logo') }}">@else<span>{{ optional(optional($site)->hotel_contents?->first())->title ?: ($websiteInfo->website_title ?? config('app.name')) }}</span>@endif
      </div>
      </div>
      <div class="court-display__summary">
        <div><strong id="active-count">0</strong><span>{{ __('Active') }}</span></div>
        <div><strong id="available-count">0</strong><span>{{ __('Available') }}</span></div>
        <div><strong id="upcoming-count">0</strong><span>{{ __('Upcoming') }}</span></div>
      </div>
      
      <div class="court-display__clock w-50" style="min-width:200px;">
        <strong id="display-time">--:--:--</strong>
        <span id="display-date">---</span>
      </div>
      <div class="court-display__actions">
        <button type="button" id="fullscreen-toggle" title="{{ __('Toggle Full Screen') }}"><i class="fas fa-expand"></i></button>
        <a href="{{ route('vendor.dashboard') }}" title="{{ __('Exit Display') }}"><i class="fas fa-times"></i></a>
      </div>
    </header>

    <main class="court-display__grid" id="court-grid">
      <div class="court-display__loading"><i class="fas fa-spinner fa-spin"></i> {{ __('Loading live court schedule...') }}</div>
    </main>

    <footer class="court-display__footer">
      <span><i class="fas fa-circle court-display__live-dot"></i> {{ __('Live display') }}</span>
      <span id="last-updated">{{ __('Waiting for data') }}</span>
    </footer>
  </div>
@endsection

@section('style')
  <style>
    .court-display{
      position:fixed;
      inset:0;
      z-index:9999;
      background:#07111f;
      color:#eef6ff;
      display:flex;
      flex-direction:column;
      font-family:"DM Sans",sans-serif;
      overflow:hidden;
      }
      .court-display__header{
        display:grid;
        grid-template-columns:minmax(230px,1fr) auto auto auto auto;
        gap:28px;
        align-items:center;
        padding:22px 30px;
        background:#0b1b2e;
        border-bottom:1px solid #20354e;
      }
      .court-display__logo{align-items:center;display:flex;justify-content:center;min-width:120px}.court-display__logo img{display:block;max-height:64px;max-width:185px;object-fit:contain}.court-display__logo span{color:#fff;font-size:1rem;font-weight:900;letter-spacing:.07em;text-align:center;text-transform:uppercase}
      .court-display__eyebrow{
        color:#5dc4ff;
        font-size:.78rem;
        font-weight:800;
        letter-spacing:.16em;
        text-transform:uppercase;
      }.court-display h1{
        font-size:2rem;
        line-height:1.1;margin:3px 0 0;
        color:#fff;
    }
      .court-display__summary{
        display:flex;
        gap:10px;
      }
      .court-display__summary div{
        min-width:92px;
        padding:9px 14px;
        border-radius:12px;
        background:#12263d;
        text-align:center;
      }
        .court-display__summary strong{
          display:block;
          font-size:1.55rem;
          line-height:1;
          color:#fff;
        }
        .court-display__summary span{
          display:block;
          margin-top:4px;
          color:#92a9c1;
          font-size:.72rem;
          text-transform:uppercase;
          letter-spacing:.08em;
        }
          .court-display__clock{
            text-align:right;
        }.court-display__clock strong{
          display:block;
          font-size:1.9rem;
          line-height:1;
          color:#fff;
          font-variant-numeric:tabular-nums;
        }
        .court-display__clock span{
          color:#92a9c1;
        }.court-display__actions{
          display:flex;
          gap:8px;
      }
      .court-display__actions button,
      .court-display__actions a
      {
        width:42px;height:42px;
        border:0;
        border-radius:10px;
        background:#19314c;
        color:#fff;
        display:grid;
        place-items:center;cursor:pointer;
      }
      .court-display__grid{
        flex:1;
        min-height:0;
        overflow:auto;
        display:grid;
        grid-template-columns:repeat(auto-fit,minmax(340px,1fr));
        grid-auto-rows:minmax(300px,1fr);gap:18px;padding:20px 24px;
      }
      .court-card{background:#0e2035;border:2px solid #1d3855;
      border-radius:18px;padding:22px;display:flex;flex-direction:column;min-height:300px;
    }.court-card.is-active{
      border-color:#f6a723;
      box-shadow:0 0 0 1px rgba(246,167,35,.16),0 18px 50px rgba(0,0,0,.2);
    }
    .court-card.is-available{
      border-color:#22b573;
    }
    .court-card__top{
      display:flex;
      justify-content:space-between;
      align-items:flex-start;
      gap:12px;
    }
    .court-card__name{
      margin:0;color:#fff;font-size:1.65rem;
    }.court-card__badge{
      padding:7px 11px;
      border-radius:999px;
      background:#17432f;
      color:#70e7a8;
      font-weight:800;
      font-size:.72rem;
      letter-spacing:.08em;
      text-transform:uppercase;
    }
    .is-active .court-card__badge{
      background:#503815;color:#ffd27b;
    }
    .court-card__main{
      padding:26px 0 20px;
    }
    .court-card__label{color:#92a9c1;font-size:.8rem;font-weight:800;text-transform:uppercase;letter-spacing:.1em;
  }.court-card__booking{
    font-size:1.45rem;font-weight:800;color:#fff;margin-top:4px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
}.court-card__countdown{font-size:2.8rem;font-weight:900;color:#ffc45b;line-height:1.1;margin-top:8px;font-variant-numeric:tabular-nums}.court-card__available{font-size:2.5rem;font-weight:900;color:#55dc98;margin-top:12px}.court-card__period{color:#b6c6d8;margin-top:5px}.court-card__upcoming{margin-top:auto;border-top:1px solid #213a55;padding-top:15px}.court-card__upcoming-title{color:#7892ad;font-size:.75rem;font-weight:800;text-transform:uppercase;letter-spacing:.1em;margin-bottom:8px}.court-card__next{display:grid;grid-template-columns:82px minmax(0,1fr) auto;gap:10px;color:#dce8f4;padding:5px 0}.court-card__next span:nth-child(2){white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.court-card__type{color:#7f9ab5;font-size:.72rem;text-transform:uppercase}.court-card__empty{color:#7892ad}.court-display__loading{grid-column:1/-1;display:grid;place-items:center;font-size:1.2rem;color:#91a9c0}.court-display__footer{display:flex;justify-content:space-between;padding:9px 28px;background:#091827;color:#7892ad;font-size:.78rem}.court-display__live-dot{font-size:.55rem;color:#30d985;margin-right:5px}@media(max-width:1100px){.court-display__header{grid-template-columns:1fr auto}.court-display__logo{grid-column:1/-1;grid-row:1}.court-display__header>div:first-child{grid-row:2}.court-display__summary{grid-column:1/-1;grid-row:3}.court-display__clock{display:none}.court-display__actions{grid-column:2;grid-row:2}.court-display__grid{grid-template-columns:1fr;grid-auto-rows:auto}.court-card{min-height:330px}}@media(min-width:1600px){.court-display__grid{grid-template-columns:repeat(3,1fr)}.court-card__name{font-size:2rem}.court-card__countdown{font-size:3.4rem}}
  </style>
@endsection

@section('script')
  <script>
    (() => {
      const root = document.getElementById('court-display');
      const grid = document.getElementById('court-grid');
      const labels = {
        available: @json(__('Available')),
        active: @json(__('Active Now')),
        remaining: @json(__('Remaining')),
        upcoming: @json(__('Upcoming Reservations')),
        none: @json(__('No upcoming reservations in the next 24 hours.')),
        updated: @json(__('Updated')),
        error: @json(__('Live schedule could not be refreshed. Retrying...'))
      };
      let latestData = null;
      let nextBoundary = null;
      let displayTimezone = 'Asia/Manila';
      // Absolute timestamps keep booking state correct across server/browser timezones.
      const now = () => Date.now();
      const countdown = endTimestamp => {
        const seconds = Math.max(0, Math.floor((Number(endTimestamp) - now()) / 1000));
        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        const remainder = seconds % 60;
        return [hours, minutes, remainder].map(value => String(value).padStart(2, '0')).join(':');
      };
      const element = (tag, className, text) => {
        const node = document.createElement(tag);
        if (className) node.className = className;
        if (text !== undefined) node.textContent = text;
        return node;
      };

      const render = data => {
        latestData = data;
        displayTimezone = data.timezone || displayTimezone;
        const currentTime = now();
        const upcomingLimit = currentTime + (24 * 60 * 60 * 1000);
        nextBoundary = null;
        const courts = data.courts.map(court => {
          const events = (court.events || []).filter(item => Number(item.ends_at_timestamp) > currentTime);
          const active = events.find(item => Number(item.starts_at_timestamp) <= currentTime && Number(item.ends_at_timestamp) > currentTime) || null;
          const upcoming = events.filter(item => Number(item.starts_at_timestamp) > currentTime && Number(item.starts_at_timestamp) <= upcomingLimit).slice(0, 3);
          events.forEach(item => {
            [Number(item.starts_at_timestamp), Number(item.ends_at_timestamp)].forEach(boundary => {
              if (boundary > currentTime && (nextBoundary === null || boundary < nextBoundary)) nextBoundary = boundary;
            });
          });
          return {...court, active, upcoming};
        });
        const activeCount = courts.filter(court => court.active).length;
        const upcomingCount = courts.reduce((total, court) => total + court.upcoming.length, 0);
        document.getElementById('active-count').textContent = activeCount;
        document.getElementById('available-count').textContent = Math.max(courts.length - activeCount, 0);
        document.getElementById('upcoming-count').textContent = upcomingCount;
        grid.replaceChildren();

        courts.forEach(court => {
          const card = element('article', `court-card ${court.active ? 'is-active' : 'is-available'}`);
          const top = element('div', 'court-card__top');
          top.append(element('h2', 'court-card__name', court.name), element('span', 'court-card__badge', court.active ? labels.active : labels.available));
          card.append(top);

          const main = element('div', 'court-card__main');
          if (court.active) {
            main.append(element('div', 'court-card__label', court.active.type.replace('_', ' ')));
            main.append(element('div', 'court-card__booking', court.active.label));
            const timer = element('div', 'court-card__countdown');
            timer.dataset.endsAt = court.active.ends_at_timestamp;
            timer.textContent = countdown(court.active.ends_at_timestamp);
            main.append(timer, element('div', 'court-card__period', `${labels.remaining} · ${court.active.start_label} – ${court.active.end_label}`));
          } else {
            main.append(element('div', 'court-card__available', labels.available));
          }
          card.append(main);

          const upcoming = element('div', 'court-card__upcoming');
          upcoming.append(element('div', 'court-card__upcoming-title', labels.upcoming));
          if (court.upcoming.length) {
            court.upcoming.forEach(item => {
              const row = element('div', 'court-card__next');
              row.append(element('strong', '', item.start_label), element('span', '', item.label), element('span', 'court-card__type', item.type.replace('_', ' ')));
              upcoming.append(row);
            });
          } else {
            upcoming.append(element('div', 'court-card__empty', labels.none));
          }
          card.append(upcoming);
          grid.append(card);
        });

        if (!courts.length) grid.append(element('div', 'court-display__loading', @json(__('No active courts are configured.'))));
        document.getElementById('last-updated').textContent = `${labels.updated}: ${new Date(now()).toLocaleTimeString()}`;
      };

      const refresh = async () => {
        try {
          const response = await fetch(root.dataset.endpoint, {headers: {'Accept': 'application/json'}, credentials: 'same-origin'});
          if (!response.ok) throw new Error('Request failed');
          render(await response.json());
        } catch (error) {
          document.getElementById('last-updated').textContent = labels.error;
        }
      };

      const tick = () => {
        const current = new Date(now());
        document.getElementById('display-time').textContent = current.toLocaleTimeString([], {hour: '2-digit', minute: '2-digit', second: '2-digit', timeZone: displayTimezone});
        document.getElementById('display-date').textContent = current.toLocaleDateString([], {weekday: 'long', month: 'short', day: 'numeric', timeZone: displayTimezone});
        document.querySelectorAll('[data-ends-at]').forEach(node => node.textContent = countdown(node.dataset.endsAt));
        if (latestData && nextBoundary !== null && now() >= nextBoundary) render(latestData);
      };

      document.getElementById('fullscreen-toggle').addEventListener('click', () => {
        if (!document.fullscreenElement) root.requestFullscreen?.(); else document.exitFullscreen?.();
      });
      refresh();
      tick();
      window.setInterval(tick, 1000);
      window.setInterval(refresh, 15000);
    })();
  </script>
@endsection
