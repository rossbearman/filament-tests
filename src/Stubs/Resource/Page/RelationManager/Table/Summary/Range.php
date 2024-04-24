<?php

namespace CodeWithDennis\FilamentTests\Stubs\Resource\Page\RelationManager\Table\Summary;

use Closure;
use CodeWithDennis\FilamentTests\Stubs\Base;

class Range extends Base
{
    public Closure|bool $isTodo = true;

    public function getDescription(): string
    {
        return 'can range values in a column on relation manager';
    }

    public function getShouldGenerate(): bool
    {
        return $this->hasRelationManagers();
    }
}
