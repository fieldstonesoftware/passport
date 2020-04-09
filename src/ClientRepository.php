<?php

namespace Laravel\Passport;

use RuntimeException;

class ClientRepository
{
    /**
     * Get a client by the given ID.
     *
     * @param  int  $id
     * @return \Laravel\Passport\Contracts\ClientContract|null
     */
    public function find($id)
    {
        return Passport::client()->findById($id);
    }

    /**
     * Get an active client by the given ID.
     *
     * @param  int  $id
     * @return \Laravel\Passport\Contracts\ClientContract|null
     */
    public function findActive($id)
    {
        $client = $this->find($id);

        return $client && ! $client->isRevoked() ? $client : null;
    }

    /**
     * Get a client instance for the given ID and user ID.
     *
     * @param  int  $clientId
     * @param  mixed  $userId
     * @return \Laravel\Passport\Contracts\ClientContract|null
     */
    public function findForUser($clientId, $userId)
    {
        return Passport::client()->findForUser($clientId, $userId);
    }

    /**
     * Get the client instances for the given user ID.
     *
     * @param  mixed  $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function forUser($userId)
    {
        return Passport::client()->forUser($userId);
    }

    /**
     * Get the active client instances for the given user ID.
     *
     * @param  mixed  $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function activeForUser($userId)
    {
        return $this->forUser($userId)->reject(
            function (\Laravel\Passport\Contracts\ClientContract $client) {
                return $client->isRevoked();
        })->values();
    }

    /**
     * Get the personal access token client for the application.
     *
     * @return \Laravel\Passport\Contracts\PersonalAccessClientContract
     *
     * @throws \RuntimeException
     */
    public function personalAccessClient()
    {
        if (Passport::$personalAccessClientId) {
            return $this->find(Passport::$personalAccessClientId);
        }

        $client = Passport::personalAccessClient();

        if (! $client->exists()) {
            throw new RuntimeException('Personal access client not found. Please create one.');
        }

        return $client->getLatestCreated()->client;
    }

    /**
     * Store a new client.
     *
     * @param  int  $userId
     * @param  string  $name
     * @param  string  $redirect
     * @param  bool  $personalAccess
     * @param  bool  $password
     * @param  bool  $confidential
     * @return \Laravel\Passport\Contracts\ClientContract
     */
    public function create($userId, $name, $redirect, $personalAccess = false, $password = false, $confidential = true)
    {
        return Passport::client()->createClient(
            $userId, $name, $redirect, $personalAccess, $password, $confidential
        );
    }

    /**
     * Store a new personal access token client.
     *
     * @param  int  $userId
     * @param  string  $name
     * @param  string  $redirect
     * @return \Laravel\Passport\Contracts\ClientContract
     */
    public function createPersonalAccessClient($userId, $name, $redirect)
    {
        return tap($this->create($userId, $name, $redirect, true)
            , function (\Laravel\Passport\Contracts\ClientContract $client) {
            $client->createPersonalAccessClient();
        });
    }

    /**
     * Store a new password grant client.
     *
     * @param  int  $userId
     * @param  string  $name
     * @param  string  $redirect
     * @return \Laravel\Passport\Contracts\ClientContract
     */
    public function createPasswordGrantClient($userId, $name, $redirect)
    {
        return $this->create($userId, $name, $redirect, false, true);
    }

    /**
     * Update the given client.
     *
     * @param  \Laravel\Passport\Contracts\ClientContract  $client
     * @param  string  $name
     * @param  string  $redirect
     * @return \Laravel\Passport\Contracts\ClientContract
     */
    public function update(\Laravel\Passport\Contracts\ClientContract $client, $name, $redirect)
    {
        return $client->updateNameAndRedirect($name, $redirect);
    }

    /**
     * Regenerate the client secret.
     *
     * @param  \Laravel\Passport\Contracts\ClientContract  $client
     * @return \Laravel\Passport\Contracts\ClientContract
     */
    public function regenerateSecret(\Laravel\Passport\Contracts\ClientContract $client)
    {
        return $client->regenerateSecret();
    }

    /**
     * Determine if the given client is revoked.
     *
     * @param  int  $id
     * @return bool
     */
    public function revoked($id)
    {
        $client = $this->find($id);

        return is_null($client) || $client->isRevoked();
    }

    /**
     * Delete the given client.
     *
     * @param  \Laravel\Passport\Contracts\ClientContract  $client
     * @return void
     */
    public function delete(\Laravel\Passport\Contracts\ClientContract $client)
    {
        $client->revokeTokens();
        $client->revoke();
    }
}
