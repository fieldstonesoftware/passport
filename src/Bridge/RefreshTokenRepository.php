<?php

namespace Laravel\Passport\Bridge;

use Illuminate\Contracts\Events\Dispatcher;
use Laravel\Passport\Events\RefreshTokenCreated;
use Laravel\Passport\RefreshTokenRepository as PassportRefreshTokenRepository;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;

class RefreshTokenRepository implements RefreshTokenRepositoryInterface
{
    /**
     * The refresh token repository instance.
     *
     * @var \Laravel\Passport\RefreshTokenRepository
     */
    protected $refreshTokenRepository;

    /**
     * The event dispatcher instance.
     *
     * @var \Illuminate\Contracts\Events\Dispatcher
     */
    protected $events;

    /**
     * Create a new repository instance.
     *
     * @param  \Laravel\Passport\RefreshTokenRepository  $refreshTokenRepository
     * @param  \Illuminate\Contracts\Events\Dispatcher  $events
     * @return void
     */
    public function __construct(PassportRefreshTokenRepository $refreshTokenRepository, Dispatcher $events)
    {
        $this->events = $events;
        $this->refreshTokenRepository = $refreshTokenRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getNewRefreshToken()
    {
        return new RefreshToken;
    }

    /**
     * {@inheritdoc}
     */
    public function persistNewRefreshToken(RefreshTokenEntityInterface $refreshTokenEntity)
    {
        $id = $refreshTokenEntity->getIdentifier();
        $accessTokenId = $refreshTokenEntity->getAccessToken()->getIdentifier();

        $this->refreshTokenRepository->create(
            $id, $accessTokenId, false, $refreshTokenEntity->getExpiryDateTime()
        );

        $this->events->dispatch(new RefreshTokenCreated($id, $accessTokenId));
    }

    /**
     * {@inheritdoc}
     */
    public function revokeRefreshToken($tokenId)
    {
        $this->refreshTokenRepository->revokeRefreshToken($tokenId);
    }

    /**
     * {@inheritdoc}
     */
    public function isRefreshTokenRevoked($tokenId)
    {
        return $this->refreshTokenRepository->isRefreshTokenRevoked($tokenId);
    }
}
