<?php

namespace Content;

interface ContentRepositoryInterface {
    public function createContent(string $id, ?string $parentId, string $name, int $schemaVersionId, string $contentXml): void;
    public function getContentById(string $id): ?array;
    public function updateContent(string $id, array $data): void;
    public function archiveContent(string $id): void;
    public function getContentByParentId(string $parentId): array;
}
