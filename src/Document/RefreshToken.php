<?php

declare(strict_types=1);

namespace App\Document;

//use App\Repository\RefreshTokenRepository;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Gesdinet\JWTRefreshTokenBundle\Document\RefreshToken as BaseRefreshToken;

#[MongoDB\Document(collection: 'refresh_tokens')]
class RefreshToken extends BaseRefreshToken
{
}