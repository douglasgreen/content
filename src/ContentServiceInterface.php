<?php

namespace Content;

interface ContentServiceInterface {
    public function createContent(string $name, ?string $parentId, int $schemaId, string $contentXml): string;
    public function getContentById(string $id): ?array;
    public function updateContent(string $id, array $data): void;
    public function archiveContent(string $id): void;
    public function getContentByParentId(string $parentId): array;
    public function createContentRelationship(string $sourceContentId, string $targetContentId): int;
    public function getContentRelationshipsBySourceId(string $sourceContentId): array;
    public function getContentRelationshipsByTargetId(string $targetContentId): array;
}
