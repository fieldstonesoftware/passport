<?php

namespace Laravel\Passport;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Laravel\Passport\Contracts\TokenContract;

class Token extends Model implements TokenContract
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'oauth_access_tokens';

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

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
        'scopes' => 'array',
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
     * Get the client that the token belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function client()
    {
        return $this->belongsTo(Passport::clientModel());
    }

    /**
     * Get the user that the token belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        $provider = config('auth.guards.api.provider');

        return $this->belongsTo(config('auth.providers.'.$provider.'.model'));
    }

    /**
     * Find a token by its identifier.
     *
     * @param $id
     * @return TokenContract
     */
    public function findById($id)
    {
        return Passport::tokenModel()::where($this->getKeyName(),$id)->first();
    }

    /**
     * Get a token by the given user ID and token ID.
     *
     * @param  $tokenId
     * @param  $userId
     * @return \Laravel\Passport\Contracts\TokenContract|null
     */
    public function findForUser($tokenId, $userId)
    {
        return Passport::tokenModel()::where('id', $tokenId)
                    ->where('user_id',$userId)
                    ->first();
    }

    /**
     * Store a new token.
     *
     * @param  mixed  $id
     * @param  mixed  $userId
     * @param  mixed  $clientId
     * @param  string  $scopes
     * @param  bool  $revoked
     * @param  Carbon  $createdAt
     * @param  Carbon  $updatedAt
     * @param  Carbon  $expiredAt
     * @return \Laravel\Passport\Contracts\TokenContract
     */
    public function createToken($id, $userId, $clientId, $scopes, $revoked
        , $createdAt, $updateAt, $expiresAt)
    {
        $token = Passport::token()->forceFill([
            'id' => $id
            , 'user_id' => $userId
            , 'client_id' => $clientId
            , 'scopes' => $scopes
            , 'revoked' => $revoked
            , 'created_at' => $createdAt
            , 'updated_at' => $updateAt
            , 'expires_at' => $expiresAt
        ]);

        $token->save();

        return $token;
    }

    /**
     * Return all tokens for the user id.
     *
     * @param string $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function forUserId($userId)
    {
        return Passport::tokenModel()::where('user_id',$userId)->get();
    }

    /**
     * Return all tokens for the user id.
     *
     * @param \Illuminate\Database\Eloquent\Model $user
     * @param \Laravel\Passport\Contracts\ClientContract $client
     * @return \Laravel\Passport\Contracts\TokenContract|null
     */
    public function findValidToken($user, $client)
    {
        return Passport::tokenModel()::where('user_id',$user->getKey())
            ->where('client_id', $client->getKey())
            ->where('expires_at', '>', Carbon::now())
            ->where('revoked',0)
            ->latest('expires_at')
            ->first();
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
     * Revoke the token instance.
     *
     * @return bool
     */
    public function revoke()
    {
        return $this->forceFill(['revoked' => true])->save();
    }

    /**
     * Delete all revoked Tokens
     *
     * @return bool
     */
    public function deleteRevoked()
    {
        return Passport::tokenModel()::where('revoked',1)->delete();
    }

    /**
     * Delete expired tokens prior to the date provided.
     *
     * @param Carbon $expired
     * @return bool
     */
    public function deleteExpiredPriorTo(Carbon $expired)
    {
        return Passport::tokenModel()::where('expires_at', '<', $expired)->delete();
    }

    /**
     * Delete all revoked tokens and those expired prior to the date provided.
     *
     * @param Carbon $expired
     * @return bool
     */
    public function deleteRevokedOrExpiredPriorTo(Carbon $expired)
    {
        return Passport::tokenModel()::where('revoked',1)->orWhereDate('expires_at','<',$expired)->delete();
    }

    /**
     * Determine if the token has a given scope.
     *
     * @param  string  $scope
     * @return bool
     */
    public function can($scope)
    {
        if (in_array('*', $this->scopes)) {
            return true;
        }

        $scopes = Passport::$withInheritedScopes
            ? $this->resolveInheritedScopes($scope)
            : [$scope];

        foreach ($scopes as $scope) {
            if (array_key_exists($scope, array_flip($this->scopes))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Resolve all possible scopes.
     *
     * @param  string  $scope
     * @return array
     */
    protected function resolveInheritedScopes($scope)
    {
        $parts = explode(':', $scope);

        $partsCount = count($parts);

        $scopes = [];

        for ($i = 1; $i <= $partsCount; $i++) {
            $scopes[] = implode(':', array_slice($parts, 0, $i));
        }

        return $scopes;
    }

    /**
     * Determine if the token is missing a given scope.
     *
     * @param  string  $scope
     * @return bool
     */
    public function cant($scope)
    {
        return ! $this->can($scope);
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
