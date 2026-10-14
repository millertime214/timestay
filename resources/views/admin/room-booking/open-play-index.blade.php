@extends('admin.layout')

@section('content')
  <div class="page-header">
    <h4 class="page-title">{{ __('Open Play Sessions') }}</h4>
    <ul class="breadcrumbs">
      <li class="nav-home"><a href="{{ route('admin.dashboard') }}"><i class="flaticon-home"></i></a></li>
      <li class="separator"><i class="flaticon-right-arrow"></i></li>
      <li class="nav-item"><a href="{{ route('admin.room_bookings.all_bookings', ['language' => optional($language)->code]) }}">{{ __('Court Bookings') }}</a></li>
      <li class="separator"><i class="flaticon-right-arrow"></i></li>
      <li class="nav-item"><a href="#">{{ __('Open Play Sessions') }}</a></li>
    </ul>
  </div>

  <div class="card">
    <div class="card-header">
      <div class="card-title">{{ __('Open Play Sessions') }}</div>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-striped">
          <thead>
            <tr>
              <th>{{ __('Open Play') }}</th>
              <th>{{ __('Court') }}</th>
              <th>{{ __('Vendor / Package Access') }}</th>
              <th>{{ __('Schedule') }}</th>
              <th>{{ __('Per Player') }}</th>
              <th>{{ __('Registrations') }}</th>
              <th>{{ __('Action') }}</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($slots as $slot)
              @php
                $courtContent = optional($slot->court)->room_content
                    ? $slot->court->room_content->where('language_id', optional($language)->id)->first() ?: $slot->court->room_content->first()
                    : null;
              @endphp
              <tr>
                <td><strong>{{ $slot->title ?: __('Open Play') }}</strong>@if ($slot->category)<br><span class="badge badge-info">{{ $slot->category }}</span>@endif</td>
                <td>{{ optional($courtContent)->title ?: __('Court') . ' #' . $slot->room_id }}</td>
                <td>
                  <strong>{{ optional($slot->vendor)->username ?: __('Vendor') . ' #' . $slot->vendor_id }}</strong><br>
                  @if (\App\Http\Helpers\VendorPermissionHelper::hasPackageFeature((int) $slot->vendor_id, 'Open Play'))
                    <span class="badge badge-success">{{ __('Included') }}</span>
                  @else
                    <span class="badge badge-warning">{{ __('Not Included') }}</span>
                  @endif
                </td>
                <td>{{ $slot->starts_at->format('M d @ h:i A') }}<br>{{ __('to') }} {{ $slot->ends_at->format(' h:i A') }}</td>
                <td>{{ symbolPrice($slot->price_per_player) }}</td>
                <td>{{ $slot->registrations_count }}</td>
                <td>
                  <a class="btn btn-primary btn-sm" href="{{ route('admin.court_schedule.open_play.show', ['id' => $slot->id, 'language' => optional($language)->code]) }}">{{ __('View Players') }}</a>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="7" class="text-center">{{ __('No Open Play sessions found.') }}</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <div class="mt-3">{{ $slots->appends(request()->query())->links() }}</div>
    </div>
  </div>
@endsection
