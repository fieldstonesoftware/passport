<?php

namespace Laravel\Passport\Tests;

use Illuminate\Support\Carbon;
use Laravel\Passport\Bridge\RefreshTokenRepository as BridgeRefreshTokenRepository;
use Laravel\Passport\Contracts\TokenContract;
use Laravel\Passport\RefreshTokenRepository;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class BridgeRefreshTokenRepositoryTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
    }

    public function test_it_can_determine_if_a_refresh_token_is_revoked()
    {
        $refreshToken = new RevokedRefreshToken;
        $repository = $this->repository($refreshToken);

        $this->assertTrue($repository->isRefreshTokenRevoked('tokenId'));
    }

    public function test_a_refresh_token_is_also_revoked_if_it_cannot_be_found()
    {
        $refreshToken = null;
        $repository = $this->repository($refreshToken);

        $this->assertTrue($repository->isRefreshTokenRevoked('tokenId'));
    }

    public function test_it_can_determine_if_a_refresh_token_is_not_revoked()
    {
        $refreshToken = new ActiveRefreshToken;
        $repository = $this->repository($refreshToken);

        $this->assertFalse($repository->isRefreshTokenRevoked('tokenId'));
    }

    private function repository($refreshToken): BridgeRefreshTokenRepository
    {
        $refreshTokenRepository = m::mock(RefreshTokenRepository::class)->makePartial();
        $refreshTokenRepository->shouldReceive('find')
            ->with('tokenId')
            ->andReturn($refreshToken);

        $events = m::mock('Illuminate\Contracts\Events\Dispatcher');

        return new BridgeRefreshTokenRepository($refreshTokenRepository, $events);
    }
}

class ActiveRefreshToken implements TokenContract
{
    public $revoked = false;

    /**
     * @inheritDoc
     */
    public function isRevoked()
    {
        return $this->revoked;
    }

    // methods below not required for this test.
    public function client(){}
    public function user(){}
    public function createToken($id, $userId, $clientId, $scopes, $revoked, $createdAt, $updateAt, $expiresAt){}
    public function findById($id){}
    public function findForUser($tokenId, $userId){}
    public function findValidToken($user, $client){}
    public function forUserId($userId){}
    public function revoke(){}
    public function deleteRevoked(){}
    public function deleteExpiredPriorTo(Carbon $expired){}
    public function deleteRevokedOrExpiredPriorTo(Carbon $expired){}
    public function save(){}
}

class RevokedRefreshToken implements TokenContract
{
    public $revoked = true;

    /**
     * @inheritDoc
     */
    public function isRevoked()
    {
        return $this->revoked;
    }

    // methods below not required for this test.
    public function client(){}
    public function user(){}
    public function createToken($id, $userId, $clientId, $scopes, $revoked, $createdAt, $updateAt, $expiresAt){}
    public function findById($id){}
    public function findForUser($tokenId, $userId){}
    public function findValidToken($user, $client){}
    public function forUserId($userId){}
    public function revoke(){}
    public function deleteRevoked(){}
    public function deleteExpiredPriorTo(Carbon $expired){}
    public function deleteRevokedOrExpiredPriorTo(Carbon $expired){}
    public function save(){}
}
