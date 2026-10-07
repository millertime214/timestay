@extends('admin.layout')

@section('content')
  <div class="page-header">
    <h4 class="page-title">{{ __('Open Play Details') }}</h4>
    <ul class="breadcrumbs">
      <li class="nav-home">
        <a href="{{ route('admin.dashboard') }}"><i class="flaticon-home"></i></a>
      </li>
      <li class="separator"><i class="flaticon-right-arrow"></i></li>
      <li class="nav-item"><a href="{{ route('admin.court_schedule.open_play.index', ['language' => optional($language)->code]) }}">{{ __('Open Play Sessions') }}</a></li>
      <li class="separator"><i class="flaticon-right-arrow"></i></li>
      <li class="nav-item"><a href="#">{{ __('Open Play Details') }}</a></li>
    </ul>
    <a href="{{ route('admin.court_schedule.open_play.index', ['language' => optional($language)->code]) }}" class="btn btn-primary btn-sm ml-auto">{{ __('Back') }}</a>
  </div>

  @include('shared.open-play.roster')
@endsection