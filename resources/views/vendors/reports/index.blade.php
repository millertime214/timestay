@extends('vendors.layout')

@section('content')
  <div class="page-header">
    <h4 class="page-title">{{ __('Reports') }}</h4>
    <ul class="breadcrumbs">
      <li class="nav-home"><a href="{{ route('vendor.dashboard') }}"><i class="flaticon-home"></i></a></li>
      <li class="separator"><i class="flaticon-right-arrow"></i></li>
      <li class="nav-item"><a href="#">{{ __('Booking and Sales Reports') }}</a></li>
    </ul>
  </div>

  <div class="card mb-4">
    <div class="card-header"><div class="card-title">{{ __('Report Filters') }}</div></div>
    <div class="card-body">
      <form method="GET" action="{{ route('vendor.reports.index') }}">
        <input type="hidden" name="language" value="{{ request('language', $defaultLang->code) }}">
        <div class="row">
          <div class="col-lg-2 col-md-4 mb-3"><label>{{ __('From') }}</label><input class="form-control" type="date" name="date_from" value="{{ $filters['date_from'] }}"></div>
          <div class="col-lg-2 col-md-4 mb-3"><label>{{ __('To') }}</label><input class="form-control" type="date" name="date_to" value="{{ $filters['date_to'] }}"></div>
          <div class="col-lg-2 col-md-4 mb-3">
            <label>{{ __('Court') }}</label>
            <select class="form-control" name="room_id"><option value="">{{ __('All Courts') }}</option>
              @foreach ($courts as $court)<option value="{{ $court->id }}" @selected((string) $filters['room_id'] === (string) $court->id)>{{ $court->title }}</option>@endforeach
            </select>
          </div>
          <div class="col-lg-2 col-md-4 mb-3">
            <label>{{ __('Payment Status') }}</label>
            <select class="form-control" name="payment_status">
              <option value="">{{ __('All Statuses') }}</option>
              <option value="1" @selected((string) $filters['payment_status'] === '1')>{{ __('Paid') }}</option>
              <option value="0" @selected((string) $filters['payment_status'] === '0')>{{ __('Pending') }}</option>
              <option value="2" @selected((string) $filters['payment_status'] === '2')>{{ __('Rejected') }}</option>
            </select>
          </div>
          <div class="col-lg-2 col-md-4 mb-3">
            <label>{{ __('Payment Method') }}</label>
            <select class="form-control" name="payment_method"><option value="">{{ __('All Methods') }}</option>
              @foreach ($paymentMethodOptions as $method)<option value="{{ $method }}" @selected($filters['payment_method'] === $method)>{{ __($method) }}</option>@endforeach
            </select>
          </div>
          <div class="col-lg-2 col-md-4 mb-3">
            <label>{{ __('Booking Type') }}</label>
            <select class="form-control" name="classification"><option value="">{{ __('All Types') }}</option>
              @foreach (['private' => 'Private', 'exclusive' => 'Exclusive', 'open_play' => 'Open Play', 'training' => 'Training'] as $value => $label)
                <option value="{{ $value }}" @selected($filters['classification'] === $value)>{{ __($label) }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <button class="btn btn-primary" type="submit"><i class="fas fa-filter mr-1"></i>{{ __('Apply Filters') }}</button>
        <a class="btn btn-secondary" href="{{ route('vendor.reports.index', ['language' => $defaultLang->code]) }}">{{ __('Reset') }}</a>
        <a class="btn btn-success float-right" href="{{ route('vendor.reports.export', request()->query()) }}"><i class="fas fa-file-csv mr-1"></i>{{ __('Export CSV') }}</a>
      </form>
    </div>
  </div>

  <div class="row">
    @foreach ([
      ['Booking Sales', symbolPrice($summary['gross_sales']), 'fa-chart-line', 'success'],
      ['Net Payments Received', symbolPrice($summary['payments_received']), 'fa-wallet', 'primary'],
      ['Outstanding Payments', symbolPrice($summary['outstanding']), 'fa-hourglass-half', 'warning'],
      ['Commission / Fees', symbolPrice($summary['commission']), 'fa-percentage', 'danger'],
      ['Total Bookings', number_format($summary['total_bookings']), 'fa-calendar-check', 'info'],
      ['Booked Hours', number_format($summary['booked_hours'], 1), 'fa-clock', 'secondary'],
      ['Average Paid Booking', symbolPrice($summary['average_booking']), 'fa-receipt', 'success'],
      ['Rejected Bookings', number_format($summary['rejected_bookings']), 'fa-times-circle', 'danger'],
    ] as [$label, $value, $icon, $color])
      <div class="col-xl-3 col-md-6"><div class="card card-stats card-{{ $color }} card-round mb-4"><div class="card-body"><div class="row"><div class="col-5"><div class="icon-big text-center"><i class="fas {{ $icon }}"></i></div></div><div class="col-7 col-stats"><div class="numbers"><p class="card-category">{{ __($label) }}</p><h4 class="card-title">{{ $value }}</h4></div></div></div></div></div></div>
    @endforeach
  </div>

  <div class="row">
    <div class="col-lg-7"><div class="card"><div class="card-header"><div class="card-title">{{ __('Court Sales') }}</div></div><div class="card-body table-responsive">
      <table class="table table-striped"><thead><tr><th>{{ __('Court') }}</th><th>{{ __('Bookings') }}</th><th>{{ __('Hours') }}</th><th>{{ __('Paid Sales') }}</th><th>{{ __('Average Sale') }}</th></tr></thead><tbody>
        @forelse ($courtSales as $row)<tr><td>{{ $row->court_title }}</td><td>{{ $row->booking_count }}</td><td>{{ number_format($row->booked_hours, 1) }}</td><td>{{ symbolPrice($row->sales) }}</td><td>{{ symbolPrice($row->paid_booking_count ? $row->sales / $row->paid_booking_count : 0) }}</td></tr>@empty<tr><td colspan="5" class="text-center">{{ __('No report data found.') }}</td></tr>@endforelse
      </tbody></table>
    </div></div></div>
    <div class="col-lg-5"><div class="card"><div class="card-header"><div class="card-title">{{ __('Payments Received by Method') }}</div></div><div class="card-body table-responsive">
      <table class="table table-striped"><thead><tr><th>{{ __('Method') }}</th><th>{{ __('Bookings') }}</th><th>{{ __('Amount') }}</th></tr></thead><tbody>
        @forelse ($paymentMethods as $row)<tr><td>{{ __($row->label) }}</td><td>{{ $row->booking_count }}</td><td>{{ symbolPrice($row->total) }}</td></tr>@empty<tr><td colspan="3" class="text-center">{{ __('No payment data found.') }}</td></tr>@endforelse
      </tbody></table>
    </div></div></div>
  </div>

  <div class="row">
    <div class="col-lg-6"><div class="card"><div class="card-header"><div class="card-title">{{ __('Booking Types') }}</div></div><div class="card-body table-responsive"><table class="table table-striped"><thead><tr><th>{{ __('Type') }}</th><th>{{ __('Bookings') }}</th><th>{{ __('Paid Sales') }}</th></tr></thead><tbody>
      @forelse ($classifications as $row)<tr><td>{{ __(ucwords(str_replace('_', ' ', $row->label))) }}</td><td>{{ $row->booking_count }}</td><td>{{ symbolPrice($row->total) }}</td></tr>@empty<tr><td colspan="3" class="text-center">{{ __('No booking data found.') }}</td></tr>@endforelse
    </tbody></table></div></div></div>
    <div class="col-lg-6"><div class="card"><div class="card-header"><div class="card-title">{{ __('Daily Paid Sales') }}</div></div><div class="card-body table-responsive"><table class="table table-striped"><thead><tr><th>{{ __('Date') }}</th><th>{{ __('Bookings') }}</th><th>{{ __('Sales') }}</th></tr></thead><tbody>
      @forelse ($dailySales as $row)<tr><td>{{ Carbon\Carbon::parse($row->report_date)->format('M d, Y') }}</td><td>{{ $row->booking_count }}</td><td>{{ symbolPrice($row->total) }}</td></tr>@empty<tr><td colspan="3" class="text-center">{{ __('No sales data found.') }}</td></tr>@endforelse
    </tbody></table></div></div></div>
  </div>

  <div class="card"><div class="card-header"><div class="card-title">{{ __('Booking Sales Ledger') }}</div></div><div class="card-body table-responsive">
    <table class="table table-striped"><thead><tr><th>{{ __('Booking No.') }}</th><th>{{ __('Court') }}</th><th>{{ __('Schedule') }}</th><th>{{ __('Customer') }}</th><th>{{ __('Type') }}</th><th>{{ __('Payment') }}</th><th>{{ __('Amount') }}</th></tr></thead><tbody>
      @forelse ($bookings as $booking)
        @php($courtContent = $booking->hotelRoom?->room_content?->first())
        <tr><td>#{{ $booking->order_number }}</td><td>{{ $courtContent->title ?? __('Deleted Court') }}</td><td>{{ Carbon\Carbon::parse($booking->check_in_date)->format('M d, Y') }} {{ Carbon\Carbon::parse($booking->check_in_time)->format($settings->time_format == 24 ? 'H:i' : 'h:i A') }}<br><small>{{ $booking->hour }} {{ __('hours') }}</small></td><td>{{ $booking->booking_name }}</td><td>{{ __(ucwords(str_replace('_', ' ', $booking->classification ?: 'private'))) }}</td><td><span class="badge badge-{{ $booking->payment_status == 1 ? 'success' : ($booking->payment_status == 0 ? 'warning' : 'danger') }}">{{ $booking->payment_status == 1 ? __('Paid') : ($booking->payment_status == 0 ? __('Pending') : __('Rejected')) }}</span><br><small>{{ __($booking->payment_method) }}</small></td><td>{{ symbolPrice($booking->grand_total) }}</td></tr>
      @empty<tr><td colspan="7" class="text-center">{{ __('No bookings found for the selected filters.') }}</td></tr>@endforelse
    </tbody></table>
  </div><div class="card-footer">{{ $bookings->links() }}</div></div>
@endsection
