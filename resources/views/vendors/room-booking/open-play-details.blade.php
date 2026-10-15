@extends('vendors.layout')

@section('style')
  <style>
    .open-play-tabs{
      border:1px solid #e3e8ef;border-radius:14px;box-shadow:0 8px 24px rgba(23,43,77,.06);overflow:hidden;
  }.open-play-tabs>.card-header{background:#f8fafc;border-bottom:1px solid #e3e8ef;}
  .open-play-tabs__nav{display:flex;flex-wrap:nowrap;gap:5px;overflow-x:visible;padding:8px 8px 0;}
  .open-play-tabs__nav .nav-item{flex:1 0 auto}.open-play-tabs__nav .nav-link{align-items:center;border:0;border-bottom:3px solid transparent;border-radius:9px 9px 0 0;color:#5b6678;display:flex;font-weight:700;gap:5px;height:100%;justify-content:center;min-height:50px;padding:11px 16px;transition:background-color .2s ease,color .2s ease,border-color .2s ease}.open-play-tabs__nav .nav-link:hover,.open-play-tabs__nav .nav-link:focus{background:#edf4ff;color:#1269db}.open-play-tabs__nav .nav-link.active{background:#fff;border-bottom-color:#1572e8;color:#1269db}.open-play-tabs__body{background:#f5f7fb;padding:22px}.open-play-tabs__body .tab-pane>.card:last-child,.open-play-tabs__body .tab-pane>.row:last-child{margin-bottom:0!important}.open-play-tabs__body .card{border-color:#e3e8ef;box-shadow:0 3px 12px rgba(23,43,77,.05)}
    body[data-background-color="dark"] .open-play-tabs{background:#17212d;border-color:#344151;box-shadow:0 10px 28px rgba(0,0,0,.28)}body[data-background-color="dark"] .open-play-tabs>.card-header{background:#141d27;border-color:#344151}body[data-background-color="dark"] .open-play-tabs__nav .nav-link{color:#aebccc}body[data-background-color="dark"] .open-play-tabs__nav .nav-link:hover,body[data-background-color="dark"] .open-play-tabs__nav .nav-link:focus{background:#223248;color:#7db8ff}body[data-background-color="dark"] .open-play-tabs__nav .nav-link.active{background:#202c3a;border-bottom-color:#55a4ff;color:#fff}body[data-background-color="dark"] .open-play-tabs__body{background:#111922}body[data-background-color="dark"] .open-play-tabs__body .card{background:#1d2936;border-color:#344151;color:#dce5ef}body[data-background-color="dark"] .open-play-tabs__body .card-header{background:#202e3c;border-color:#344151}body[data-background-color="dark"] .open-play-tabs__body .table{color:#dce5ef}body[data-background-color="dark"] .open-play-tabs__body .table td,body[data-background-color="dark"] .open-play-tabs__body .table th{border-color:#344151}body[data-background-color="dark"] .open-play-tabs__body .table-striped tbody tr:nth-of-type(odd){background:rgba(255,255,255,.025)}body[data-background-color="dark"] .open-play-tabs__body .border{border-color:#3b4959!important}body[data-background-color="dark"] .open-play-tabs__body .alert-light{background:#263442;border-color:#435267;color:#dce5ef}body[data-background-color="dark"] .open-play-tabs__body .text-muted{color:#9eacbc!important}
    @media(max-width:767.98px){.open-play-tabs__nav .nav-item{flex:0 0 auto}.open-play-tabs__nav .nav-link{justify-content:flex-start;min-height:46px;white-space:nowrap}.open-play-tabs__body{padding:14px}}
  </style>
@endsection

@section('content')
  <div class="page-header">
    <h4 class="page-title">{{ __('Open Play Details') }}</h4>
    <ul class="breadcrumbs">
      <li class="nav-home"><a href="{{ route('vendor.dashboard') }}"><i class="flaticon-home"></i></a></li>
      <li class="separator"><i class="flaticon-right-arrow"></i></li>
      <li class="nav-item"><a href="{{ route('vendor.court_schedule.index', ['language' => optional($language)->code]) }}">{{ __('Court Schedule') }}</a></li>
      <li class="separator"><i class="flaticon-right-arrow"></i></li>
      <li class="nav-item"><a href="#">{{ __('Open Play Details') }}</a></li>
    </ul>
    <a href="{{ route('vendor.court_schedule.index', ['language' => optional($language)->code]) }}" class="btn btn-primary btn-sm ml-auto">{{ __('Back') }}</a>
  </div>

  <div class="card open-play-tabs">
    <div class="card-header p-0">
      <ul class="nav nav-tabs border-0 open-play-tabs__nav" id="openPlayDetailTabs" role="tablist">
        <li class="nav-item"><a class="nav-link active" id="open-play-overview-tab" data-toggle="tab" href="#open-play-overview" role="tab" aria-controls="open-play-overview" aria-selected="true"><i class="fas fa-users mr-1"></i>{{ __('Open Play Details & Registered Players') }}</a></li>
        <li class="nav-item"><a class="nav-link" id="open-play-matchmaker-tab" data-toggle="tab" href="#open-play-matchmaker" role="tab" aria-controls="open-play-matchmaker" aria-selected="false"><i class="fas fa-random mr-1"></i>{{ __('Pickleball Match Maker & Play Rotation') }}</a></li>
        <li class="nav-item"><a class="nav-link" id="open-play-history-tab" data-toggle="tab" href="#open-play-history" role="tab" aria-controls="open-play-history" aria-selected="false"><i class="fas fa-history mr-1"></i>{{ __('Previous Tournament History') }}</a></li>
      </ul>
    </div>
    <div class="card-body open-play-tabs__body">
      <div class="tab-content" id="openPlayDetailTabContent">
        <div class="tab-pane fade show active" id="open-play-overview" role="tabpanel" aria-labelledby="open-play-overview-tab">@include('shared.open-play.roster')</div>
        <div class="tab-pane fade" id="open-play-matchmaker" role="tabpanel" aria-labelledby="open-play-matchmaker-tab">@include('vendors.room-booking.partials.open-play-matchmaker')</div>
        <div class="tab-pane fade" id="open-play-history" role="tabpanel" aria-labelledby="open-play-history-tab">@include('vendors.room-booking.partials.open-play-tournament-history')</div>
      </div>
    </div>
  </div>
@endsection

@section('script')
  <script>
    (function ($) {
      const hash = window.location.hash;
      if (hash === '#generated-rounds' || hash === '#open-play-matchmaker') {
        $('#open-play-matchmaker-tab').tab('show');
      } else if (hash === '#open-play-history') {
        $('#open-play-history-tab').tab('show');
      } else if (@json($errors->any())) {
        $('#open-play-matchmaker-tab').tab('show');
      }
      $('#openPlayDetailTabs a[data-toggle="tab"]').on('shown.bs.tab', function (event) {
        if (window.history && window.history.replaceState) window.history.replaceState(null, '', event.target.getAttribute('href'));
      });
    })(jQuery);
  </script>
@endsection
