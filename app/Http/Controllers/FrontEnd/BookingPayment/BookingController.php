<?php

namespace App\Http\Controllers\FrontEnd\BookingPayment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\FrontEnd\BookingPayment\OfflineController;
use App\Http\Controllers\FrontEnd\BookingPayment\PayPalController;
use App\Http\Controllers\FrontEnd\BookingPayment\InstamojoController;
use App\Http\Controllers\FrontEnd\BookingPayment\RazorpayController;
use App\Http\Controllers\FrontEnd\BookingPayment\PaytmController;
use App\Http\Controllers\FrontEnd\BookingPayment\PaystackController;
use App\Http\Controllers\FrontEnd\BookingPayment\MercadoPagoController;
use App\Http\Controllers\FrontEnd\BookingPayment\MidtransController;
use App\Http\Controllers\FrontEnd\BookingPayment\MyfatoorahController;
use App\Http\Controllers\FrontEnd\BookingPayment\YocoController;
use App\Http\Controllers\FrontEnd\BookingPayment\ToyyibpayController;
use App\Http\Controllers\FrontEnd\MiscellaneousController;
use App\Http\Requests\Room\BookingProcessRequest;
use App\Models\BasicSettings\Basic;
use App\Models\BasicSettings\MailTemplate;
use App\Models\Booking;
use App\Models\HourlyRoomPrice;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Helpers\BasicMailer;
use App\Models\AdditionalService;
use App\Models\BookingHour;
use App\Models\Membership;
use App\Models\Room;
use App\Models\Vendor;
use App\Models\VendorEwallet;
use App\Models\CourtBlockedSlot;
use App\Models\Hotel;
use Carbon\Carbon;

class BookingController extends Controller
{

