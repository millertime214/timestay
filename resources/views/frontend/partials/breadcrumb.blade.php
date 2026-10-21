<!-- Breadcrumb start -->
<div class="breadcrumb-area bg-img bg-cover z-1 header-next mt-50"
@if(isset($roomContent->feature_image))
data-bg-img="{{ asset('assets/img/room/featureImage/' . $roomContent->feature_image) }}"
@else
data-bg-img="{{ asset('assets/img/' . $breadcrumb) }}"
@endif
  >
  <div class="overlay opacity-75"></div>
  <div class="container">
    <div class="content text-center hotel_author">
      <h2 class="color-white">
        @if(isset($roomContent->hotellogo) && !empty($roomContent->hotellogo))
        <img class="img-sm ls-is-cached lazyloaded"
                      src="{{ asset('assets/img/hotel/logo/' . $roomContent->hotellogo) }}" alt="{{ __('Image') }}">
        @endif
         {{ !empty($title) ? $title : '' }}</h2>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ route('index') }}">{{ __('Home') }}</a></li>
          <li class="breadcrumb-item" aria-current="page">{{ !empty($title) ? $title : '' }}</li>
        </ol>
      </nav>
    </div>
  </div>
</div>
<!-- Breadcrumb end -->
