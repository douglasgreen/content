<?php

declare(strict_types=1);

namespace DouglasGreen\Content;

interface SchemaVersionRepositoryInterface
{
    public function createSchemaVersion(
        int $schemaId,
        int $version,
        string $xmlContent,
    ): int;

    public function getSchemaVersionById(int $versionId): ?array;

    public function getLatestSchemaVersionBySchemaId(int $schemaId): ?array;
}
