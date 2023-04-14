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
        ];
    }

    protected $fileMap = [
        'common' => [
            'addon.setup',
            'Addon',
            'upd',
            'Blueprint',
            'CreateNewTable',
            'ExtensionSchema',
            'ModuleSchema',
            'UpdateTable',
            'WithTableFields'
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
        // $description = $this->ask('Please enter the add-on description');
        // $namespace = $this->ask('Please enter the add-on namespace');
        $cwd = getcwd();

        $generating = 'module';

        // Create the folder
        $className = Str::of($name)->ucfirst()->snake()->toString();

        $values = collect([
            'name' => $name,
            'short_name' => $shortName,
            'version' => '1.0.0',
            // 'description' => $description,
            // 'namespace' => $namespace,
            'description' => 'Cool add-on bro',
            'namespace' => 'EETech\Ekko',
            'has_settings' => 'true',
            'ext_has_settings' => 'y',
            'author' => 'Author',
            'author_url' => 'https://example.com',
            'docs_url' => 'https://example.com',
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

        $this->cwd()->createDirectory($shortName);

        if (($files = $this->fileMap[$generating]) ?? false) {
            $this->writeAddonFiles($shortName, $this->fileMap['common'], $values);
            $this->writeAddonFiles($shortName, $files, $values);
        }

        
        

        $this->line($name);
        $this->line(getcwd());

        return Command::SUCCESS;
    }

    public function writeAddonFiles($shortName, $files = [], $values = [])
    {
        $addonFiles = [
            'mod',
            'ext',
            'pi',
            'upd',
            'mcp'
        ];
        $helperFiles = [
            'Blueprint',
            'CreateNewTable',
            'ExtensionSchema',
            'ModuleSchema',
            'UpdateTable',
            'WithTableFields'
        ];
        foreach ($files as $key => $file) {
            $filename = in_array($file, $addonFiles)
                ? $file . '.' . $shortName
                : $file;

            $filename = in_array($filename, $helperFiles)
                ? 'Service/Helpers/' . $filename
                : $filename;

            $this->addon($shortName)
                ->write(
                    "{$filename}.php",
                    $this->readAndReplace("stubs/{$file}.stub", $values)
                );
        }

        return $this;
    }

    public function readAndReplace($location, $values = [])
    {
        $contents = $this->resources()->read($location);

        foreach ($values as $key => $value) {
            $contents = Str::replace($key, $value, $contents);
        }

        return $contents;
    }
}