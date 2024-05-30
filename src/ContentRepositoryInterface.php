<?php

declare(strict_types=1);

namespace DouglasGreen\Content;

interface ContentRepositoryInterface
{
    public function createContent(
        string $contentId,
        ?string $parentId,
        string $name,
        int $schemaVersionId,
        string $contentXml
    ): void;

    public function getContentById(string $contentId): ?array;

    public function updateContent(string $contentId, array $data): void;

    public function archiveContent(string $contentId): void;

    public function getContentByParentId(string $parentId): array;
}
