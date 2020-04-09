<?php

namespace Laravel\Passport\Contracts;

interface PersonalAccessClientContract
{
    /**
     * Get the client that owns the personal access client.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function client();

    /**
     * Return the latest created personal access client.
     *
     * @return \Laravel\Passport\Contracts\PersonalAccessClientContract
     */
    public function getLatestCreated();
}
