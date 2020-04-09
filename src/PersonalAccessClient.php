<?php

namespace Laravel\Passport;

use Illuminate\Database\Eloquent\Model;
use Laravel\Passport\Contracts\PersonalAccessClientContract;

class PersonalAccessClient extends Model implements PersonalAccessClientContract
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'oauth_personal_access_clients';

    /**
     * The guarded attributes on the model.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Get the client that the personal access client belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function client()
    {
        return $this->belongsTo(Passport::clientModel());
    }

    /**
     * Return the latest created personal access client.
     *
     * @return \Laravel\Passport\Contracts\PersonalAccessClientContract
     */
    public function getLatestCreated()
    {
        return Passport::personalAccessClient()::latest('created_at')->first();
    }
}
