<section class="home-pickleball-guide pt-100 pb-70" aria-labelledby="pickleball-guide-title">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-xl-9 col-lg-10 text-center" data-aos="fade-up">
        <span class="subtitle">{{ __('Plan your next game') }}</span>
        <h2 class="title mt-2 mb-3" id="pickleball-guide-title">
          {{ __('Book a Pickleball Court or Join Open Play') }}
        </h2>
        <p class="home-pickleball-guide__lead mb-0">
          {{ __('Find nearby Pickleball Sites, compare their available Courts, and reserve a schedule that works for you. You can rent a Court for your own group or register individually for an Open Play session hosted by a local Site.') }}
        </p>
      </div>
    </div>

    <div class="row g-4 mt-4">
      <div class="col-lg-4 col-md-6" data-aos="fade-up">
        <article class="home-pickleball-guide__step h-100">
          <span class="home-pickleball-guide__number">1</span>
          <h3>{{ __('Find the right Court') }}</h3>
          <p>{{ __('Search by location, date, and time to discover Courts that fit your plans. Each listing provides Site details, Court information, amenities, operating hours, pricing, and available schedules so you can compare before booking.') }}</p>
          <a href="{{ route('frontend.rooms') }}">{{ __('Browse Courts') }} <i class="far fa-long-arrow-right"></i></a>
        </article>
      </div>
      <div class="col-lg-4 col-md-6" data-aos="fade-up">
        <article class="home-pickleball-guide__step h-100">
          <span class="home-pickleball-guide__number">2</span>
          <h3>{{ __('Choose an available schedule') }}</h3>
          <p>{{ __('Select a vacant time directly from the Court availability schedule. Your chosen Court, date, start time, duration, and calculated rental amount are carried into checkout and checked again before the reservation is finalized.') }}</p>
          <a href="{{ route('frontend.hotels') }}">{{ __('Explore Pickleball Sites') }} <i class="far fa-long-arrow-right"></i></a>
        </article>
      </div>
      <div class="col-lg-4 col-md-6" data-aos="fade-up">
        <article class="home-pickleball-guide__step h-100">
          <span class="home-pickleball-guide__number">3</span>
          <h3>{{ __('Confirm with confidence') }}</h3>
          <p>{{ __('Review the total and follow the payment instructions supplied by the Site. When paying through an available e-wallet, verify the recipient details and keep your transaction reference so the Site can identify and confirm your payment.') }}</p>
          <a href="{{ route('faq') }}">{{ __('Read Booking FAQs') }} <i class="far fa-long-arrow-right"></i></a>
        </article>
      </div>
    </div>

    <div class="row g-4 align-items-stretch mt-4">
      <div class="col-lg-7" data-aos="fade-up">
        <article class="home-pickleball-guide__content h-100">
          <h3>{{ __('New to Open Play?') }}</h3>
          <p>
            {{ __('Open Play lets you join a scheduled group session without organizing a complete group or renting the whole Court. The hosting Site publishes the session time, category, price per player, and maximum number of participants. You register the players included in your booking, and the remaining capacity is checked again when checkout is completed.') }}
          </p>
          <p class="mb-0">
            {{ __('Some sessions may organize games by skill level or use a player-rotation format so participants can meet different partners and opponents. Read the session description before registering, enter accurate player information, and arrive early enough to check in with the organizer.') }}
          </p>
        </article>
      </div>
      <div class="col-lg-5" data-aos="fade-up">
        <aside class="home-pickleball-guide__content home-pickleball-guide__checklist h-100">
          <h3>{{ __('Before you go') }}</h3>
          <ul class="list-unstyled mb-0">
            <li><i class="fas fa-check-circle"></i><span>{{ __('Confirm the Site address, Court number, and start time.') }}</span></li>
            <li><i class="fas fa-check-circle"></i><span>{{ __('Review equipment, footwear, and venue-specific rules.') }}</span></li>
            <li><i class="fas fa-check-circle"></i><span>{{ __('Keep your booking number and payment reference available.') }}</span></li>
            <li><i class="fas fa-check-circle"></i><span>{{ __('Contact the Site promptly if your plans change.') }}</span></li>
          </ul>
        </aside>
      </div>
    </div>

    <div class="home-pickleball-guide__owner mt-5" data-aos="fade-up">
      <div>
        <span class="subtitle">{{ __('For Pickleball businesses') }}</span>
        <h3 class="mt-2 mb-2">{{ __('Own or manage a Pickleball Site?') }}</h3>
        <p class="mb-0">
          {{ __('Create a vendor account to publish your Site and Courts, manage availability, receive booking requests, schedule Open Play, and access eligible rotation, tournament, and live-display tools. Sites must be active and approved before they can accept public bookings.') }}
        </p>
      </div>
      <a class="btn btn-lg btn-primary radius-sm" href="{{ route('vendor.signup') }}">{{ __('List Your Site') }}</a>
    </div>
  </div>
</section>

<style>
  .home-pickleball-guide{background:linear-gradient(180deg,#fff 0%,#f7faf8 100%)}
  .home-pickleball-guide__lead{color:var(--text-medium);font-size:1.08rem;line-height:1.8}
  .home-pickleball-guide__step,.home-pickleball-guide__content{background:#fff;border:1px solid rgba(20,80,55,.12);border-radius:16px;padding:30px;box-shadow:0 12px 35px rgba(25,66,50,.07)}
  .home-pickleball-guide__step{position:relative;padding-top:38px}
  .home-pickleball-guide__number{align-items:center;background:var(--color-primary);border-radius:50%;color:#fff;display:flex;font-size:1rem;font-weight:700;height:40px;justify-content:center;left:30px;position:absolute;top:-20px;width:40px}
  .home-pickleball-guide h3{font-size:1.35rem;margin-bottom:14px}
  .home-pickleball-guide p{line-height:1.75}
  .home-pickleball-guide__step a{align-items:center;display:inline-flex;font-weight:700;gap:8px;margin-top:4px}
  .home-pickleball-guide__checklist li{align-items:flex-start;display:flex;gap:11px;margin-top:15px}
  .home-pickleball-guide__checklist i{color:var(--color-primary);margin-top:5px}
  .home-pickleball-guide__owner{align-items:center;background:#102f25;border-radius:18px;color:#fff;display:flex;gap:30px;justify-content:space-between;padding:34px 38px}
  .home-pickleball-guide__owner h3,.home-pickleball-guide__owner p{color:#fff}
  .home-pickleball-guide__owner>div{max-width:780px}
  .home-pickleball-guide__owner .btn{flex:0 0 auto}
  @media(max-width:767px){.home-pickleball-guide__owner{align-items:flex-start;flex-direction:column;padding:28px}.home-pickleball-guide__owner .btn{width:100%}}
</style>
