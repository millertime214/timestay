<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>{{ __('Court Booking Invoice') }} | {{ config('app.name') }}</title>
  <style>
    @page{margin:28px 34px}*{box-sizing:border-box}body{margin:0;color:#243447;font-family:DejaVu Sans,Arial,sans-serif;font-size:10px;line-height:1.35}table{width:100%;border-collapse:collapse}.header{border-bottom:2px solid #1766c2;margin-bottom:16px;padding-bottom:12px}.header td{vertical-align:middle}.logo{max-height:58px;max-width:170px}.invoice-heading{color:#13283f;font-size:22px;font-weight:700;letter-spacing:.5px;margin:0 0 2px;text-align:right}.invoice-subtitle{color:#6c7b8a;font-size:9px;letter-spacing:1px;text-align:right;text-transform:uppercase}.reference{background:#f3f7fb;border:1px solid #d9e3ed;margin-bottom:14px}.reference td{border-right:1px solid #d9e3ed;padding:8px 10px;vertical-align:top;width:33.33%}.reference td:last-child{border-right:0}.label{color:#6f7f8f;display:block;font-size:8px;font-weight:700;letter-spacing:.5px;margin-bottom:2px;text-transform:uppercase}.value{color:#172b42;font-size:10px;font-weight:700}.section-title{border-bottom:1px solid #d9e3ed;color:#1766c2;font-size:9px;font-weight:700;letter-spacing:.7px;margin:0 0 8px;padding-bottom:4px;text-transform:uppercase}.schedule{border:1px solid #d9e3ed;margin-bottom:14px}.schedule td{padding:9px 10px;vertical-align:top}.schedule .court{background:#1766c2;color:#fff;width:36%}.schedule .court .label,.schedule .court .value{color:#fff}.schedule .time{border-left:1px solid #d9e3ed;width:32%}.details{margin-bottom:14px}.details>tbody>tr>td{vertical-align:top;width:50%}.details>tbody>tr>td:first-child{padding-right:8px}.details>tbody>tr>td:last-child{padding-left:8px}.card{border:1px solid #d9e3ed;min-height:96px;padding:9px 10px}.info{margin-bottom:5px}.status{border-radius:10px;display:inline-block;font-size:8px;font-weight:700;padding:2px 8px;text-transform:uppercase}.paid{background:#daf3e5;color:#18794e}.unpaid{background:#fde5e5;color:#b42318}.charges-wrap{margin-left:38%;width:62%}.charges{border:1px solid #d9e3ed}.charges td{border-bottom:1px solid #e5ebf1;padding:6px 9px}.charges .amount{font-weight:700;text-align:right;white-space:nowrap}.charges .discount{color:#b42318}.charges .subtotal td{border-top:1px solid #bfcbd7;font-weight:700}.charges .total td{background:#13283f;border:0;color:#fff;font-size:12px;font-weight:700;padding:9px}.footer{border-top:1px solid #d9e3ed;color:#718090;font-size:8px;margin-top:18px;padding-top:8px;text-align:center}
  </style>
</head>
<body>
@php
  $position = $bookingInfo->currency_text_position;
  $currency = $bookingInfo->currency_text;
  $money = fn($amount) => $position === 'left' ? $currency . ' ' . number_format((float) $amount, 2) : number_format((float) $amount, 2) . ' ' . $currency;
  $dateFormat = 'M d, Y';
  $timeFormat = $websiteInfo->time_format == 24 ? 'H:i' : 'h:i A';
  $arrivalDate = Carbon\Carbon::parse($bookingInfo->check_in_date)->format($dateFormat);
  $arrivalTime = Carbon\Carbon::parse($bookingInfo->check_in_time)->format($timeFormat);
  $departureDate = Carbon\Carbon::parse($bookingInfo->check_out_date)->format($dateFormat);
  $departureTime = Carbon\Carbon::parse($bookingInfo->check_out_time)->format($timeFormat);
  $courtContent = $bookingInfo->hotelRoom->room_content->where('language_id', $currentLanguageInfo->id)->first();
  $courtTitle = optional($courtContent)->title ?? __('Court');
@endphp
<table class="header">
  <tr>
    <td style="width:45%">
      <img class="logo" src="{{ asset('assets/img/' . $websiteInfo->logo) }}" alt="{{ $websiteInfo->website_title }}"></td>
    <td style="width:55%">
      <div class="invoice-heading">{{ __('INVOICE') }}</div>
      <div class="invoice-subtitle">{{ __('Court Booking') }}</div>
    </td>
  </tr>
</table>
<table class="reference">
  <tr>
    <td>
      <span class="label">{{ __('Invoice / Booking No.') }}</span>
      <span class="value">#{{ $bookingInfo->order_number }}</span>
    </td>
    <td>
      <span class="label">{{ __('Booking Date') }}</span>
      <span class="value">{{ $bookingInfo->created_at->format($dateFormat) }}</span>
    </td>
    <td>
      <span class="label">{{ __('Payment Status') }}</span>
      <span class="status {{ $bookingInfo->payment_status == 1 ? 'paid' : 'unpaid' }}">
        {{ $bookingInfo->payment_status == 1 ? __('Paid') : __('Unpaid') }}
      </span>
    </td>
  </tr>
</table>
<div class="section-title">{{ __('Booking Schedule') }}</div>
<table class="schedule">
  <tr>
    <td class="court">
      <span class="label">{{ __('Court') }}</span>
      <span class="value">{{ $courtTitle }}</span>
    </td>
    <td class="time">
      <span class="label">{{ __('Start') }}</span>
      <span class="value">{{ $arrivalDate }}</span><br>{{ $arrivalTime }}
    </td>
    <td class="time">
      <span class="label">{{ __('End') }}</span>
      <span class="value">{{ $departureDate }}</span><br>{{ $departureTime }}
    </td>
  </tr>
</table>
<table class="details">
  <tr>
    <td>
      <div class="section-title">{{ __('Customer Details') }}</div>
      <div class="card">
        <div class="info">
          <span class="label">{{ __('Name') }}</span>
          <span class="value">{{ $bookingInfo->booking_name }}</span>
        </div>
        @if(!empty($bookingInfo->booking_email))
          <div class="info">
            <span class="label">{{ __('Email') }}</span>
            <span class="value">{{ $bookingInfo->booking_email }}</span>
          </div>
        @endif
        <div class="info">
          <span class="label">{{ __('Phone') }}</span>
          <span class="value">{{ $bookingInfo->booking_phone }}</span>
        </div>
      </div>
    </td>
    <td>
      <div class="section-title">{{ __('Payment Details') }}</div>
      <div class="card">
        <div class="info">
          <span class="label">{{ __('Payment Method') }}</span>
          <span class="value">{{ $bookingInfo->payment_method ?: __('Not specified') }}</span>
        </div>
        <div class="info">
          <span class="label">{{ __('Status') }}</span>
          {{ $bookingInfo->payment_status == 1 ? __('Paid') : __('Unpaid') }}
        </div>
        <div class="info">
          <span class="label">{{ __('Currency') }}</span>
          <span class="value">{{ $currency }}</span>
        </div>
      </div>
    </td>
  </tr>
</table >
<div class="section-title">{{ __('Charges Summary') }}</div>
<div class="charges-wrap">
  <table class="charges">
    <tr>
      <td>{{ __('Court Rent') }}</td>
      <td class="amount">{{ $money($bookingInfo->roomPrice) }}</td>
    </tr>
    <tr>
      <td>{{ __('Additional Service Charge') }}</td>
      <td class="amount">{{ $money($bookingInfo->serviceCharge) }}</td>
    </tr>
    <tr>
      <td>{{ __('Discount') }}</td>
      <td class="amount discount">- {{ $money($bookingInfo->discount) }}</td>
    </tr>
    <tr class="subtotal">
      <td>{{ __('Subtotal') }}</td>
      <td class="amount">{{ $money($bookingInfo->total) }}</td>
    </tr>
    <tr>
      <td>{{ __('Tax') }}</td>
      <td class="amount">{{ $money($bookingInfo->tax) }}</td>
    </tr>
    <tr class="total">
      <td>{{ __('Grand Total') }}</td>
      <td class="amount">{{ $money($bookingInfo->grand_total) }}</td>
    </tr>
  </table>
</div>
<div class="footer">{{ __('This invoice was generated electronically for booking') }} #{{ $bookingInfo->order_number }}. {{ __('Thank you for choosing') }} {{ $websiteInfo->website_title }}.</div>
</body>
</html>
