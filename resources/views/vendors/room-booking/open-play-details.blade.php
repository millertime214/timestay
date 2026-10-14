@extends('vendors.layout')

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

  @include('shared.open-play.roster')
  @include('vendors.room-booking.partials.open-play-matchmaker')
@endsection
