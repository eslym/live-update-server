<?php

namespace App\Console\Commands\Version;

use App\Models\VersionResolution;
use Illuminate\Console\Command;

class ReIndexCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:version:reindex';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Re-index the version resolutions';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $resolutions = VersionResolution::where('needs_reindex', true)->get();

        $projects = [];

        $bar = $this->output->createProgressBar($resolutions->count());

        $resolutions->each(function (VersionResolution $resolution) use ($bar) {
            $resolution->reIndex($projects[$resolution->project_id]);
            $bar->advance();
        });

        $bar->finish();

        $this->info('Done');
    }
}
