<?php

namespace CodeWithDennis\FilamentTests\Stubs\Resource\Page\Edit\RelationManager;

use CodeWithDennis\FilamentTests\Stubs\Base;

class Render extends Base
{
    public function getDescription(): string
    {
        return 'can render the '.str($this->relationManager)->basename()->snake()->replace('_', ' ').' on the edit page';
    }

    public function getShouldGenerate(): bool
    {
        return $this->getRelationManagerTableColumns($this->relationManager)->isNotEmpty();
    }

    public function getVariables(): array
    {
        return [
            'RELATION_MANAGER_NAME' => str($this->relationManager)->basename(),
            'RELATION_MANAGER_CLASS' => $this->relationManager.'::class',
            'RELATION_MANAGER_RELATIONSHIP_MODEL' => $this->getRelationManagerRelationshipNameToModelClass($this->relationManager),
            'RELATION_MANAGER_RELATIONSHIP_NAME' => str($this->getRelationManager($this->relationManager)->getRelationshipName())->ucfirst(),
        ];
    }
}
