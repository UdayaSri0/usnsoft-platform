<?php

namespace App\Contracts\Audit;

interface AuditableEntity
{
    public function auditDescription(): string;

    /**
     * @return array<string, mixed>
     */
    public function auditMetadata(): array;
}
