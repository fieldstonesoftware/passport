<?php

namespace Laravel\Passport;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Laravel\Passport\Contracts\ClientContract;

class Client extends Model implements ClientContract
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'oauth_clients';

    /**
     * The guarded attributes on the model.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'secret',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'grant_types' => 'array',
        'personal_access_client' => 'bool',
        'password_client' => 'bool',
        'revoked' => 'bool',
    ];

    /**
     * The temporary plain-text client secret.
     *
     * @var string|null
     */
    protected $plainSecret;

    /**
     * Get the user that the client belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(
            config('auth.providers.'.config('auth.guards.api.provider').'.model')
        );
    }

    /**
     * Get all of the authentication codes for the client.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function authCodes()
    {
        return $this->hasMany(Passport::authCodeModel(), 'client_id');
    }

    /**
     * Get all of the tokens that belong to the client.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tokens()
    {
        return $this->hasMany(Passport::tokenModel(), 'client_id');
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
     * Get the value of the client secret.
     *
     * @return string
     */
    public function getClientSecret()
    {
        return $this->secret;
    }

    /**
     * Get the grant types this client handles.
     *
     * @return string|array
     */
    public function getGrantTypes()
    {
        return $this->grant_types;
    }

    /**
     * Find a token by its identifier.
     *
     * @param $id
     * @return ClientContract
     */
    public function findById($id)
    {
        return Passport::clientModel()::where($this->getKeyName(), $id)->first();
    }

    /**
     * Get a token by the given user ID and token ID.
     *
     * @param  $clientId
     * @param  $userId
     * @return \Laravel\Passport\Contracts\ClientContract|null
     */
    public function findForUser($clientId, $userId)
    {
        return Passport::clientModel()::where($this->getKeyName(), $clientId)
                     ->where('user_id', $userId)
                     ->first();
    }

    /**
     * Get the client instances for the given user ID.
     *
     * @param  mixed  $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function forUser($userId)
    {
        return Passport::clientModel()::where('user_id', $userId)
            ->orderBy('name', 'asc')->get();
    }

    /**
     * Store a new client.
     *
     * @param  mixed  $userId
     * @param  string  $name
     * @param  string  $redirect
     * @param  bool  $personalAccess
     * @param  bool  $password
     * @param  bool  $confidential
     * @return \Laravel\Passport\Contracts\ClientContract
     */
    public function createClient($userId, $name, $redirect, $personalAccess = false, $password = false, $confidential = true)
    {
        $client = Passport::client()->forceFill([
            'user_id' => $userId,
            'name' => $name,
            'secret' => ($confidential || $personalAccess) ? Str::random(40) : null,
            'redirect' => $redirect,
            'personal_access_client' => $personalAccess,
            'password_client' => $password,
            'revoked' => false,
        ]);

        $client->save();

        return $client;
    }

    /**
     * Store a new personal access client based on the client.
     *
     * @return \Laravel\Passport\Contracts\PersonalAccessClientContract
     */
    public function createPersonalAccessClient()
    {
        $personalAccessClient = Passport::personalAccessClient()->forceFill(
            ['id' => $this->getKey(), 'client_id' => $this->getKey()]
        );

        $personalAccessClient->save();

        return $personalAccessClient;
    }

    /**
     * Update the name and redirect attributes of the client.
     *
     * @param  string  $name
     * @param  string  $redirect
     * @return \Laravel\Passport\Contracts\ClientContract
     */
    public function updateNameAndRedirect($name, $redirect)
    {
        $this->forceFill([
            'name' => $name, 'redirect' => $redirect,
        ])->save();

        return $this;
    }

    /**
     * Regenerate the client secret and save it in the client.
     *
     * @return \Laravel\Passport\Contracts\ClientContract
     */
    public function regenerateSecret()
    {
        $this->forceFill([
            'secret' => Str::random(40),
        ])->save();

        return $this;
    }

    /**
     * Revoke the client.
     *
     * @return bool
     */
    public function revoke()
    {
        return $this->forceFill(['revoked'=>true])->save();
    }

    /**
     * Revoke all tokens associated with the client.
     *
     * @return int
     */
    public function revokeTokens()
    {
        return $this->tokens()->update(['revoked'=>true]);
    }

    /**
     * Is this a personal access client
     *
     * @return bool
     */
    public function personalAccessClient()
    {
        return $this->personal_access_client;
    }

    /**
     * Is this a password client
     *
     * @return bool
     */
    public function passwordClient()
    {
        return $this->password_client;
    }

    /**
     * Return the client secret
     *
     * @return string
     */
    public function secret()
    {
        return $this->secret;
    }

    /**
     * Return the client redirect
     *
     * @return string
     */
    public function redirect()
    {
        return $this->redirect;
    }

    /**
     * Return the client name
     *
     * @return string
     */
    public function name()
    {
        return $this->name;
    }

    /**
     * The temporary non-hashed client secret.
     *
     * This is only available once during the request that created the client.
     *
     * @return string|null
     */
    public function getPlainSecretAttribute()
    {
        return $this->plainSecret;
    }

    /**
     * Set the value of the secret attribute.
     *
     * @param  string|null  $value
     * @return void
     */
    public function setSecretAttribute($value)
    {
        $this->plainSecret = $value;

        if (is_null($value) || ! Passport::$hashesClientSecrets) {
            $this->attributes['secret'] = $value;

            return;
        }

        $this->attributes['secret'] = password_hash($value, PASSWORD_BCRYPT);
    }

    /**
     * Determine if the client is a "first party" client.
     *
     * @return bool
     */
    public function firstParty()
    {
        return $this->personal_access_client || $this->password_client;
    }

    /**
     * Determine if the client should skip the authorization prompt.
     *
     * @return bool
     */
    public function skipsAuthorization()
    {
        return false;
    }

    /**
     * Determine if the client is a confidential client.
     *
     * @return bool
     */
    public function confidential()
    {
        return ! empty($this->secret);
    }
}
