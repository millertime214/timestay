<?php

namespace Tests\Unit;

use App\Models\Booking;
use App\Models\Court;
use App\Models\Site;
use App\Models\Vendor;
use App\Models\VendorEwallet;
use App\Models\CourtBlockedSlot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;
use App\Support\PackageBilling;
use App\Models\Package;
use App\Http\Requests\Checkout\ExtendRequest;
use Carbon\Carbon;

class PickleballDomainTest extends TestCase
{
    public function test_site_and_court_aliases_use_the_compatible_tables(): void
    {
        $this->assertSame('hotels', (new Site())->getTable());
        $this->assertSame('rooms', (new Court())->getTable());
    }

    public function test_site_court_vendor_and_booking_relationships_are_available(): void
    {
        $this->assertInstanceOf(HasMany::class, (new Site())->courts());
        $this->assertInstanceOf(BelongsTo::class, (new Court())->site());
        $this->assertInstanceOf(HasMany::class, (new Vendor())->sites());
        $this->assertInstanceOf(HasMany::class, (new Vendor())->courts());
        $this->assertInstanceOf(BelongsTo::class, (new Booking())->site());
        $this->assertInstanceOf(BelongsTo::class, (new Booking())->court());
    }

    public function test_named_public_routes_generate_pickleball_urls(): void
    {
        $this->assertStringEndsWith('/sites', route('frontend.hotels'));
        $this->assertStringEndsWith('/courts', route('frontend.rooms'));
        $this->assertStringEndsWith('/site/central-pickleball/7', route('frontend.hotel.details', ['central-pickleball', 7]));
        $this->assertStringEndsWith('/court/championship-court/11', route('frontend.room.details', ['championship-court', 11]));
        $this->assertStringEndsWith('/terms-and-conditions', route('terms_and_conditions'));
        $this->assertStringEndsWith('/vendor-site-agreement', route('vendor_site_agreement'));
        $this->assertStringEndsWith('/admin/hotel-management/verification/7', route('admin.hotel_management.verification.update', 7));
        $this->assertStringEndsWith('/admin/hotel-management/verification/7/document/business_permit_document', route('admin.hotel_management.verification.document', [7, 'business_permit_document']));
        $this->assertStringEndsWith('/admin/hotel-management/verification/7/document/business_permit_document/preview', route('admin.hotel_management.verification.document.preview', [7, 'business_permit_document']));
    }

    public function test_legacy_translation_keys_render_pickleball_language(): void
    {
        $this->app->setLocale('en');

        $this->assertSame('Site', __('Hotel'));
        $this->assertSame('Sites', __('Hotels'));
        $this->assertSame('Court', __('Room'));
        $this->assertSame('Courts', __('Rooms'));
        $this->assertSame('Adult Player', __('Adult'));
        $this->assertSame('Junior Players', __('Children'));
        $this->assertSame('Start Date', __('Check-in Date'));
        $this->assertSame('End Time', __('Check-out Time'));
        $this->assertSame('Book Pickleball Courts by the Hour', __('Discover the Freedom of Hourly Hotel Bookings for Relax'));
        $this->assertSame('Your Pickleball Sports Hub', __('Your Ultimate Hourly Hotel Booking Solution'));
    }

    public function test_vendor_ewallet_domain_and_routes_are_available(): void
    {
        $this->assertInstanceOf(HasMany::class, (new Vendor())->ewallets());
        $this->assertInstanceOf(BelongsTo::class, (new VendorEwallet())->vendor());
        $this->assertInstanceOf(BelongsTo::class, (new Booking())->vendorEwallet());
        $this->assertStringEndsWith('/vendor/e-wallets', route('vendor.ewallets.index'));
    }

    public function test_vendor_ewallet_schema_is_installed(): void
    {
        $this->assertTrue(Schema::hasTable('vendor_ewallets'));
        $this->assertTrue(Schema::hasColumns('vendor_ewallets', [
            'vendor_id', 'name', 'account_number', 'verified_name', 'tin_number',
            'dti_business_name_number', 'qr_code', 'profile_screenshot',
            'business_registration_document', 'mayors_permit_document', 'status',
        ]));
        $this->assertTrue(Schema::hasColumns('bookings', [
            'vendor_ewallet_id', 'payment_transaction_id', 'payer_phone',
        ]));
    }

