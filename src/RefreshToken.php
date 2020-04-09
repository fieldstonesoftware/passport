<?php

namespace Laravel\Passport;

use DateTimeImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Laravel\Passport\Contracts\RefreshTokenContract;

class RefreshToken extends Model implements RefreshTokenContract
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'oauth_refresh_tokens';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

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
     * Get the access token that the refresh token belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function accessToken()
    {
        return $this->belongsTo(Passport::tokenModel());
    }

    /**
     * Revoke the token instance.
     *
     * @return bool
     */
    public function revoke()
    {
        return $this->forceFill(['revoked' => true])->save();
    }

    /**
     * Store a new token.
     *
     * @param  mixed  $id
     * @param  mixed  $access_token_id
     * @param  bool  $revoked
     * @param  DateTimeImmutable  $expires_at
     * @return \Laravel\Passport\Contracts\RefreshTokenContract
     */
    public function createRefreshToken($id, $access_token_id, $revoked, $expires_at)
    {
        $refreshToken = Passport::refreshToken()->forceFill([
            'id' => $id
            , 'access_token_id' => $access_token_id
            , 'revoked' => $revoked
            , 'expires_at' => $expires_at
        ]);

        $refreshToken->save();

        return $refreshToken;
    }

    /**
     * Find a refresh token by its identifier.
     *
     * @param $id
     * @return RefreshTokenContract
     */
    public function findById($id)
    {
        return Passport::refreshTokenModel()::where('id', $id)->first();
    }

    /**
     * Revoke all refresh tokens with this access token ID.
     *
     * @param $accessTokenId
     * @return mixed
     */
    public function revokeWithAccessTokenId($accessTokenId)
    {
        return Passport::refreshTokenModel()::where('access_token_id', $accessTokenId)
            ->update(['revoked'=>true]);
    }

    /**
     * Is this token revoked
     *
     * @return bool
     */
    public function isRevoked()
    {
        return $this->revoked;
    }

    /**
     * Delete all revoked refresh tokens.
     *
     * @return bool
     */
    public function deleteRevoked()
    {
        return Passport::refreshTokenModel()::where('revoked',1)->delete();
    }

    /**
     * Delete expired refresh tokens prior to the date provided.
     *
     * @param Carbon $expired
     * @return bool
     */
    public function deleteExpiredPriorTo(Carbon $expired)
    {
        return Passport::refreshTokenModel()::where('expires_at', '<', $expired)->delete();
    }

    /**
     * Delete all revoked refresh tokens and those expired prior to the date provided.
     *
     * @param Carbon $expired
     * @return bool
     */
    public function deleteRevokedOrExpiredPriorTo(Carbon $expired)
    {
        return Passport::refreshTokenModel()::where('revoked',1)->orWhereDate('expires_at','<',$expired)->delete();
    }

    /**
     * Determine if the token is a transient JWT token.
     *
     * @return bool
     */
    public function transient()
    {
        return false;
    }
}
