<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\SitemapService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    public function __construct(
        protected SitemapService $sitemapService
    ) {}

    /**
     * Generate and return the XML sitemap.
     */
    public function index(): Response
    {
        $xml = Cache::remember('sitemap.xml', now()->addHours(1), function () {
            return $this->sitemapService->toXml();
        });

        return response($xml, 200, [
            'Content-Type' => 'application/xml',
        ]);
    }

    /**
     * Generate and return robots.txt with sitemap reference.
     */
    public function robots(): Response
    {
        $sitemapUrl = url('sitemap.xml');

        $content = <<<TXT
User-agent: *
Allow: /
Disallow: /admin/
Disallow: /login
Disallow: /register
Disallow: /dashboard
Disallow: /settings/

Sitemap: {$sitemapUrl}
TXT;

        return response($content, 200, [
            'Content-Type' => 'text/plain',
        ]);
    }
}
