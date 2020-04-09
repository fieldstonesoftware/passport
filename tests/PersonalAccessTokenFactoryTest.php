<?php

namespace Laravel\Passport\Tests;

use Laravel\Passport\ClientRepository;
use Laravel\Passport\Contracts\ClientContract;
use Laravel\Passport\Contracts\TokenContract;
use Laravel\Passport\PersonalAccessTokenFactory;
use Laravel\Passport\PersonalAccessTokenResult;
use Laravel\Passport\Token;
use Laravel\Passport\TokenRepository;
use Lcobucci\JWT\Parser;
use League\OAuth2\Server\AuthorizationServer;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class PersonalAccessTokenFactoryTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
    }

    public function test_access_token_can_be_created()
    {
        $server = m::mock(AuthorizationServer::class);
        $clients = m::mock(ClientRepository::class);
        $tokens = m::mock(TokenRepository::class);
        $jwt = m::mock(Parser::class);

        $factory = new PersonalAccessTokenFactory($server, $clients, $tokens, $jwt);

        $clients->shouldReceive('personalAccessClient')->andReturn($client = new PersonalAccessTokenFactoryTestClientStub);
        $server->shouldReceive('respondToAccessTokenRequest')->andReturn($response = m::mock());
        $response->shouldReceive('getBody->__toString')->andReturn(json_encode([
            'access_token' => 'foo',
        ]));

        $jwt->shouldReceive('parse')->with('foo')->andReturn($parsedToken = m::mock());
        $parsedToken->shouldReceive('getClaim')->with('jti')->andReturn('token');
        $tokens->shouldReceive('find')
            ->with('token')
            ->andReturn($foundToken = new PersonalAccessTokenFactoryTestModelStub);
        $tokens->shouldReceive('save')->with($foundToken);

        $result = $factory->make(1, 'token', ['scopes']);

        $this->assertInstanceOf(PersonalAccessTokenResult::class, $result);
    }
}

class PersonalAccessTokenFactoryTestClientStub implements ClientContract
{
    public $id = 1;

    public $secret = 'something';

    /**
     * @inheritDoc
     */
    public function getKey()
    {
        return $this->id;
    }

    /**
     * @inheritDoc
     */
    public function secret()
    {
        return $this->secret;
    }

    // Methods below not required for this test.
    public function user(){}
    public function authCodes(){}
    public function tokens(){}
    public function createClient($userId, $name, $redirect, $personalAccess = false, $password = false, $confidential = true){}
    public function createPersonalAccessClient(){}
    public function getClientSecret(){}
    public function getGrantTypes(){}
    public function personalAccessClient(){}
    public function passwordClient(){}
    public function confidential(){}
    public function redirect(){}
    public function name(){}
    public function findById($id){}
    public function findForUser($clientId, $userId) {}
    public function forUser($userId){}
    public function isRevoked(){}
    public function updateNameAndRedirect($name, $redirect){}
    public function regenerateSecret(){}
    public function revokeTokens(){}
    public function revoke(){}
}

class PersonalAccessTokenFactoryTestModelStub extends Token implements TokenContract
{
    public $id = 1;

    public $secret = 'something';
}
