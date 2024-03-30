<?php

namespace Content;

interface ContentRelationshipRepositoryInterface {
    public function createContentRelationship(string $sourceContentId, string $targetContentId): int;
    public function getContentRelationshipsBySourceId(string $sourceContentId): array;
    public function getContentRelationshipsByTargetId(string $targetContentId): array;
}