<?php

namespace App\Http\Controllers\FrontEnd;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Session;

use App\Http\Controllers\FrontEnd\MiscellaneousController;
use App\Models\HourlyRoomPrice;
use App\Models\PaymentGateway\OfflineGateway;
use App\Models\PaymentGateway\OnlineGateway;
use App\Models\Room;
use App\Models\RoomContent;
use App\Models\RoomCoupon;
use App\Models\VendorEwallet;
use App\Models\CourtBlockedSlot;
use Illuminate\Support\Facades\Response;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    public function checkCheckout(Request $request)
    {

        $id = $request->room_id;
        $room = Room::with('hotel')->where('status', 1)->findOrFail($id);
        if (!$room->hotel || !$room->hotel->isBookable()) {
            return response()->json(__('This Site is not verified and cannot accept bookings.'), 422);
        }

        $selectedPrice = HourlyRoomPrice::with('bookingHour')
            ->where('room_id', $room->id)
            ->find($request->price);

        if (!$selectedPrice) {
            return response()->json(__('Please select a valid booking price for this Court.'), 422);
        }

        $selectedHours = (float) optional($selectedPrice->bookingHour)->hour;
        if ($selectedHours <= 0 || (float) $request->hour !== $selectedHours) {
            return response()->json(__('The selected booking duration is invalid.'), 422);
        }

        $normalizedCheckInTime = $this->normalizeBookingTime($request->checkInTime);
        $normalizedCheckOutTime = $this->normalizeBookingTime($request->check_out_time);
        $startsAt = Carbon::parse($request->checkInDate.' '.$normalizedCheckInTime);
        $expectedCheckOutTime = $startsAt->copy()->addMinutes((int) round($selectedHours * 60))->format('H:i:s');
        if ($normalizedCheckOutTime !== $expectedCheckOutTime) {
            return response()->json(__('The selected checkout time is invalid.'), 422);
        }

        $rule = [
            'price' => 'required',

        ];

        $messages = [
            'price.required' => __('Please select a booking hour to proceed.')
        ];

        $validator = Validator::make($request->except('check_out_time','room_id','hour'), $rule, $messages);



        if ($validator->fails()) {
            return response()->json(
                [
                    'alert_type' => 'error',
                    'message' => 'Validation error occurred.',
                    'errors' => $validator->errors()
                ],
                422
            );
        }

        $openingTime = Carbon::parse($request->checkInDate.' '.$room->open_time);
        $closingTime = Carbon::parse($request->checkInDate.' '.$room->close_time);
        if ($closingTime->lessThanOrEqualTo($openingTime)) {
            $closingTime->addDay();
        }
        $selectedEnd = $startsAt->copy()->addMinutes((int) round($selectedHours * 60));
        if ($startsAt->lessThan($openingTime) || $selectedEnd->greaterThan($closingTime)) {
            return response()->json('Selected time exceeds venue closing hours.', 422);
        }

       
       
     //    var_dump($closing_time, $request->check_out_time);die();
        //  if ($request->check_out_time > $closing_time) {
        //     return response()->json('Selected time exceeds venue closing hours.', 422);
        // }


            $isAvailable = $this->isRoomAvailable(
                $request->room_id,
                $request->checkInDate,
                $request->checkInTime,
                $request->check_out_time
            );

        if ($isAvailable) {
           // Session::flash('error', __('Schedule not available') . '!');
            return response()->json('Schedule not available',422);
           // return response()->json(['alert_type' => 'error','message' => 'Schedule not Available'],422);

        }
//            if ($isAvailable) {
//// var_dump($isAvailable);die();
//                $msg = [
//                    'error' => 'Schedule not available'
//                ];
//                Session::flash('error', __('Schedule not available') . '!');
//               return redirect()->back()->response()->json(['error' => __('Coupon is not valid!')],500);
//          // return response()->json(['message' => __('Schedule not available.'), 'alert_type' => 'error'],422);
//             //   return response()->json(['alert_type' => 'error','message' => __('Schedule not available.'), 'errors' => $msg['error']],420);
//
//            }

        $request->session()->put('room_id', $request->room_id);
        $request->session()->put('checkInTime', $normalizedCheckInTime);
        $request->session()->put('checkInDate', $request->checkInDate);
        $request->session()->put('check_out_time', $request->check_out_time);
        $request->session()->put('hour', $request->hour);
        $request->session()->put('price', $request->price);
        $request->session()->put('adult', $request->adult);
        $request->session()->put('children', $request->children);
        Session::forget('serviceCharge');
        Session::forget('takeService');
        Session::forget('roomDiscount');

        $checkoutUrl = route('frontend.room.checkout');

        if (!$request->ajax() && !$request->expectsJson()) {
            return redirect()->to($checkoutUrl);
        }

        return response()->json([
            'redirect_url' => $checkoutUrl
        ], 200);
    }

    public function isRoomAvailable($roomId, $startDate, $startTime, $endTime)
    {
        $bookingExists = Booking::where('room_id', $roomId)->where('check_in_date',$startDate)
            ->where('check_in_time', '<', $endTime)
            ->where('check_out_time', '>', $this->normalizeBookingTime($startTime))
            ->exists();

        $startsAt = Carbon::parse($startDate . ' ' . $this->normalizeBookingTime($startTime));
        $endsAt = Carbon::parse($startDate . ' ' . $endTime);
        if ($endsAt->lessThanOrEqualTo($startsAt)) {
            $endsAt->addDay();
        }

        $blockExists = CourtBlockedSlot::overlapping($roomId, $startsAt, $endsAt)->exists();

        return $bookingExists || $blockExists;


//        $isAvailable = Booking::where('room_id', $roomId)->where('check_in_date',$startDate)
//            ->where(function ($query) use ($startTime, $endTime) {
//                $query->whereBetween('check_in_time', [$startTime, $endTime])
//                    ->orWhereBetween('check_out_time', [$startTime, $endTime])
//                    ->orWhere(function ($q) use ($startTime, $endTime) {
//                        $q->where('check_in_time', '<', $startTime)
//                            ->where('check_out_time', '>', $endTime);
//                    });
//            })
//            ->exists();
//
//        return !$isAvailable; // Returns true if the room is available, false if there is a conflict

    }

    private function normalizeBookingTime(string $time): string
    {
        return Carbon::parse($time)->format('H:i:s');
    }

    public function checkout(Request $request)
    {
        $misc = new MiscellaneousController();
        $language = $misc->getLanguage();
        $information['language'] = $language;

        $information['pageHeading'] = $misc->getPageHeading($language);
        $information['bgImg'] = $misc->getBreadcrumb();
        $information['authUser'] = Auth::guard('web')->check() == true ? Auth::guard('web')->user() : null;

        $information['currencyInfo'] = $this->getCurrencyInfo();

        $information['onlineGateways'] = OnlineGateway::where('status', 1)->get();

        $information['offline_gateways'] = OfflineGateway::where('status', 1)->orderBy('serial_number', 'asc')->get();
        $information['vendorEwallets'] = collect();

        $stripe = OnlineGateway::where('keyword', 'stripe')->first();
        $stripe_info = json_decode($stripe->information, true);
        $information['stripe_key'] = $stripe_info['key'];


        $authorizenet = OnlineGateway::query()->whereKeyword('authorize.net')->first();
        $anetInfo = json_decode($authorizenet->information);

        if ($anetInfo->sandbox_check == 1) {
            $information['anetSource'] = 'https://jstest.authorize.net/v1/Accept.js';
        } else {
            $information['anetSource'] = 'https://js.authorize.net/v1/Accept.js';
        }

        $information['anetClientKey'] = $anetInfo->public_key;
        $information['anetLoginId'] = $anetInfo->login_id;


        $information['checkInTime'] = $request->session()->get('checkInTime');
        $information['checkInDate']  = $request->session()->get('checkInDate');
        $information['check_out_time'] = $request->session()->get('check_out_time');
        $information['hour']  = $request->session()->get('hour');
        $information['room_id']  = $request->session()->get('room_id');
        $price  = $request->session()->get('price');
        $information['price'] = $price;
        $openPlaySlot = null;
        if ($request->session()->has('open_play_slot_id')) {
            $openPlaySlot = CourtBlockedSlot::where('slot_type', 'open_play')
                ->where('ends_at', '>', now())
                ->find($request->session()->get('open_play_slot_id'));
        }
        $information['openPlaySlot'] = $openPlaySlot;

        $information['adult']  = $request->session()->get('adult');
        $information['children']  = $request->session()->get('children');

        $information['classifications'] = [
            'private' =>__('Private'),
            'exclusive' => __('Exclusive'),
            'open_play' => __('Open Play'),
            'training' => __('Training')
        ];

        if ($information['checkInTime'] && $information['checkInDate'] && ($information['price'] || $openPlaySlot)) {

            $roomId = $openPlaySlot ? $openPlaySlot->room_id : HourlyRoomPrice::findOrFail($price)->room_id;
            $additionalServices = json_decode(Room::find($roomId)->additional_service);

            $information['room'] = RoomContent::join('rooms', 'rooms.id', '=', 'room_contents.room_id')
                ->Join('hotels', 'rooms.hotel_id', '=', 'hotels.id')
                ->Join('room_categories', 'room_contents.room_category', '=', 'room_categories.id')
                ->Join('hotel_contents', 'rooms.hotel_id', '=', 'hotel_contents.hotel_id')
                ->Join('hotel_categories', 'hotel_contents.category_id', '=', 'hotel_categories.id')
                ->where('hotel_contents.language_id', $language->id)
                ->where('room_categories.status', 1)
                ->where('hotel_categories.status', 1)
                ->where('room_contents.language_id', $language->id)
                ->where('rooms.status',  '=',    '1')
                ->where('hotels.status',  '=',    '1')
                ->where('hotels.verification_status', 'approved')

                ->where([
                    ['rooms.status', '=', '1']
                ])

                ->select(
                    'rooms.*',
                    'room_contents.title',
                    'room_contents.slug',
                    'room_contents.amenities',
                    'room_categories.name as room_category',
                    'room_categories.slug as room_category_slug',
                    'room_contents.meta_keyword',
                    'room_contents.meta_description',
                    'hotel_contents.address',
                    'room_contents.description',
                    'hotels.id as hotelId',
                    'hotels.latitude as latitude',
                    'hotels.longitude as longitude',
                )
                ->where('rooms.id', $roomId)
                ->firstorFail();

            $information['vendorEwallets'] = VendorEwallet::query()
                ->where('vendor_id', $information['room']->vendor_id)
                ->where('status', true)
                ->orderBy('name')
                ->get();

            if ($additionalServices) {
                $information['additionalServices'] = $openPlaySlot ? null : $additionalServices;
            } else {
                $information['additionalServices'] = null;
            }
            return view('frontend.room.checkout', $information);
        } else {
            return redirect(route('frontend.rooms'));
        }
    }

    public function applyCoupon(Request $request)
    {
        try {
            $coupon = RoomCoupon::where('code', $request->coupon)->firstOrFail();

            $startDate = Carbon::parse($coupon->start_date);
            $endDate = Carbon::parse($coupon->end_date);
            $todayDate = Carbon::now();

            if ($todayDate->between($startDate, $endDate) == false) {
                return response()->json(['error' => __('Sorry, coupon has been expired!')]);
            }

            $price_id = $request->session()->get('price');
            $serviceCharge = $request->session()->get('serviceCharge');
            $roomId = HourlyRoomPrice::findorfail($price_id)->room_id;
            $price = HourlyRoomPrice::findorfail($price_id)->price;

            $roomIds = empty($coupon->rooms) ? '' : json_decode($coupon->rooms);

            if (!empty($roomIds) && !in_array($roomId, $roomIds)) {
                return response()->json(['error' => __('You can not apply this coupon for this court!')]);
            }

            session()->put('couponCode', $request->coupon);

            if ($coupon->type == 'fixed') {

                $request->session()->put('roomDiscount', $coupon->value);
                return response()->json([
                    'success' => __('Coupon applied successfully.'),
                ]);
            } else {

                $couponAmount = ($price + $serviceCharge) * ($coupon->value / 100);
                $request->session()->put('roomDiscount', $couponAmount);

                return response()->json([
                    'success' => __('Coupon applied successfully.'),
                ]);
            }
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => __('Coupon is not valid!')]);
        }
    }
    public function additonalService(Request $request)
    {
        $haven = Session::get('takeService');
        $taken = $request->takeService;

        $havenArray = !empty($haven) ? explode(',', $haven) : [];
        $takenArray = !empty($taken) ? explode(',', $taken) : [];

        $havenCount = count($havenArray);
        $takenCount = count($takenArray);

        Session::put('serviceCharge', $request->serviceCharge);
        Session::put('takeService', $request->takeService);
        Session::forget('roomDiscount');
        $couponCode = Session::forget('couponCode');


        if ($havenCount > $takenCount) {
            return response()->json([
                'error' => __('Additional Service removed successfully.'),
            ]);
        } else {
            return response()->json([
                'success' => __('Additional Service added successfully.'),
            ]);
        }
    }
}
