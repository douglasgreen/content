<?php

declare(strict_types=1);

namespace DouglasGreen\Content;

interface SchemaRepositoryInterface
{
    public function createSchema(string $name): int;

    public function getSchemaByName(string $name): ?array;
}
