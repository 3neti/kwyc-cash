<?php

namespace App\Traits;

use App\Models\Campaign;

trait HasMetaAttributes
{
    const REFERENCE_LABEL_FIELD = 'reference_label_field';

    public function initializeHasMetaAttributes(): void
    {
        $this->mergeFillable([
            'reference_label'
        ]);

        $this->appends = array_merge($this->appends, [
            'reference_label'
        ]);
    }

    public function setReferenceLabelAttribute(string $value): static
    {
        $this->getAttribute('meta')->set(self::REFERENCE_LABEL_FIELD, $value);

        return $this;
    }

    public function getReferenceLabelAttribute(): ?string
    {
        return $this->getAttribute('meta')->get(self::REFERENCE_LABEL_FIELD);
    }
}
