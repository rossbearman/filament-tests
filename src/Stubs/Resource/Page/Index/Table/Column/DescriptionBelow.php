<?php

namespace CodeWithDennis\FilamentTests\Stubs\Resource\Page\Index\Table\Column;

use CodeWithDennis\FilamentTests\Stubs\Base;

class DescriptionBelow extends Base
{
    public function getDescription(): string
    {
        return 'has the correct descriptions below';
    }

    public function getShouldGenerate(): bool
    {
        return $this->getDescriptionBelowColumns($this->resource)->isNotEmpty();
    }

    public function getVariables(): array
    {
        return [
            'RESOURCE_TABLE_DESCRIPTIONS_BELOW_COLUMNS' => $this->transformToPestDataset($this->getTableColumnDescriptionBelow($this->resource), ['column', 'description']),
        ];
    }
}
