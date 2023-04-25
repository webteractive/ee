<?php

namespace Webteractive\EE;

use Illuminate\Support\Str;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

#[AsCommand(name: 'generate', description: 'Generate ExpressionEngine add-on boilerplate.')]
class GenerateCommand extends Command
{
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


    public function inputs()
    {
        return [
            new InputArgument('type', InputArgument::OPTIONAL, 'The type of add-on to generate. Only ' . collect(AddonFiles::types())->map(fn ($item) => "<comment>{$item}</comment>")->join(', ', ', and ') . ' are supported.', 'module'),
            new InputOption('overwrite', 'o', null, 'Overwrite existing boilerplate.'),
        ];
    }

    public function handle()
    {
        $generating = $this->input->getArgument('type');

        if (!in_array($generating, AddonFiles::types())) {
            $this->line('');
            $this->line("The supplied add-on type <comment>{$generating}</comment> is invalid.");
            $this->line('');
            return Command::FAILURE;
        }

        // Check if cwd has is in ee root - find system

        if ($name = $this->ask('Please enter the add-on name')) {
            $shortName = $this->ask('Please enter the add-on short name', Str::of($name)->replace('-', ' ')->snake());

            if ($this->cwd()->directoryExists($shortName)) {
                if ($this->input->getOption('overwrite') === null) {
                    $this->warn("The <error> {$shortName} </error> add-on already exists.");
                    if ($this->ask('Do you want to overwrite the existing files', 'no', 'comment') === 'no') {
                        $this->info('Alright. Nothing to do here, bye!');
                        return Command::FAILURE;
                    }
                } else {
                    $this->warn("The <error> {$shortName} </error> add-on already exists and will be overwritten.");
                }
            }

            $description = $this->ask('Please enter the add-on description', '');
            $namespace = $this->ask('Please enter the add-on namespace', 'Acme\\'.Str::of($shortName)->replace('-', ' ')->camel()->ucfirst());
            $author = $this->ask('Please enter the add-on author', get_current_user());
            $authorUrl = $this->ask('Please enter the add-on author URL', '#');
            $docsUrl = $this->ask('Please enter the add-on documentation URL', '#');

            $storage = $this->makeStorage($shortName);
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

            $storage->cwd()->createDirectory($shortName);

            AddonFiles::make($generating, $shortName, $values)->write();

            $this->info("The <comment>{$name}</comment> add-on boilerplate has been generated and is located in <comment>{$storage->getAddonPath()}</comment>.");

            return Command::SUCCESS;
        }

        $this->line('');
        $this->error('Add-on name must not be empty!');
        $this->line('');

        return Command::INVALID;
    }
}