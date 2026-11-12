<?php

namespace App\Console\Commands;

use App\Services\OboorMarketPriceImporter;
use Illuminate\Console\Command;

class ImportOboorMarketPrices extends Command
{
    protected $signature = 'oboor:import-prices {--no-images : Import prices without downloading product images}';

    protected $description = 'Import fruit wholesale prices from Oboor Market into the storefront catalog.';

    public function handle(OboorMarketPriceImporter $importer): int
    {
        $this->info('Importing Oboor Market prices...');

        $summary = $importer->import(downloadImages: !$this->option('no-images'));

        $this->line('Source: ' . $summary['source_url']);
        $this->line('Fruits: ' . ($summary['groups']['fruits'] ?? 0));
        $this->line('Created: ' . $summary['created']);
        $this->line('Updated: ' . $summary['updated']);
        $this->info('Total imported: ' . $summary['total']);

        return self::SUCCESS;
    }
}
