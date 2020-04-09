<?php

namespace Laravel\Passport\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Laravel\Passport\Passport;

class PurgeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'passport:purge
                            {--revoked : Only purge revoked tokens and authentication codes}
                            {--expired : Only purge expired tokens and authentication codes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Purge revoked and / or expired tokens and authentication codes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $expired = Carbon::now()->subDays(7);

        if (($this->option('revoked') && $this->option('expired')) ||
            (! $this->option('revoked') && ! $this->option('expired'))) {
            Passport::token()->deleteRevokedOrExpiredPriorTo($expired);
            Passport::authCode()->deleteRevokedOrExpiredPriorTo($expired);
            Passport::refreshToken()->deleteRevokedOrExpiredPriorTo($expired);

            $this->info('Purged revoked items and items expired for more than seven days.');
        } elseif ($this->option('revoked')) {
            Passport::token()->deleteRevoked();
            Passport::authCode()->deleteRevoked();
            Passport::refreshToken()->deleteRevoked();

            $this->info('Purged revoked items.');
        } elseif ($this->option('expired')) {
            Passport::token()->deleteExpiredPriorTo($expired);
            Passport::authCode()->deleteExpiredPriorTo($expired);
            Passport::refreshToken()->deleteExpiredPriorTo($expired);

            $this->info('Purged items expired for more than seven days.');
        }
    }
}
