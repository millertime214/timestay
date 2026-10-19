<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| User Interface Routes
|--------------------------------------------------------------------------
*/

Route::post('/push-notification/store-endpoint', 'FrontEnd\PushNotificationController@store');

Route::get('/sitemap.xml', 'FrontEnd\SitemapController@index')->name('sitemap');
Route::get('/robots.txt', 'FrontEnd\SitemapController@robots')->name('robots');

// cron job for sending expiry mail
Route::get('/subcheck', 'CronJobController@expired')->name('cron.expired');

Route::get('/change-language', 'FrontEnd\MiscellaneousController@changeLanguage')->name('change_language');


Route::get('/midtrans/bank-notify', 'MidtransBankController@bankNotify')->name('midtrans.bank.notify');
Route::get('midtrans/cancel', 'MidtransBankController@cancelPayment')->name('midtrans.cancel');

Route::get('myfatoorah/callback', 'MyfatoorahController@myfatoorah_callback')->name('myfatoorah_callback');

Route::get('myfatoorah/cancel', 'MyfatoorahController@myfatoorah_cancel')->name('myfatoorah_cancel');

Route::get('/offline', 'FrontEnd\HomeController@offline')->middleware('change.lang');

Route::middleware('change.lang')->group(function () {

  Route::post('/store-subscriber', 'FrontEnd\MiscellaneousController@storeSubscriber')->name('store_subscriber');

  Route::get('/pricing', 'FrontEnd\HomeController@pricing')->name('frontend.pricing');
  Route::get('/faq', 'FrontEnd\FaqController@faq')->name('faq');

  Route::get('/', 'FrontEnd\HomeController@index')->name('index');

  Route::redirect('/rooms', '/courts', 301);
  Route::redirect('/rooms/search-room', '/courts/search-court', 301);
  Route::prefix('courts')->group(function () {
    Route::get('/', 'FrontEnd\RoomController@index')->name('frontend.rooms');
    Route::get('/search-court', 'FrontEnd\RoomController@search_room')->name('frontend.search_room');
  });

  Route::prefix('vendors')->group(function () {
    Route::get('/', 'FrontEnd\VendorController@index')->name('frontend.vendors');
    Route::post('contact/message', 'FrontEnd\VendorController@contact')->name('vendor.contact.message');
  });
  Route::get('vendor/{username}', 'FrontEnd\VendorController@details')->name('frontend.vendor.details');


  Route::get('/room/{slug}/{id}', function ($slug, $id) {
    return redirect()->route('frontend.room.details', compact('slug', 'id'), 301);
  });
  Route::prefix('court')->group(function () {

    Route::get('/open-play/{id}', 'FrontEnd\OpenPlayController@show')->name('frontend.open_play.show');
    Route::post('/open-play/{id}/checkout', 'FrontEnd\OpenPlayController@checkout')->name('frontend.open_play.checkout');
    Route::get('/{slug}/{id}/availability', 'FrontEnd\RoomController@availability')->name('frontend.room.details.availability');
    Route::get('/{slug}/{id}', 'FrontEnd\RoomController@details')->name('frontend.room.details');
    Route::get('/{slug}/{id}/get-hourly-price', 'FrontEnd\RoomController@getPrice')->name('frontend.room.details.get_hourly_price');
    Route::post('/room-review/{id}/store-review', 'FrontEnd\RoomController@storeReview')->name('frontend.room.room_details.store_review')->middleware('Demo');

    Route::get('/get-address', 'FrontEnd\RoomController@getAddress')->name('frontend.rooms.get-address');

    Route::post('/go-checkout', 'FrontEnd\BookingController@checkCheckout')->name('frontend.room.go.checkout');
    Route::get('/checkout', 'FrontEnd\BookingController@checkout')->name('frontend.room.checkout');
    Route::post('/apply-coupon', 'FrontEnd\BookingController@applyCoupon')->name('frontend.room.apply_coupon');
    Route::post('/add-additional-service', 'FrontEnd\BookingController@additonalService')->name('frontend.room.add_additional_service');

    Route::get('room-addto/wishlist/{id}', 'FrontEnd\UserController@add_to_wishlist_room')->name('addto.wishlist.room');
    Route::get('room-remove/wishlist/{id}', 'FrontEnd\UserController@remove_wishlist_room')->name('remove.wishlist.room');

    Route::get('/store-visitor', 'FrontEnd\RoomController@store_visitor')->name('frontend.store_visitor');

    Route::prefix('/booking')->group(function () {

      Route::post('', 'FrontEnd\BookingPayment\BookingController@index')->name('frontend.room.room_booking')->middleware('Demo');

      Route::get('/paypal/notify', 'FrontEnd\BookingPayment\PayPalController@notify')->name('frontend.room.room_booking.paypal.notify');
      Route::get('/mollie/notify', 'FrontEnd\BookingPayment\MollieController@notify')->name('frontend.room.room_booking.mollie.notify');
      Route::get('/instamojo/notify', 'FrontEnd\BookingPayment\InstamojoController@notify')->name('frontend.room.room_booking.instamojo.notify');
      Route::post('/razorpay/notify', 'FrontEnd\BookingPayment\RazorpayController@notify')->name('frontend.room.room_booking.razorpay.notify');
      Route::post('/paytm/notify', 'FrontEnd\BookingPayment\PaytmController@notify')->name('frontend.room.room_booking.paytm.notify');
      Route::get('/flutterwave/notify', 'FrontEnd\BookingPayment\FlutterwaveController@notify')->name('frontend.room.room_booking.flutterwave.notify');
      Route::get('/paystack/notify', 'FrontEnd\BookingPayment\PaystackController@notify')->name('frontend.room.room_booking.paystack.notify');
      Route::get('/mercadopago/notify', 'FrontEnd\BookingPayment\MercadoPagoController@notify')->name('frontend.room.room_booking.mercadopago.notify');
      Route::get('/midtrans/notify', 'FrontEnd\BookingPayment\MidtransController@creditCardNotify')->name('frontend.room.room_booking.midtrans.notify');
      Route::any('/phonepe/notify', 'FrontEnd\BookingPayment\PhonepeController@notify')->name('frontend.room.room_booking.phonepe.notify');
      Route::get('/yoco/notify', 'FrontEnd\BookingPayment\YocoController@notify')->name('frontend.room.room_booking.yoco.notify');
      Route::get('/toyyibpay/notify', 'FrontEnd\BookingPayment\ToyyibpayController@notify')->name('frontend.room.room_booking.toyyibpay.notify');
      Route::post('/paytabs/notify', 'FrontEnd\BookingPayment\PaytabsController@notify')->name('frontend.room.room_booking.paytabs.notify');
      Route::get('/perfect-money/notify', 'FrontEnd\BookingPayment\PerfectMoneyController@notify')->name('frontend.room.room_booking.perfect_money.notify');
      Route::get('/xendit/notify', 'FrontEnd\BookingPayment\XenditController@notify')->name('frontend.room.room_booking.xendit.notify');
      Route::post('/iyzico/notify', 'FrontEnd\BookingPayment\IyzicoController@notify')->name('frontend.room.room_booking.iyzico.notify');

      Route::get('/complete/{type?}', 'FrontEnd\BookingPayment\BookingController@complete')->name('frontend.room_booking.complete');
      Route::get('/cancel', 'FrontEnd\BookingPayment\BookingController@cancel')->name('frontend.room_booking.cancel');
    });
  });

  Route::redirect('/hotels', '/sites', 301);
  Route::redirect('/hotels/search-hotel', '/sites/search-site', 301);
  Route::prefix('sites')->group(function () {
    Route::get('/', 'FrontEnd\HotelController@index')->name('frontend.hotels');
    Route::get('/search-site', 'FrontEnd\HotelController@search_hotel')->name('frontend.search_hotel');

    Route::post('/get-states', 'FrontEnd\HotelController@getState')->name('frontend.hotels.get-state');
    Route::post('/get-cities', 'FrontEnd\HotelController@getCity')->name('frontend.hotels.get-city');

    Route::get('hotel-addto/wishlist/{id}', 'FrontEnd\UserController@add_to_wishlist_hotel')->name('addto.wishlist.hotel');
    Route::get('hotel-remove/wishlist/{id}', 'FrontEnd\UserController@remove_wishlist_hotel')->name('remove.wishlist.hotel');
  });
  Route::get('/hotel/{slug}/{id}', function ($slug, $id) {
    return redirect()->route('frontend.hotel.details', compact('slug', 'id'), 301);
  });
  Route::get('/site/{slug}/{id}', 'FrontEnd\HotelController@details')->name('frontend.hotel.details');

  Route::prefix('/blog')->group(function () {
    Route::get('', 'FrontEnd\BlogController@index')->name('blog');

    Route::get('/{slug}/{id}',  'FrontEnd\BlogController@details')->name('blog_details');
  });

  Route::get('/about-us', 'FrontEnd\HomeController@about')->name('about_us');
  Route::get('/terms-and-conditions', 'FrontEnd\HomeController@termsAndConditions')
    ->name('terms_and_conditions');
  Route::get('/vendor-site-agreement', 'FrontEnd\HomeController@vendorSiteAgreement')
    ->name('vendor_site_agreement');

  Route::prefix('/contact')->group(function () {

    Route::get('', 'FrontEnd\ContactController@contact')->name('contact');
    Route::post('/send-mail', 'FrontEnd\ContactController@sendMail')->name('contact.send_mail');
  });
});

