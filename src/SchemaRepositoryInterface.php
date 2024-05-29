<?php

declare(strict_types=1);

namespace Content;

interface SchemaRepositoryInterface
{
    public function createSchema(string $name): int;

    public function getSchemaByName(string $name): ?array;
}
