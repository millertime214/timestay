<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\BasicSettings\Basic;
use App\Models\Booking;
use App\Models\Language;
use App\Models\RoomContent;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $filters = $this->validatedFilters($request);
        $vendorId = (int) Auth::guard('vendor')->id();
        $language = $this->reportLanguage($request);
        $baseQuery = $this->bookingQuery($vendorId, $filters);

        $paidQuery = (clone $baseQuery)->where('payment_status', 1);
        $summary = [
            'total_bookings' => (clone $baseQuery)->count(),
            'paid_bookings' => (clone $paidQuery)->count(),
            'gross_sales' => (float) (clone $paidQuery)->sum('grand_total'),
            'outstanding' => (float) (clone $baseQuery)->where('payment_status', 0)->sum('grand_total'),
            'rejected_bookings' => (clone $baseQuery)->where('payment_status', 2)->count(),
            'booked_hours' => (float) (clone $baseQuery)->where('payment_status', '!=', 2)->sum('hour'),
        ];

        $transactionTotals = Transaction::query()
            ->join('bookings', 'bookings.id', '=', 'transactions.booking_id')
            ->where('transactions.vendor_id', $vendorId)
            ->where('transactions.transcation_type', 'room_booking')
            ->where('transactions.payment_status', 1)
            ->where('bookings.payment_status', 1)
            ->when($filters['date_from'], fn ($query, $date) => $query->whereDate('bookings.check_in_date', '>=', $date))
            ->when($filters['date_to'], fn ($query, $date) => $query->whereDate('bookings.check_in_date', '<=', $date))
            ->when($filters['room_id'], fn ($query, $roomId) => $query->where('bookings.room_id', $roomId))
            ->when($filters['payment_status'] !== null, fn ($query) => $query->where('bookings.payment_status', $filters['payment_status']))
            ->when($filters['payment_method'], fn ($query, $method) => $query->where('bookings.payment_method', $method))
            ->when($filters['classification'], fn ($query, $classification) => $query->where('bookings.classification', $classification))
            ->selectRaw('COALESCE(SUM(transactions.grand_total), 0) as gross_received')
            ->selectRaw('COALESCE(SUM(transactions.commission), 0) as commission')
            ->first();

        $summary['payments_received'] = (float) $transactionTotals->gross_received - (float) $transactionTotals->commission;
        $summary['commission'] = (float) $transactionTotals->commission;
        $summary['average_booking'] = $summary['paid_bookings'] > 0
            ? $summary['gross_sales'] / $summary['paid_bookings']
            : 0;

        $courtSales = $this->aggregateQuery($vendorId, $filters)
            ->leftJoin('room_contents', function ($join) use ($language) {
                $join->on('room_contents.room_id', '=', 'bookings.room_id')
                    ->where('room_contents.language_id', '=', $language->id);
            })
            ->selectRaw("bookings.room_id, COALESCE(room_contents.title, CONCAT('Court #', bookings.room_id)) as court_title")
            ->selectRaw('COUNT(bookings.id) as booking_count')
            ->selectRaw('SUM(CASE WHEN bookings.payment_status = 1 THEN 1 ELSE 0 END) as paid_booking_count')
            ->selectRaw('COALESCE(SUM(CASE WHEN bookings.payment_status = 1 THEN bookings.grand_total ELSE 0 END), 0) as sales')
            ->selectRaw('COALESCE(SUM(CASE WHEN bookings.payment_status != 2 THEN bookings.hour ELSE 0 END), 0) as booked_hours')
            ->groupBy('bookings.room_id', 'room_contents.title')
            ->orderByDesc('sales')
            ->get();

        $paymentMethods = $this->aggregateQuery($vendorId, $filters)
            ->where('bookings.payment_status', 1)
            ->selectRaw("COALESCE(NULLIF(bookings.payment_method, ''), 'Not specified') as label")
            ->selectRaw('COUNT(*) as booking_count, COALESCE(SUM(bookings.grand_total), 0) as total')
            ->groupBy('bookings.payment_method')
            ->orderByDesc('total')
            ->get();

        $classifications = $this->aggregateQuery($vendorId, $filters)
            ->selectRaw("COALESCE(NULLIF(bookings.classification, ''), 'private') as label")
            ->selectRaw('COUNT(*) as booking_count, COALESCE(SUM(CASE WHEN bookings.payment_status = 1 THEN bookings.grand_total ELSE 0 END), 0) as total')
            ->groupBy('bookings.classification')
            ->orderByDesc('booking_count')
            ->get();

        $dailySales = $this->aggregateQuery($vendorId, $filters)
            ->where('bookings.payment_status', 1)
            ->selectRaw('bookings.check_in_date as report_date, COUNT(*) as booking_count, COALESCE(SUM(bookings.grand_total), 0) as total')
            ->groupBy('bookings.check_in_date')
            ->orderByDesc('bookings.check_in_date')
            ->limit(31)
            ->get();

        $bookings = (clone $baseQuery)
            ->with(['hotelRoom.room_content' => fn ($query) => $query->where('language_id', $language->id)])
            ->orderByDesc('check_in_date')
            ->orderByDesc('check_in_time')
            ->paginate(20)
            ->withQueryString();

        $courts = RoomContent::query()
            ->join('rooms', 'rooms.id', '=', 'room_contents.room_id')
            ->where('rooms.vendor_id', $vendorId)
            ->where('room_contents.language_id', $language->id)
            ->orderBy('room_contents.title')
            ->get(['rooms.id', 'room_contents.title']);

        $paymentMethodOptions = Booking::query()->where('vendor_id', $vendorId)
            ->whereNotNull('payment_method')->where('payment_method', '!=', '')
            ->distinct()->orderBy('payment_method')->pluck('payment_method');

        $defaultLang = $language;
        $settings = Basic::query()->select('time_format')->first();

        return view('vendors.reports.index', compact(
            'summary', 'courtSales', 'paymentMethods', 'classifications', 'dailySales',
            'bookings', 'courts', 'paymentMethodOptions', 'filters', 'defaultLang', 'settings'
        ));
    }

    public function export(Request $request)
    {
        $filters = $this->validatedFilters($request);
        $vendorId = (int) Auth::guard('vendor')->id();
        $language = $this->reportLanguage($request);
        $fileName = 'booking-sales-report-' . now()->format('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () use ($vendorId, $filters, $language) {
            $output = fopen('php://output', 'w');
            fputcsv($output, [
                'Booking No.', 'Court', 'Booking Date', 'Start Time', 'Hours', 'Customer',
                'Classification', 'Payment Method', 'Payment Status', 'Gross Total', 'Currency',
            ]);

            $this->bookingQuery($vendorId, $filters)
                ->with(['hotelRoom.room_content' => fn ($query) => $query->where('language_id', $language->id)])
                ->orderBy('id')
                ->chunkById(250, function ($bookings) use ($output) {
                    foreach ($bookings as $booking) {
                        $court = $booking->hotelRoom?->room_content?->first();
                        fputcsv($output, [
                            $booking->order_number,
                            $court->title ?? 'Court #' . $booking->room_id,
                            $booking->check_in_date,
                            $booking->check_in_time,
                            $booking->hour,
                            $booking->booking_name,
                            $booking->classification ?: 'private',
                            $booking->payment_method,
                            $this->paymentStatusLabel((int) $booking->payment_status),
                            $booking->grand_total,
                            $booking->currency_text,
                        ]);
                    }
                });

            fclose($output);
        }, $fileName, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function validatedFilters(Request $request): array
    {
        $validated = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'room_id' => ['nullable', 'integer'],
            'payment_status' => ['nullable', 'in:0,1,2'],
            'payment_method' => ['nullable', 'string', 'max:100'],
            'classification' => ['nullable', 'in:private,exclusive,open_play,training'],
        ]);

        return array_merge([
            'date_from' => Carbon::now()->startOfMonth()->toDateString(),
            'date_to' => Carbon::now()->toDateString(),
            'room_id' => null,
            'payment_status' => null,
            'payment_method' => null,
            'classification' => null,
        ], $validated);
    }

    private function bookingQuery(int $vendorId, array $filters): Builder
    {
        return Booking::query()->where('vendor_id', $vendorId)
            ->when($filters['date_from'], fn ($query, $date) => $query->whereDate('check_in_date', '>=', $date))
            ->when($filters['date_to'], fn ($query, $date) => $query->whereDate('check_in_date', '<=', $date))
            ->when($filters['room_id'], fn ($query, $roomId) => $query->where('room_id', $roomId))
            ->when($filters['payment_status'] !== null, fn ($query) => $query->where('payment_status', $filters['payment_status']))
            ->when($filters['payment_method'], fn ($query, $method) => $query->where('payment_method', $method))
            ->when($filters['classification'], fn ($query, $classification) => $query->where('classification', $classification));
    }

    private function aggregateQuery(int $vendorId, array $filters)
    {
        $query = DB::table('bookings')->where('bookings.vendor_id', $vendorId)
            ->when($filters['date_from'], fn ($builder, $date) => $builder->whereDate('bookings.check_in_date', '>=', $date))
            ->when($filters['date_to'], fn ($builder, $date) => $builder->whereDate('bookings.check_in_date', '<=', $date))
            ->when($filters['room_id'], fn ($builder, $roomId) => $builder->where('bookings.room_id', $roomId))
            ->when($filters['payment_status'] !== null, fn ($builder) => $builder->where('bookings.payment_status', $filters['payment_status']))
            ->when($filters['payment_method'], fn ($builder, $method) => $builder->where('bookings.payment_method', $method))
            ->when($filters['classification'], fn ($builder, $classification) => $builder->where('bookings.classification', $classification));

        return $query;
    }

    private function reportLanguage(Request $request): Language
    {
        return Language::query()->where('code', $request->input('language'))->first()
            ?? Language::query()->where('is_default', 1)->firstOrFail();
    }

    private function paymentStatusLabel(int $status): string
    {
        return match ($status) {
            1 => 'Paid',
            2 => 'Rejected',
            default => 'Pending',
        };
    }
}