Route::post('/advertisement/{id}/count-view', 'FrontEnd\MiscellaneousController@countAdView');

Route::prefix('login')->middleware(['guest:web', 'change.lang'])->group(function () {
  // user login via facebook route
  Route::prefix('/user/facebook')->group(function () {
    Route::get('', 'FrontEnd\UserController@redirectToFacebook')->name('user.login.facebook');

    Route::get('/callback', 'FrontEnd\UserController@handleFacebookCallback');
  });

  // user login via google route
  Route::prefix('/google')->group(function () {
    Route::get('', 'FrontEnd\UserController@redirectToGoogle')->name('user.login.google');

    Route::get('/callback', 'FrontEnd\UserController@handleGoogleCallback');
  });
});

Route::prefix('/user')->middleware(['guest:web', 'change.lang'])->group(function () {
  Route::prefix('/login')->group(function () {
    // user redirect to login page route
    Route::get('', 'FrontEnd\UserController@login')->name('user.login');
  });
  //user login submit route
  Route::post('/login-submit', 'FrontEnd\UserController@loginSubmit')->name('user.login_submit');


  // user forget password route
  Route::get('/forget-password', 'FrontEnd\UserController@forgetPassword')->name('user.forget_password');

  // send mail to user for forget password route
  Route::post('/send-forget-password-mail', 'FrontEnd\UserController@forgetPasswordMail')->name('user.send_forget_password_mail')->middleware('Demo');

  // reset password route
  Route::get('/reset-password', 'FrontEnd\UserController@resetPassword');

  // user reset password submit route
  Route::post('/reset-password-submit', 'FrontEnd\UserController@resetPasswordSubmit')->name('user.reset_password_submit')->middleware('Demo');

  // user redirect to signup page route
  Route::get('/signup', 'FrontEnd\UserController@signup')->name('user.signup');

  // user signup submit route
  Route::post('/signup-submit', 'FrontEnd\UserController@signupSubmit')->name('user.signup_submit')->middleware('Demo');

  // signup verify route
  Route::get('/signup-verify/{token}', 'FrontEnd\UserController@signupVerify');
});