    public function test_vendor_court_schedule_domain_is_available(): void
    {
        $this->assertInstanceOf(BelongsTo::class, (new CourtBlockedSlot())->court());
        $this->assertInstanceOf(BelongsTo::class, (new CourtBlockedSlot())->vendor());
        $this->assertStringEndsWith('/vendor/court-schedule', route('vendor.court_schedule.index'));
        $this->assertTrue(Schema::hasColumns('court_blocked_slots', [
            'vendor_id', 'room_id', 'starts_at', 'ends_at', 'reason', 'slot_type', 'title', 'category', 'price_per_player', 'max_players', 'poster',
        ]));
        $this->assertStringEndsWith('/vendor/court-schedule/open-play', route('vendor.court_schedule.open_play.store'));
        $this->assertStringEndsWith('/court/open-play/9', route('frontend.open_play.show', 9));
        $this->assertStringEndsWith('/vendor/court-display', route('vendor.court_display.index'));
        $this->assertStringEndsWith('/vendor/court-display/data', route('vendor.court_display.data'));
        $this->assertTrue(Schema::hasColumn('bookings', 'open_play_slot_id'));
        $this->assertTrue(Schema::hasColumn('bookings', 'open_play_players'));
        $this->assertTrue(Schema::hasColumn('memberships', 'billing_term'));
        $this->assertTrue(Schema::hasColumns('packages', ['monthly_discount', 'semi_annual_discount', 'annual_discount', 'monthly_term_active', 'semi_annual_term_active', 'annual_term_active']));
        $this->assertTrue(Schema::hasColumns('hotels', ['business_permit_document', 'business_registration_document', 'mayors_permit_document', 'verification_status', 'verification_notes', 'verified_by', 'verified_at', 'site_agreement_version', 'site_agreement_accepted_at']));
        $this->assertInstanceOf(BelongsTo::class, (new Booking())->openPlaySlot());
    }

    public function test_package_billing_uses_monthly_base_price(): void
    {
        $this->assertSame(100.0, PackageBilling::total(100, 'monthly'));
        $this->assertSame(600.0, PackageBilling::total(100, 'semi-annual'));
        $this->assertSame(1200.0, PackageBilling::total(100, 'yearly'));
        $this->assertSame(540.0, PackageBilling::total(100, 'semi-annual', 10));
        $this->assertSame(900.0, PackageBilling::total(100, 'yearly', 25));
        $this->assertSame('2026-07-14', PackageBilling::expiresAt(Carbon::parse('2026-01-15'), 'semi-annual')->toDateString());
    }

    public function test_package_exposes_only_active_payment_terms(): void
    {
        $package = new Package([
            'monthly_term_active' => true,
            'semi_annual_term_active' => false,
            'annual_term_active' => true,
        ]);

        $this->assertSame(['monthly', 'yearly'], array_keys($package->enabledBillingTerms()));
        $this->assertTrue($package->isBillingTermEnabled('monthly'));
        $this->assertFalse($package->isBillingTermEnabled('semi-annual'));
    }

    public function test_vendor_package_checkout_requires_terms_acceptance(): void
    {
        $rules = (new ExtendRequest())->rules();

        $this->assertSame('required|accepted', $rules['subscription_terms_accepted']);
    }

    public function test_only_active_verified_sites_are_bookable(): void
    {
        $site = new Site(['status' => 1, 'verification_status' => 'approved']);
        $this->assertTrue($site->isBookable());

        $site->verification_status = 'pending';
        $this->assertFalse($site->isBookable());

        $site->verification_status = 'approved';
        $site->status = 0;
        $this->assertFalse($site->isBookable());
    }

    public function test_pending_sites_are_publicly_visible_but_not_bookable(): void
    {
        $site = new Site(['status' => 0, 'verification_status' => 'pending']);

        $this->assertTrue($site->isPubliclyVisible());
        $this->assertFalse($site->isBookable());

        $site->verification_status = 'rejected';
        $this->assertFalse($site->isPubliclyVisible());
    }

    public function test_vendor_site_and_court_creation_do_not_require_package_middleware(): void
    {
        $siteRoute = Route::getRoutes()->getByName('vendor.hotel_management.store_hotel');
        $courtRoute = Route::getRoutes()->getByName('vendor.room_management.store_room');

        $this->assertNotNull($siteRoute);
        $this->assertNotNull($courtRoute);
        $this->assertNotContains('packageLimitsCheck:hotel,store', $siteRoute->gatherMiddleware());
        $this->assertNotContains('packageLimitsCheck:room,store', $courtRoute->gatherMiddleware());
    }
}
