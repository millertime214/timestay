<?php

namespace App\Http\Controllers\FrontEnd;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\Journal\Blog;
use App\Models\Language;
use App\Models\Room;
use App\Models\Vendor;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $language = Language::where('is_default', 1)->firstOrFail();
        $urls = $this->staticUrls();

        $sites = Hotel::query()
            ->join('hotel_contents', 'hotel_contents.hotel_id', '=', 'hotels.id')
            ->where('hotel_contents.language_id', $language->id)
            ->where('hotels.status', 1)
            ->where('hotels.verification_status', 'approved')
            ->whereNotNull('hotel_contents.slug')
            ->select([
                'hotels.id',
                'hotels.updated_at as listing_updated_at',
                'hotel_contents.slug',
                'hotel_contents.updated_at as content_updated_at',
            ])
            ->get();

        foreach ($sites as $site) {
            $urls[] = $this->urlEntry(
                route('frontend.hotel.details', ['slug' => $site->slug, 'id' => $site->id]),
                $this->latestDate($site->listing_updated_at, $site->content_updated_at),
                'weekly',
                '0.8'
            );
        }

        $courts = Room::query()
            ->join('room_contents', 'room_contents.room_id', '=', 'rooms.id')
            ->join('hotels', 'hotels.id', '=', 'rooms.hotel_id')
            ->where('room_contents.language_id', $language->id)
            ->where('rooms.status', 1)
            ->where('hotels.status', 1)
            ->where('hotels.verification_status', 'approved')
            ->whereNotNull('room_contents.slug')
            ->select([
                'rooms.id',
                'rooms.updated_at as listing_updated_at',
                'room_contents.slug',
                'room_contents.updated_at as content_updated_at',
            ])
            ->get();

        foreach ($courts as $court) {
            $urls[] = $this->urlEntry(
                route('frontend.room.details', ['slug' => $court->slug, 'id' => $court->id]),
                $this->latestDate($court->listing_updated_at, $court->content_updated_at),
                'weekly',
                '0.8'
            );
        }

        $vendors = Vendor::query()
            ->join('memberships', 'memberships.vendor_id', '=', 'vendors.id')
            ->where('vendors.status', 1)
            ->where('memberships.status', 1)
            ->whereDate('memberships.start_date', '<=', now())
            ->whereDate('memberships.expire_date', '>=', now())
            ->whereNotNull('vendors.username')
            ->select('vendors.username', 'vendors.updated_at')
            ->distinct()
            ->get();

        foreach ($vendors as $vendor) {
            $urls[] = $this->urlEntry(
                route('frontend.vendor.details', ['username' => $vendor->username]),
                $vendor->updated_at,
                'weekly',
                '0.6'
            );
        }

        $blogs = Blog::query()
            ->join('blog_informations', 'blog_informations.blog_id', '=', 'blogs.id')
            ->where('blog_informations.language_id', $language->id)
            ->where('blogs.status', 1)
            ->whereNotNull('blog_informations.slug')
            ->select([
                'blogs.id',
                'blogs.updated_at as blog_updated_at',
                'blog_informations.slug',
                'blog_informations.updated_at as content_updated_at',
            ])
            ->get();

        foreach ($blogs as $blog) {
            $urls[] = $this->urlEntry(
                route('blog_details', ['slug' => $blog->slug, 'id' => $blog->id]),
                $this->latestDate($blog->blog_updated_at, $blog->content_updated_at),
                'monthly',
                '0.6'
            );
        }

        return response()
            ->view('frontend.sitemap', compact('urls'))
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    public function robots(): Response
    {
        $content = "User-agent: *\nAllow: /\n\nSitemap: " . route('sitemap') . "\n";

        return response($content, 200)->header('Content-Type', 'text/plain; charset=UTF-8');
    }

    private function staticUrls(): array
    {
        return [
            $this->urlEntry(route('index'), null, 'daily', '1.0'),
            $this->urlEntry(route('frontend.hotels'), null, 'daily', '0.9'),
            $this->urlEntry(route('frontend.rooms'), null, 'daily', '0.9'),
            $this->urlEntry(route('frontend.vendors'), null, 'weekly', '0.7'),
            $this->urlEntry(route('frontend.pricing'), null, 'monthly', '0.6'),
            $this->urlEntry(route('blog'), null, 'weekly', '0.7'),
            $this->urlEntry(route('about_us'), null, 'monthly', '0.5'),
            $this->urlEntry(route('faq'), null, 'monthly', '0.5'),
            $this->urlEntry(route('contact'), null, 'monthly', '0.5'),
            $this->urlEntry(route('terms_and_conditions'), null, 'yearly', '0.3'),
        ];
    }

    private function urlEntry(string $location, $lastModified, string $changeFrequency, string $priority): array
    {
        return [
            'loc' => $location,
            'lastmod' => $lastModified ? Carbon::parse($lastModified)->toAtomString() : null,
            'changefreq' => $changeFrequency,
            'priority' => $priority,
        ];
    }

    private function latestDate($first, $second)
    {
        if (!$first) {
            return $second;
        }

        if (!$second) {
            return $first;
        }

        $first = Carbon::parse($first);
        $second = Carbon::parse($second);

        return $first->greaterThan($second) ? $first : $second;
    }
}
