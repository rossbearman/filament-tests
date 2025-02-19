<?php

namespace CodeWithDennis\FilamentTests\Stubs\Resource\Page\Index\Table\BulkAction;

use CodeWithDennis\FilamentTests\Stubs\Base;

class DeleteForce extends Base
{
    public function getDescription(): string
    {
        return 'can bulk force delete records';
    }

    public function getShouldGenerate(): bool
    {
        return $this->hasTableFilter('trashed', $this->getResourceTable($this->resource))
            && $this->hasSoftDeletes($this->resource)
            && $this->hasTableBulkAction('forceDelete', $this->resource);
    }
}