Route::prefix('/user')->middleware(['auth:web', 'account.status', 'user.email.verify', 'change.lang'])->group(function () {
  // user redirect to dashboard route
  Route::get('/dashboard', 'FrontEnd\UserController@redirectToDashboard')->name('user.dashboard');
  Route::get('/wishlist/room', 'FrontEnd\UserController@roomWishlist')->name('user.wishlist.room');
  Route::get('/wishlist/hotel', 'FrontEnd\UserController@hotelWishlist')->name('user.wishlist.hotel');

  // edit profile route
  Route::get('/edit-profile', 'FrontEnd\UserController@editProfile')->name('user.edit_profile');

  // update profile route
  Route::post('/update-profile', 'FrontEnd\UserController@updateProfile')->name('user.update_profile')->middleware('Demo');

  // change password route
  Route::get('/change-password', 'FrontEnd\UserController@changePassword')->name('user.change_password');

  // update password route
  Route::post('/update-password', 'FrontEnd\UserController@updatePassword')->name('user.update_password')->middleware('Demo');

  //room booking
  Route::get('/court-bookings',  'FrontEnd\UserController@roomBooking')->name('user.room_bookings');

  Route::get('/court-booking/details/{id}', 'FrontEnd\UserController@bookingDetails')->name('user.room_booking_details');

  // user logout attempt route
  Route::get('/logout', 'FrontEnd\UserController@logoutSubmit')->name('user.logout');
});

// service unavailable route
Route::get('/service-unavailable', 'FrontEnd\MiscellaneousController@serviceUnavailable')->name('service_unavailable')->middleware('exists.down');

/*
|--------------------------------------------------------------------------
| admin frontend route
|--------------------------------------------------------------------------
*/

Route::prefix('/admin')->middleware('guest:admin')->group(function () {
  // admin redirect to login page route
  Route::get('/', 'Admin\AdminController@login')->name('admin.login');

  // admin login attempt route
  Route::post('/auth', 'Admin\AdminController@authentication')->name('admin.auth');

  // admin forget password route
  Route::get('/forget-password', 'Admin\AdminController@forgetPassword')->name('admin.forget_password');

  // send mail to admin for forget password route
  Route::post('/mail-for-forget-password', 'Admin\AdminController@forgetPasswordMail')->name('admin.mail_for_forget_password')->middleware('Demo');
});


/*
|--------------------------------------------------------------------------
| Custom Page Route For UI
|--------------------------------------------------------------------------
*/
Route::get('/{slug}', 'FrontEnd\PageController@page')->name('dynamic_page')->middleware('change.lang');

// fallback route
Route::fallback(function () {
  return view('errors.404');
})->middleware('change.lang');
