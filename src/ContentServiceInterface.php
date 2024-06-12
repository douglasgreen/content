<?php

declare(strict_types=1);

namespace DouglasGreen\Content;

interface ContentServiceInterface
{
    public function createContent(
        string $name,
        ?string $parentId,
        int $schemaId,
        string $contentXml,
    ): string;

    public function getContentById(string $contentId): ?array;

    public function updateContent(string $contentId, array $data): void;

    public function archiveContent(string $contentId): void;

    public function getContentByParentId(string $parentId): array;

    public function createContentRelationship(
        string $sourceContentId,
        string $targetContentId,
    ): int;

    public function getContentRelationshipsBySourceId(
        string $sourceContentId,
    ): array;

    public function getContentRelationshipsByTargetId(
        string $targetContentId,
    ): array;
}