    public function index(BookingProcessRequest $request)
    {
        $priceId = $request->session()->get('price');
        $openPlaySlot = $this->openPlaySlot($request);
        $roomId = $openPlaySlot ? $openPlaySlot->room_id : ($priceId ? HourlyRoomPrice::findOrFail($priceId)->room_id : null);
        $room = $roomId ? Room::with('hotel')->where('status', 1)->findOrFail($roomId) : null;
        if (!$room || !$room->hotel || !$room->hotel->isBookable()) {
            return redirect()->route('frontend.rooms')->with('error', __('This Site is not verified and cannot accept bookings.'));
        }
        if (!$openPlaySlot && $priceId) {
            $price = HourlyRoomPrice::findOrFail($priceId);
            $startsAt = Carbon::parse(
                $request->session()->get('checkInDate') . ' ' . $request->session()->get('checkInTime')
            );
            $endsAt = (clone $startsAt)->addHours(BookingHour::findOrFail($price->hour_id)->hour);

            if (CourtBlockedSlot::overlapping($price->room_id, $startsAt, $endsAt)->exists()) {
                return redirect()->back()->withInput()->with('error', __('This timeslot is blocked by the vendor.'));
            }
        }

        if (!$request->exists('gateway')) {
            Session::flash('error', 'Please select a payment method.');

            return redirect()->back()->withInput();
        }
        if ($request['gateway'] == 'paypal') {
            $paypal = new PayPalController();

            return $paypal->index($request, 'Court Booking');
        } else if ($request['gateway'] == 'midtrans') {
            $midtrans = new MidtransController();
            $userType = 'booking';
            return $midtrans->index($request, 'Court Booking', $userType);
        } else if ($request['gateway'] == 'instamojo') {
            $instamojo = new InstamojoController();

            return $instamojo->index($request, 'Court Booking');
        } else if ($request['gateway'] == 'paystack') {
            $paystack = new PaystackController();

            return $paystack->index($request, 'Court Booking');
        } else if ($request['gateway'] == 'flutterwave') {
            $flutterwave = new FlutterwaveController();

            return $flutterwave->index($request, 'Court Booking');
        } else if ($request['gateway'] == 'iyzico') {

            $iyzico = new IyzicoController();
            return $iyzico->index($request, 'product purchase');
        } else if ($request['gateway'] == 'toyyibpay') {

            $toyyibpay = new ToyyibpayController();
            return $toyyibpay->index($request, 'Court Booking');
        } else if ($request['gateway'] == 'razorpay') {
            $razorpay = new RazorpayController();

            return $razorpay->index($request, 'Court Booking');
        } else if ($request['gateway'] == 'paytabs') {

            $paytabs = new PaytabsController();
            return $paytabs->index($request, 'Court Booking');
        } else if ($request['gateway'] == 'phonepe') {

            $phonepe = new PhonepeController();
            return $phonepe->index($request, 'Court Booking');
        } else if ($request['gateway'] == 'yoco') {

            $yoco = new YocoController();
            return $yoco->index($request, 'Court Booking');
        } else if ($request['gateway'] == 'mercadopago') {
            $mercadopago = new MercadoPagoController();

            return $mercadopago->index($request, 'Court Booking');
        } else if ($request['gateway'] == 'mollie') {
            $mollie = new MollieController();

            return $mollie->index($request, 'Court Booking');
        } else if ($request['gateway'] == 'stripe') {
            $stripe = new StripeController();

            return $stripe->index($request, 'Court Booking');
        } else if ($request['gateway'] == 'myfatoorah') {
            $myfatoorah = new MyfatoorahController();
            return $myfatoorah->index($request, 'Court Booking');
        } else if ($request['gateway'] == 'paytm') {
            $paytm = new PaytmController();

            return $paytm->index($request, 'Court Booking');
        } else if ($request['gateway'] == 'perfect_money') {
            $perfect_money = new PerfectMoneyController();

            return $perfect_money->index($request, 'Court Booking');
        } else if ($request['gateway'] == 'xendit') {

            $xendit = new XenditController();
            return $xendit->index($request, 'Court Booking');
        } else if ($request['gateway'] == 'authorize.net') {
            $author = new AuthorizeNetController();

            return $author->index($request, 'Court Booking');
        } else if (str_starts_with((string) $request['gateway'], 'vendor_ewallet_')) {
            $walletId = (int) str_replace('vendor_ewallet_', '', $request['gateway']);
            $wallet = VendorEwallet::whereKey($walletId)->where('status', true)->firstOrFail();
            $bookingVendorId = $openPlaySlot
                ? $openPlaySlot->vendor_id
                : HourlyRoomPrice::findOrFail($request->session()->get('price'))->vendor_id;

            abort_unless((int) $wallet->vendor_id === (int) $bookingVendorId, 403);

            $ewallet = new EwalletController();

            return $ewallet->index($request, $wallet);
        } else {
            $offline = new OfflineController();

            return $offline->index($request, 'Court Booking');
        }
    }
    public function timeCheck(Request $request, $paymentMethod)
    {
        if ($slot = $this->openPlaySlot($request)) {
            $room = Room::findOrFail($slot->room_id);
            $calculatedData = $this->calculation($request, null);
            $currencyInfo = $this->getCurrencyInfo();
            $hours = $slot->starts_at->floatDiffInHours($slot->ends_at);

            return [
                'check_in_time' => $slot->starts_at->format('H:i:s'),
                'check_in_date' => $slot->starts_at->format('Y-m-d'),
                'check_out_date' => $slot->ends_at->format('Y-m-d'),
                'check_out_time' => $slot->ends_at->format('H:i:s'),
                'check_in_date_time' => $slot->starts_at->format('Y-m-d H:i:s'),
                'check_out_date_time' => $slot->ends_at->format('Y-m-d H:i:s'),
                'vendor_id' => $slot->vendor_id,
                'hotel_id' => $room->hotel_id,
                'room_id' => $room->id,
                'open_play_slot_id' => $slot->id,
                'preparation_time' => 0,
                'next_booking_time' => $slot->ends_at->format('H:i:s'),
                'hour' => $hours,
                'adult' => (int) $request->session()->get('adult'),
                'children' => 0,
                'open_play_players' => $request->session()->get('open_play_players', []),
                'booking_name' => $request['booking_name'],
                'booking_email' => $request['booking_email'],
                'booking_phone' => $request['booking_phone'],
                'booking_address' => $request['booking_address'],
                'additional_service' => null,
                'service_details' => [],
                'roomPrice' => $calculatedData['roomPrice'],
                'serviceCharge' => 0,
                'total' => $calculatedData['total'],
                'discount' => $calculatedData['discount'],
                'tax' => $calculatedData['tax'],
                'grandTotal' => $calculatedData['grandTotal'],
                'currencyText' => $currencyInfo->base_currency_text,
                'currencyTextPosition' => $currencyInfo->base_currency_text_position,
                'currencySymbol' => $currencyInfo->base_currency_symbol,
                'currencySymbolPosition' => $currencyInfo->base_currency_symbol_position,
                'paymentMethod' => $paymentMethod,
                'gatewayType' => 'online',
                'payment_status' => $paymentMethod == 'Iyzico' ? 0 : 1,
                'attachment' => null,
                'classification' => 'open_play',
            ];
        }

        $price_id = $request->session()->get('price');
        $Time = $request->session()->get('checkInTime');
        $Date = $request->session()->get('checkInDate');
        $adult = $request->session()->get('adult');
        $children = $request->session()->get('children');

        $hour_id = HourlyRoomPrice::findorfail($price_id)->hour_id;
        $hour = BookingHour::findorfail($hour_id)->hour;
        $vendor_id = HourlyRoomPrice::findorfail($price_id)->vendor_id;
        $hotel_id = HourlyRoomPrice::findorfail($price_id)->hotel_id;
        $room_id = HourlyRoomPrice::findorfail($price_id)->room_id;
        $preparation_time = Room::findorfail($room_id)->preparation_time;
        $checkInTime = date('H:i:s', strtotime($Time));
        $checkInDate = date('Y-m-d', strtotime($Date));
        $check_in_date_time = $checkInDate . ' ' . $checkInTime;

        $checkoutTime = date('H:i:s', strtotime($Time . " +$hour hour"));
        $next_booking_time = date('H:i:s', strtotime($checkoutTime . " +$preparation_time min"));


        list($current_hour, $current_minute, $current_second) = explode(':', $checkInTime);
        $total_hours = (int)$current_hour + $hour;
        $next_booking_time_for_next_day = sprintf('%02d:%02d:%02d', $total_hours, $current_minute, $current_second);

        $checkoutTimeLimit = '23:59:59';

        if ($checkoutTimeLimit < $next_booking_time_for_next_day) {
            $checkoutDate = date('Y-m-d', strtotime($checkInDate . ' +1 day'));
        } else {
            $checkoutDate = date('Y-m-d', strtotime($checkInDate));
        }

        $check_out_date_time = $checkoutDate . ' ' . $next_booking_time;
        $priceId = $request->session()->get('price');

        $bookingProcess = new BookingController();

        // do calculation
        $calculatedData = $bookingProcess->calculation($request, $priceId);

        $currencyInfo = $this->getCurrencyInfo();

        $arrData = array(
            'check_in_time' => $checkInTime,
            'check_in_date' =>  $checkInDate,
            'check_out_date' => $checkoutDate,
            'check_out_time' =>  $checkoutTime,
            'check_in_date_time' =>  $check_in_date_time,
            'check_out_date_time' =>  $check_out_date_time,
            'vendor_id' =>  $vendor_id,
            'hotel_id' =>  $hotel_id,
            'room_id' =>  $room_id,
            'preparation_time' =>  $preparation_time,
            'next_booking_time' =>  $next_booking_time,
            'hour' =>  $hour,
            'adult' =>  $adult,
            'children' => $children,
            'booking_name' => $request['booking_name'],
            'booking_email' => $request['booking_email'],
            'booking_phone' => $request['booking_phone'],
            'booking_address' => $request['booking_address'],
            'additional_service' => $calculatedData['additional_service'],
            'service_details' => $calculatedData['service_details'],
            'roomPrice' => $calculatedData['roomPrice'],
            'serviceCharge' => $calculatedData['serviceCharge'],
            'total' => $calculatedData['total'],
            'discount' => $calculatedData['discount'],
            'tax' => $calculatedData['tax'],
            'grandTotal' => $calculatedData['grandTotal'],
            'currencyText' => $currencyInfo->base_currency_text,
            'currencyTextPosition' => $currencyInfo->base_currency_text_position,
            'currencySymbol' => $currencyInfo->base_currency_symbol,
            'currencySymbolPosition' => $currencyInfo->base_currency_symbol_position,
            'paymentMethod' => $paymentMethod,
            'gatewayType' => 'online',
            'payment_status' => $paymentMethod  == 'Iyzico' ? 0 : 1,
            'attachment' => null,
            'classification' => $request->classification,
        );

        return $arrData;
    }
    public function calculation(Request $request, $priceId)
    {
        $misc = new MiscellaneousController();
        $language = $misc->getLanguage();
        $openPlaySlot = $this->openPlaySlot($request);
        $roomPrice = $openPlaySlot
            ? (float) $openPlaySlot->price_per_player * max(1, (int) $request->session()->get('adult'))
            : floatval(HourlyRoomPrice::findOrFail($priceId)->price);
        $roomId = $openPlaySlot ? $openPlaySlot->room_id : HourlyRoomPrice::findOrFail($priceId)->room_id;
        $serviceCharge = floatval($request->session()->get('serviceCharge'));
        $total = $roomPrice + $serviceCharge;
        $service_details = [];

        if ($request->session()->has('roomDiscount')) {
            $discountVal = $request->session()->get('roomDiscount');
        }

        if ($request->session()->has('takeService')) {
            $additional_service = $request->session()->get('takeService');

            $room = Room::find($roomId);
            $additionalServices = json_decode($room->additional_service, true);

            $service_ids = explode(',', $additional_service);

            foreach ($service_ids as $id) {
                if (isset($additionalServices[$id])) {
                    $price = $additionalServices[$id];

                    $service = AdditionalService::join('additional_service_contents', 'additional_services.id', '=', 'additional_service_contents.additional_service_id')
                        ->where('additional_services.id', $id)
                        ->where('additional_service_contents.language_id', $language->id)
                        ->select('additional_service_contents.title')
                        ->first();
                    if ($service) {
                        $service_details[] = [
                            'price' => $price,
                            'service_name' => $service->title,
                        ];
                    }
                }
            }
        } else {
            $additional_service = null;
        }

        $discount = isset($discountVal) ? floatval($discountVal) : 0.00;
        $subtotal = $total - $discount;

        $taxData = Basic::select('hotel_tax_amount')->first();

        $taxAmount = floatval($taxData->hotel_tax_amount);
        $calculatedTax = $subtotal * ($taxAmount / 100);
        $grandTotal = $subtotal + $calculatedTax;

        $calculatedData = array(
            'total' => $total,
            'discount' => $discount,
            'subtotal' => $subtotal,
            'tax' => $calculatedTax,
            'roomPrice' => $roomPrice,
            'serviceCharge' => $serviceCharge,
            'grandTotal' => $grandTotal,
            'additional_service' => $additional_service,
            'service_details' => $service_details,
        );

        return $calculatedData;
    }
    public function storeData($arrData)
    {
        return DB::transaction(function () use ($arrData) {
        $site = Hotel::whereKey($arrData['hotel_id'])->lockForUpdate()->firstOrFail();
        if (!$site->isBookable()) {
            throw ValidationException::withMessages([
                'site' => __('This Site is not verified and cannot accept bookings.'),
            ]);
        }
        if ($arrData['vendor_id'] != 0) {
            $currentPackage = Membership::query()->where([
                ['vendor_id', '=', $arrData['vendor_id']],
                ['status', '=', 1],
                ['start_date', '<=', Carbon::now()->format('Y-m-d')],
                ['expire_date', '>=', Carbon::now()->format('Y-m-d')]
            ])->first();
        }

        $service_details = json_encode($arrData['service_details']);

        if (!empty($arrData['open_play_slot_id'])) {
            $slot = CourtBlockedSlot::whereKey($arrData['open_play_slot_id'])->lockForUpdate()->firstOrFail();
            $registeredPlayers = Booking::where('open_play_slot_id', $slot->id)
                ->where('payment_status', '!=', 2)
                ->sum('adult');

            if ((int) $arrData['adult'] > max((int) $slot->max_players - (int) $registeredPlayers, 0)) {
                throw ValidationException::withMessages([
                    'players' => __('There are no longer enough player spots available for this registration.'),
                ]);
            }
        }

        $orderInfo = Booking::query()->create([
            'order_number' => uniqid(),
            'user_id' => Auth::guard('web')->check() == true ? Auth::guard('web')->user()->id : null,
            'check_in_date' => $arrData['check_in_date'],
            'check_in_time' => $arrData['check_in_time'],
            'check_out_date' => $arrData['check_out_date'],
            'check_out_time' => $arrData['check_out_time'],
            'hour' => $arrData['hour'],
            'check_in_date_time' => $arrData['check_in_date_time'],
            'check_out_date_time' => $arrData['check_out_date_time'],
            'vendor_id' => $arrData['vendor_id'],
            'membership_id' => $arrData['vendor_id'] != 0 ? ($currentPackage ? $currentPackage->id : null) : 0,
            'hotel_id' => $arrData['hotel_id'],
            'room_id' => $arrData['room_id'],
            'open_play_slot_id' => $arrData['open_play_slot_id'] ?? null,
            'open_play_players' => $arrData['open_play_players'] ?? null,
            'preparation_time' => $arrData['preparation_time'],
            'next_booking_time' => $arrData['next_booking_time'],
            'adult' => $arrData['adult'],
            'children' => $arrData['children'],
            'booking_name' => $arrData['booking_name'],
            'booking_phone' => $arrData['booking_phone'],
            'booking_email' => $arrData['booking_email'],
            'booking_address' => $arrData['booking_address'],
            'total' => $arrData['total'],
            'roomPrice' => $arrData['roomPrice'],
            'serviceCharge' => $arrData['serviceCharge'],
            'discount' => $arrData['discount'],
            'tax' => $arrData['tax'],
            'grand_total' => $arrData['grandTotal'],
            'currency_text' => $arrData['currencyText'],
            'currency_text_position' => $arrData['currencyTextPosition'],
            'currency_symbol' => $arrData['currencySymbol'],
            'currency_symbol_position' => $arrData['currencySymbolPosition'],
            'payment_method' => $arrData['paymentMethod'],
            'vendor_ewallet_id' => $arrData['vendor_ewallet_id'] ?? null,
            'payment_transaction_id' => $arrData['payment_transaction_id'] ?? null,
            'payer_phone' => $arrData['payer_phone'] ?? null,
            'gateway_type' => $arrData['gatewayType'],
            'payment_status' => $arrData['payment_status'],
            'additional_service' => $arrData['additional_service'],
            'service_details' => $service_details,
            'attachment' => array_key_exists('attachment', $arrData) ? $arrData['attachment'] : null,
            'conversation_id' => array_key_exists('conversation_id', $arrData) ? $arrData['conversation_id'] : null,
            'classification' => array_key_exists('classification', $arrData) ? $arrData['classification'] : null,
        ]);

        if (!empty($arrData['open_play_slot_id'])) {
            Session::forget([
                'price', 'open_play_slot_id', 'checkInTime', 'checkInDate', 'check_out_time',
                'hour', 'adult', 'children', 'open_play_players', 'classification', 'roomDiscount', 'takeService', 'serviceCharge',
            ]);
        }

        return $orderInfo;
        });
    }

