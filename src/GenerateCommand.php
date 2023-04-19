<?php

namespace Webteractive\EE;

use Illuminate\Support\Str;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputOption;

#[AsCommand(name: 'generate', description: 'Generate ExpressionEngine add-on boilerplate')]
class GenerateCommand extends Command
{
    
    public function inputs()
    {
        return [
            new InputOption('plugin-only', 'p', null, 'Generate plugin only boilerplate'),
            new InputOption('extension-only', 'e', null, 'Generate extension only boilerplate'),
            new InputOption('overwrite', 'o', null, 'Overwrite existing boilerplate'),
        ];
    }

    protected $fileMap = [
        'common' => [
            'addon.setup',
            'Addon',
            'upd',
            'Service/Helpers/Migrations/Blueprint',
            'Service/Helpers/Migrations/CreateNewTable',
            'Service/Helpers/Migrations/ExtensionSchema',
            'Service/Helpers/Migrations/ModuleSchema',
            'Service/Helpers/Migrations/UpdateTable',
            'Service/Helpers/Migrations/WithTableFields',
            'Service/Helpers/Fields/Date',
            'Service/Helpers/Fields/BaseField',
            'Service/Helpers/Fields/Hidden',
            'Service/Helpers/Fields/Html',
            'Service/Helpers/Fields/Text',
            'Service/Helpers/Fields/Textarea',
            'Service/Helpers/View',
            'Service/Helpers/Jsonable',
            'language/english/lang',
        ],
        'module' => [
            'mod',
            'mcp'
        ]
    ];

    public function handle()
    {
        $name = $this->ask('Please enter the add-on name');
        $shortName = $this->ask('Please enter the add-on short name', Str::of($name)->replace('-', ' ')->snake());
        $description = $this->ask('Please enter the add-on description', '');
        $namespace = $this->ask('Please enter the add-on namespace', 'Acme\\'.Str::of($name)->replace('-', ' ')->camel()->ucfirst());
        $author = $this->ask('Please enter the add-on author', get_current_user());
        $authorUrl = $this->ask('Please enter the add-on author URL', '#');
        $docsUrl = $this->ask('Please enter the add-on documentation URL', '#');

        $storage = $this->makeStorage($shortName);

        $generating = 'module';

        // Create the folder
        $className = Str::of($shortName)->snake()->ucfirst()->toString();

        $values = collect([
            'name' => $name,
            'short_name' => $shortName,
            'version' => '1.0.0',
            'description' => $description,
            'namespace' => $namespace,
            'has_settings' => 'true',
            'ext_has_settings' => 'y',
            'author' => $author,
            'author_url' => $authorUrl,
            'docs_url' => $docsUrl,
            'upd_class' => $className . '_upd',
            'mod_class' => $className,
            'pi_class' => $className,
            'ext_class' => $className . '_ext',
            'mcp_class' => $className . '_mcp',
        ])->mapWithKeys(function($value, $key) {
            return ['__' . strtoupper($key) . '__' => $value];
        })->toArray();
        
        // if ($this->cwd()->directoryExists($shortName)) {
        //     $this->line("Add-on {$name} already exists.");
        //     return Command::FAILURE;
        // }

        $storage->cwd()->createDirectory($shortName);

        if (($files = $this->fileMap[$generating]) ?? false) {
            $this->writeAddonFiles($storage, $shortName, $this->fileMap['common'], $values);
            $this->writeAddonFiles($storage, $shortName, $files, $values);
        }

        $this->info("The <comment>{$name}</comment> add-on boilerplate has been generated and is located in <comment>{$storage->getAddonPath()}</comment>");

        return Command::SUCCESS;
    }

    public function writeAddonFiles(Storage $storage, $shortName, $files = [], $values = [])
    {
        collect($files)
            ->each(function($items) use ($storage, $shortName, $values) {
                collect($items)->each(function($item) use ($storage, $shortName, $values) {
                    $addonClassFiles = [
                        'mod',
                        'ext',
                        'pi',
                        'upd',
                        'mcp'
                    ];

                    $target = in_array($item, $addonClassFiles)
                        ? $item . '.' . $shortName
                        : $item;

                    if ($item == 'language/english/lang') {
                        $target = "language/english/{$shortName}_lang";
                    }

                    $source = class_basename($item) . '.stub';

                    $storage->addon()->write(
                        $target . '.php',
                        $this->readAndReplace($storage, $source, $values)
                    );
                });
            });

        return $this;
    }

    public function readAndReplace($storage, $location, $values = [])
    {
        $contents = $storage->stubs()->read($location);

        foreach ($values as $key => $value) {
            $contents = Str::replace($key, $value, $contents);
        }

        return $contents;
    }
}