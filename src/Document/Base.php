<?php

declare(strict_types=1);

namespace App\Document;

use App\Data\Helpers\MongoHelper;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

#[MongoDB\MappedSuperclass]
abstract class Base
{
    #[MongoDB\Id]
    protected string $id;

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }
}
