<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\SitemapService;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class GenerateSitemap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sitemap:generate
                            {--path= : Custom path to write the sitemap file}
                            {--clear-cache : Clear the sitemap cache after generation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate the XML sitemap';

    /**
     * Execute the console command.
     */
    public function handle(SitemapService $sitemapService): int
    {
        $this->info('Generating sitemap...');

        $path = $this->option('path') ?? public_path('sitemap.xml');

        try {
            $sitemap = $sitemapService->generate();
            $sitemap->writeToFile($path);

            $this->info("Sitemap generated successfully at: {$path}");

            // Get URL count from sitemap
            $urlCount = count($sitemap->getTags());
            $this->info("Total URLs in sitemap: {$urlCount}");

            if ($this->option('clear-cache')) {
                Cache::forget('sitemap.xml');
                $this->info('Sitemap cache cleared.');
            }

            return self::SUCCESS;
        } catch (Exception $e) {
            $this->error("Failed to generate sitemap: {$e->getMessage()}");

            return self::FAILURE;
        }
    }
}
