<?php

namespace Laravel\Passport;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Laravel\Passport\Contracts\AuthCodeContract;

class AuthCode extends Model implements AuthCodeContract
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'oauth_auth_codes';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The guarded attributes on the model.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'revoked' => 'bool',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'expires_at',
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Get the client that owns the authentication code.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function client()
    {
        return $this->belongsTo(Passport::clientModel());
    }

    /**
     * Store a new auth code.
     *
     * @param $id
     * @param $userId
     * @param $clientId
     * @param $scopes
     * @param $revoked
     * @param $expiresAt
     * @return AuthCodeContract
     */
    public function createAuthCode(
        $id, $userId, $clientId, $scopes, $revoked, $expiresAt
    ){
        $authCode = Passport::authCode()->forceFill(
            ['id' => $id
                , 'user_id' => $userId
                , 'client_id' => $clientId
                , 'scopes' => $scopes
                , 'revoked' => $revoked
                , 'expires_at' => $expiresAt
            ]
        );

        $authCode->save();

        return $authCode;
    }

    /**
     * Find a client by its identifier.
     *
     * @param $id
     * @return AuthCodeContract
     */
    public function findById($id)
    {
        return Passport::authCodeModel()::where('id', $id)->first();
    }

    /**
     * Is this code revoked.
     *
     * @return bool
     */
    public function isRevoked()
    {
        return $this->revoked;
    }

    /**
     * Revoke the auth code
     * @return bool
     */
    public function revoke()
    {
        $this->revoked = true;
        return $this->save();
    }

    /**
     * Delete all revoked Auth Codes
     *
     * @return bool
     */
    public function deleteRevoked()
    {
        return Passport::authCodeModel()::where('revoked',1)->delete();
    }

    /**
     * Delete expired auth codes prior to the date provided.
     *
     * @param Carbon $expired
     * @return bool
     */
    public function deleteExpiredPriorTo(Carbon $expired)
    {
        return Passport::authCodeModel()::where('expires_at', '<', $expired)->delete();
    }

    /**
     * Delete all revoked tokens and those expired prior to the date provided.
     *
     * @param Carbon $expired
     * @return bool
     */
    public function deleteRevokedOrExpiredPriorTo(Carbon $expired)
    {
        return Passport::authCodeModel()::where('revoked',1)->orWhereDate('expires_at','<',$expired)->delete();
    }

}
