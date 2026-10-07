<?php

namespace App\Http\Controllers\FrontEnd\BookingPayment;

use App\Http\Controllers\Controller;
use App\Models\BookingHour;
use App\Models\HourlyRoomPrice;
use App\Models\Room;
use App\Models\Hotel;
use App\Models\Transaction;
use App\Models\VendorEwallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class EwalletController extends Controller
{
    public function index(Request $request, VendorEwallet $wallet)
    {
        if ($request->session()->has('open_play_slot_id')) {
            $bookingProcess = new BookingController();
            $arrData = $bookingProcess->timeCheck($request, $wallet->name);
            $arrData['gatewayType'] = 'vendor_ewallet';
            $arrData['payment_status'] = 0;
            $arrData['vendor_ewallet_id'] = $wallet->id;
            $arrData['payment_transaction_id'] = $request->payment_transaction_id;
            $arrData['payer_phone'] = $request->payer_phone;
            $booking = $bookingProcess->storeData($arrData);
            $booking->update(['invoice' => $bookingProcess->generateInvoice($booking)]);
            $bookingProcess->prepareMailForvendor($booking);

            Transaction::updateOrCreate(
                ['booking_id' => $booking->id, 'transcation_type' => 'room_booking', 'vendor_id' => $booking->vendor_id],
                [
                    'transcation_id' => $booking->payment_transaction_id,
                    'user_id' => $booking->user_id,
                    'payment_status' => $booking->payment_status,
                    'payment_method' => $booking->payment_method,
                    'grand_total' => $booking->grand_total,
                    'commission' => 0,
                    'gateway_type' => 'vendor_ewallet',
                    'currency_symbol' => $booking->currency_symbol,
                    'currency_symbol_position' => $booking->currency_symbol_position,
                    'pre_balance' => null,
                    'after_balance' => null,
                ]
            );

            $request->session()->forget([
                'price', 'open_play_slot_id', 'checkInTime', 'checkInDate', 'check_out_time',
                'hour', 'adult', 'children', 'open_play_players', 'classification', 'roomDiscount', 'takeService', 'serviceCharge',
            ]);

            return redirect()->route('frontend.room_booking.complete', ['type' => 'offline_booking']);
        }

        $priceId = $request->session()->get('price');
        if (!$priceId) {
            Session::flash('error', __('Something went wrong') . '!');
            return redirect()->back();
        }

        $price = HourlyRoomPrice::findOrFail($priceId);
        $room = Room::findOrFail($price->room_id);
        abort_unless(Hotel::findOrFail($room->hotel_id)->isBookable(), 404);
        $hour = BookingHour::findOrFail($price->hour_id)->hour;
        $checkInTime = date('H:i:s', strtotime($request->session()->get('checkInTime')));
        $checkInDate = date('Y-m-d', strtotime($request->session()->get('checkInDate')));
        $checkoutTime = date('H:i:s', strtotime($checkInTime . " +{$hour} hour"));
        $nextBookingTime = date('H:i:s', strtotime($checkoutTime . " +{$room->preparation_time} min"));
        $checkoutDate = $checkoutTime < $checkInTime
            ? date('Y-m-d', strtotime($checkInDate . ' +1 day'))
            : $checkInDate;

        $bookingProcess = new BookingController();
        $calculated = $bookingProcess->calculation($request, $priceId);
        $currency = $this->getCurrencyInfo();

        $booking = $bookingProcess->storeData([
            'check_in_time' => $checkInTime,
            'check_in_date' => $checkInDate,
            'check_out_date' => $checkoutDate,
            'check_out_time' => $checkoutTime,
            'check_in_date_time' => $checkInDate . ' ' . $checkInTime,
            'check_out_date_time' => $checkoutDate . ' ' . $nextBookingTime,
            'vendor_id' => $price->vendor_id,
            'hotel_id' => $price->hotel_id,
            'room_id' => $price->room_id,
            'preparation_time' => $room->preparation_time,
            'next_booking_time' => $nextBookingTime,
            'hour' => $hour,
            'adult' => $request->session()->get('adult'),
            'children' => $request->session()->get('children'),
            'booking_name' => $request->booking_name,
            'booking_email' => $request->booking_email,
            'booking_phone' => $request->booking_phone,
            'booking_address' => $request->booking_address,
            'additional_service' => $calculated['additional_service'],
            'service_details' => $calculated['service_details'],
            'roomPrice' => $calculated['roomPrice'],
            'serviceCharge' => $calculated['serviceCharge'],
            'total' => $calculated['total'],
            'discount' => $calculated['discount'],
            'tax' => $calculated['tax'],
            'grandTotal' => $calculated['grandTotal'],
            'currencyText' => $currency->base_currency_text,
            'currencyTextPosition' => $currency->base_currency_text_position,
            'currencySymbol' => $currency->base_currency_symbol,
            'currencySymbolPosition' => $currency->base_currency_symbol_position,
            'paymentMethod' => $wallet->name,
            'gatewayType' => 'vendor_ewallet',
            'payment_status' => 0,
            'attachment' => null,
            'classification' => $request->classification,
            'vendor_ewallet_id' => $wallet->id,
            'payment_transaction_id' => $request->payment_transaction_id,
            'payer_phone' => $request->payer_phone,
        ]);

        $booking->update(['invoice' => $bookingProcess->generateInvoice($booking)]);
        $bookingProcess->prepareMailForvendor($booking);

        Transaction::updateOrCreate(
            [
                'booking_id' => $booking->id,
                'transcation_type' => 'room_booking',
                'vendor_id' => $booking->vendor_id,
            ],
            [
                'transcation_id' => $booking->payment_transaction_id,
                'user_id' => $booking->user_id,
                'payment_status' => $booking->payment_status,
                'payment_method' => $booking->payment_method,
                'grand_total' => $booking->grand_total,
                'commission' => 0,
                'gateway_type' => 'vendor_ewallet',
                'currency_symbol' => $booking->currency_symbol,
                'currency_symbol_position' => $booking->currency_symbol_position,
                'pre_balance' => null,
                'after_balance' => null,
            ]
        );

        $request->session()->forget([
            'price', 'checkInTime', 'checkInDate', 'adult', 'children',
            'roomDiscount', 'takeService', 'serviceCharge',
        ]);

        return redirect()->route('frontend.room_booking.complete', ['type' => 'offline_booking']);
    }
}
