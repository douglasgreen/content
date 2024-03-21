<?php

namespace Content;

interface SchemaVersionRepositoryInterface {
    public function createSchemaVersion(int $schemaId, int $version, string $xmlContent): int;
    public function getSchemaVersionById(int $id): ?array;
    public function getLatestSchemaVersionBySchemaId(int $schemaId): ?array;
}
