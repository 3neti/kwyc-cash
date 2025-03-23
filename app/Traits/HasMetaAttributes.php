<?php

namespace App\Traits;

use App\Models\Campaign;

trait HasMetaAttributes
{
    const REFERENCE_LABEL_FIELD = 'reference_label';
    const DEDICATION_FIELD = 'dedication';

    public function initializeHasMetaAttributes(): void
    {
        $this->mergeFillable([
            self::REFERENCE_LABEL_FIELD,
            self::DEDICATION_FIELD
        ]);

        $this->appends = array_merge($this->appends, [
            self::REFERENCE_LABEL_FIELD,
            self::DEDICATION_FIELD
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

    public function setDedicationAttribute(string $value): static
    {
        $this->getAttribute('meta')->set(self::DEDICATION_FIELD, $value);

        return $this;
    }

    public function getDedicationAttribute(): ?string
    {
        return $this->getAttribute('meta')->get(self::DEDICATION_FIELD);
    }
}
