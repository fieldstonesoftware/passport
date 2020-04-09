<?php

namespace Laravel\Passport\Tests;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Events\Dispatcher;
use Laravel\Passport\Bridge\AccessToken;
use Laravel\Passport\Bridge\AccessTokenRepository;
use Laravel\Passport\Bridge\Client;
use Laravel\Passport\Bridge\Scope;
use Laravel\Passport\TokenRepository;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class BridgeAccessTokenRepositoryTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
    }

    public function test_access_tokens_can_be_persisted()
    {
        $expiration = CarbonImmutable::now();

        $tokenRepository = m::mock(TokenRepository::class);
        $events = m::mock(Dispatcher::class);

        $testScopes = 'scopes';
        $testClientId = 'client-id';
        $testName = 'name';
        $testRedirect = 'redirect';

        $tokenRepository->shouldReceive('create')->once()->andReturnUsing(
            function ($id, $userId, $clientId, $scopes, $revoked, $created_at, $updated_at, $expires_at) use ($expiration) {
            $this->assertEquals(1, $id);
            $this->assertEquals(2, $userId);
            $this->assertEquals('client-id', $clientId);
            $this->assertEquals(['scopes'], $scopes);
            $this->assertEquals(false, $revoked);
            $this->assertInstanceOf('DateTime', $created_at);
            $this->assertInstanceOf('DateTime', $updated_at);
            $this->assertEquals($expiration, $expires_at);
        });

        $events->shouldReceive('dispatch')->once();

        $accessToken = new AccessToken(2, [new Scope($testScopes)], new Client($testClientId, $testName, $testRedirect));
        $accessToken->setIdentifier(1);
        $accessToken->setExpiryDateTime($expiration);

        $repository = new AccessTokenRepository($tokenRepository, $events);

        $repository->persistNewAccessToken($accessToken);
    }

    public function test_can_get_new_access_token()
    {
        $tokenRepository = m::mock(TokenRepository::class);
        $events = m::mock(Dispatcher::class);
        $repository = new AccessTokenRepository($tokenRepository, $events);
        $client = new Client('client-id', 'name', 'redirect');
        $scopes = [new Scope('place-orders'), new Scope('check-status')];
        $userIdentifier = 123;

        $token = $repository->getNewToken($client, $scopes, $userIdentifier);

        $this->assertInstanceOf(AccessToken::class, $token);
        $this->assertEquals($client, $token->getClient());
        $this->assertEquals($scopes, $token->getScopes());
        $this->assertEquals($userIdentifier, $token->getUserIdentifier());
    }
}