    private function openPlaySlot(Request $request): ?CourtBlockedSlot
    {
        $slotId = $request->session()->get('open_play_slot_id');

        return $slotId
            ? CourtBlockedSlot::where('slot_type', 'open_play')->where('ends_at', '>', now())->findOrFail($slotId)
            : null;
    }
    public function generateInvoice($bookingInfo)
    {
        $fileName = $bookingInfo->order_number . '.pdf';
        $directory = public_path('assets/file/invoices/room/');
        @mkdir($directory, 0775, true);

        if (!file_exists($directory)) {
            @mkdir($directory, 0775, true);
        }

        $fileLocated = $directory . $fileName;

        Pdf::loadView('frontend.pdf.room_booking', compact('bookingInfo'))->save($fileLocated);

        return $fileName;
    }
    public function prepareMailForCustomer($bookingInfo)
    {
        // get the mail template info from db
        $mailTemplate = MailTemplate::query()->where('mail_type', '=', 'room_booking')->first();
        $mailData['subject'] = $mailTemplate->mail_subject;
        $mailBody = $mailTemplate->mail_body;

        // get the website title info from db
        $info = Basic::select('website_title')->first();

        $customerName = $bookingInfo->booking_name;
        $orderNumber = $bookingInfo->order_number;
        $websiteTitle = $info->website_title;

        // replacing with actual data
        $mailBody = str_replace('{customer_name}', $customerName, $mailBody);
        $mailBody = str_replace('{order_number}', $orderNumber, $mailBody);
        $mailBody = str_replace('{website_title}', $websiteTitle, $mailBody);

        $mailData['body'] = $mailBody;

        $mailData['recipient'] = $bookingInfo->booking_email;

        $mailData['invoice'] = public_path('assets/file/invoices/room/') . $bookingInfo->invoice;

        BasicMailer::sendMail($mailData);

        return;
    }
    public function prepareMailForvendor($bookingInfo)
    {
        // get the mail template info from db
        $mailTemplate = MailTemplate::query()->where('mail_type', '=', 'inform_vendor_about_room_booking')->first();
        $mailData['subject'] = $mailTemplate->mail_subject;
        $mailBody = $mailTemplate->mail_body;

        // get the website title info from db
        $info = Basic::select('website_title', 'to_mail')->first();

        if ($bookingInfo->vendor_id != 0) {
            $vendor = Vendor::where('id', $bookingInfo->vendor_id)->select('to_mail', 'email', 'username')->first();
            if ($vendor->to_mail) {
                $mailData['recipient']  = $vendor->to_mail;
            } else {
                $mailData['recipient']  = $vendor->email;
            }

            $vendorUserName = $vendor->username;
        } else {
            $mailData['recipient'] = $info->to_mail;
            $vendorUserName = 'Admin';
        }

        $customerName = $bookingInfo->booking_name;

        $orderNumber = $bookingInfo->order_number;
        $websiteTitle = $info->website_title;


        // replacing with actual data
        $mailBody = str_replace('{username}', $vendorUserName, $mailBody);
        $mailBody = str_replace('{customer_name}', $customerName, $mailBody);
        $mailBody = str_replace('{order_number}', $orderNumber, $mailBody);
        $mailBody = str_replace('{website_title}', $websiteTitle, $mailBody);
        // $mailBody = str_replace('{order_link}', $orderLink, $mailBody);

        $mailData['body'] = $mailBody;


        $mailData['invoice'] = public_path('assets/file/invoices/room/') . $bookingInfo->invoice;

        BasicMailer::sendMail($mailData);

        return;
    }
    public function complete($type = null)
    {
        $misc = new MiscellaneousController();

        $information['bgImg'] = $misc->getBreadcrumb();

        $information['purchaseType'] = $type;

        return view('frontend.room.booking-success', $information);
    }
    public function cancel(Request $request)
    {
        Session::flash('warning', 'Payment Cancel.');
        return redirect()->route('frontend.rooms');
    }
}
