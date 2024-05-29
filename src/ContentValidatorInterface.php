<?php

declare(strict_types=1);

namespace Content;

interface ContentValidatorInterface
{
    public function validateContent(string $contentXml, string $schemaXml): bool;
}
