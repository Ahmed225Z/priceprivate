<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;

class PruneGeneratedPdfs extends Command
{
    protected $signature = 'prints:prune';

    protected $description = 'Delete generated print PDFs older than 24 hours (SRS section 10)';

    public function handle(): int
    {
        $n = 0;
        foreach (glob(storage_path('app/prints/*.pdf')) ?: [] as $f) {
            if (filemtime($f) < now()->subDay()->getTimestamp()) {
                unlink($f);
                $n++;
            }
        }
        $this->info("Deleted {$n} file(s).");

        return self::SUCCESS;
    }
}
