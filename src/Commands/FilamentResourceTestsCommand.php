<?php

namespace CodeWithDennis\FilamentResourceTests\Commands;

use Filament\Facades\Filament;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Stringable;

use function Laravel\Prompts\select;

class FilamentResourceTestsCommand extends Command
{
    protected $signature = 'make:filament-resource-test {name?}';

    protected $description = 'Create a new test for a Filament resource.';

    protected ?string $resourceName = '';

    protected Filesystem $files;

    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    protected function getStubPath(): string
    {
        return __DIR__.'/../../stubs/Resource.stub';
    }

    protected function getStubVariables(): array
    {
        $name = $this->resourceName;

        return [
            'resource' => $this->getResourceName(),
            'model' => $this->getModel(),
            'singular_name' => $this->getResourceSingularName(),
            'singular_name_lowercase' => $this->getResourceSingularName()->lower(),
            'plural_name' => $this->getResourcePluralName(),
            'plural_name_lowercase' => $this->getResourcePluralName()->lower(),
            'table_columns_exist_test' => $this->generateTableColumnsExistTest(),
        ];
    }

    protected function getResourceSingularName(): Stringable
    {
        return str($this->resourceName)->singular()->remove('resource', false);
    }

    protected function getResourcePluralName(): Stringable
    {
        return str($this->resourceName)->plural()->remove('resource', false);
    }

    protected function getSourceFile(): array|bool|string
    {
        return $this->getStubContents($this->getStubPath(), $this->getStubVariables());
    }

    protected function getStubContents($stub, $stubVariables = []): array|bool|string
    {
        $contents = file_get_contents($stub);

        foreach ($stubVariables as $search => $replace) {
            $contents = str_replace('$'.$search.'$', $replace, $contents);
        }

        return $contents;
    }

    protected function getSourceFilePath(): string
    {
        $directory = trim(config('filament-resource-tests.directory_name'), '/');

        if (config('filament-resource-tests.separate_tests_into_folders')) {
            $directory .= DIRECTORY_SEPARATOR.$this->resourceName;
        }

        return $directory.DIRECTORY_SEPARATOR.$this->getResourceName().'Test.php';
    }

    protected function makeDirectory($path): string
    {
        if (! $this->files->isDirectory($path)) {
            $this->files->makeDirectory($path, 0777, true, true);
        }

        return $path;
    }

    protected function getModel(): ?string
    {
        return $this->getResourceClass()?->getModel();
    }

    protected function getResourceName(): ?string
    {
        return str($this->resourceName)->endsWith('Resource') ?
            $this->resourceName :
            $this->resourceName.'Resource';
    }

    protected function getResourceClass(): ?Resource
    {
        $match = $this->getResources()
            ->first(fn ($resource): bool => str_contains($resource, $this->getResourceName()) && class_exists($resource));

        return $match ? app()->make($match) : null;
    }

    protected function getResources(): Collection
    {
        return collect(Filament::getResources());
    }

    protected function getTable(): Table
    {
        $livewire = app('livewire')->new(ListRecords::class);

        return $this->getResourceClass()::table(new Table($livewire));
    }

    protected function getResourceTableColumns(): array
    {
        return $this->getTable()->getColumns();
    }

    protected function getResourceSortableTableColumns(): Collection
    {
        return collect($this->getResourceTableColumns())->filter(fn ($column) => $column->isSortable());
    }

    protected function getResourceSearchableTableColumns(): Collection
    {
        return collect($this->getResourceTableColumns())->filter(fn ($column) => $column->isSearchable());
    }

    protected function getResourceTableFilters(): array
    {
        return $this->getTable()->getFilters();
    }

    protected function generateTableColumnsExistTest(): string
    {
        $columns = $this->getResourceTableColumns();
        $tests = '';

        foreach (collect($columns)->keys() as $key) {
            $label = str_replace(['.', '_'], ' ', $key);

            $tests .= <<<EOT
            it('can render {$this->getResourceSingularName()->lower()} {$label} column', function () {
                {$this->getResourceSingularName()}::factory()->count(3)->create();
                livewire(List{$this->getResourcePluralName()}::class)->assertCanRenderTableColumn('{$key}');
            });
            
            
            EOT;
        }

        return $tests;
    }

    public function handle(): void
    {
        // Get the resource name from the command argument
        $this->resourceName = $this->argument('name');

        // Get all available resources
        $availableResources = $this->getResources()
            ->map(fn ($resource): string => str($resource)->afterLast('Resources\\'));

        // Ask the user for the resource
        $this->resourceName = (string) str(
            $this->resourceName ?? select(
                label: 'What is the resource you would like to create this test for?',
                options: $availableResources->flatten(),
                required: true,
            ),
        )
            ->studly()
            ->trim(' / ')
            ->trim('\\')
            ->trim(' ')
            ->replace(' / ', '\\');

        // If the resource does not end with 'Resource', append it
        if (! str($this->resourceName)->endsWith('Resource')) {
            $this->resourceName .= 'Resource';
        }

        // Check if the resource exists
        if (! $this->getResourceClass()) {
            $this->warn("The filament resource {$this->resourceName} does not exist.");

            return;
        }

        // Get the source file path
        $path = $this->getSourceFilePath();

        // Make the directory if it does not exist
        $this->makeDirectory(dirname($path));

        // Get the source file contents
        $contents = $this->getSourceFile();

        // Check if the test already exists
        if ($this->files->exists($path)) {
            $this->warn("Test for {$this->getResourceName()} already exists.");

            return;
        }

        // Write the file
        $this->files->put($path, $contents);

        // Output success message
        $this->info("Test for {$this->getResourceName()} created successfully.");
    }
}
