<?php

namespace Content;

interface ContentValidatorInterface {
    public function validateContent(string $contentXml, string $schemaXml): bool;
}
