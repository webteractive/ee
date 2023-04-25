<?php

namespace Webteractive\EE;

use Illuminate\Support\Str;
use Illuminate\Support\Arr;

class AddonFiles
{
    const TYPE_MODULE = 'module';
    const TYPE_PLUGIN = 'plugin';
    const TYPE_EXTENSION = 'extension';

    protected $files = [
        'required' => [
            'addon.setup',
            'Addon',
        ],
        'migrations' => [
            'Service/Helpers/Migrations/Blueprint',
            'Service/Helpers/Migrations/CreateNewTable',
            'Service/Helpers/Migrations/ExtensionSchema',
            'Service/Helpers/Migrations/ModuleSchema',
            'Service/Helpers/Migrations/UpdateTable',
            'Service/Helpers/Migrations/WithTableFields',
        ],
        'fields' => [
            'Service/Helpers/Fields/Date',
            'Service/Helpers/Fields/BaseField',
            'Service/Helpers/Fields/Hidden',
            'Service/Helpers/Fields/Html',
            'Service/Helpers/Fields/Text',
            'Service/Helpers/Fields/Textarea',
        ],
        'helpers' => [
            'Service/Helpers/View',
            'Service/Helpers/Jsonable',
            'Service/Helpers/Url',
        ],
        'languages' => [
            'language/english/lang',
        ],
        'views' => [

        ]
    ];
    protected $type;
    protected $name;
    protected $values;
    protected $storage;

    public function __construct($type, $name, $values = [])
    {
        $this->type = $type;
        $this->values = $values;
        $this->storage = new Storage($this->name = $name);

        $this->files = array_merge(
            $this->files,
            [static::TYPE_MODULE => ['upd', 'mod', 'mcp']],
            [static::TYPE_PLUGIN => ['pi']],
            [static::TYPE_EXTENSION => ['ext']],
        );
    }

    public static function make($type, $name, $values = [])
    {
        return new static($type, $name, $values);
    }

    public static function types()
    {
        return [
            static::TYPE_MODULE,
            static::TYPE_PLUGIN,
            static::TYPE_EXTENSION,
        ];
    }

    public function files()
    {
        return collect($this->files);
    }

    public function write()
    {
        collect($this->files)
            ->filter(function($items, $key) {
                return in_array($key, [
                    static::TYPE_MODULE => ['required', 'migrations', 'fields', 'helpers', 'languages', static::TYPE_MODULE],
                    static::TYPE_PLUGIN => ['required', 'helpers', 'languages', static::TYPE_PLUGIN],
                    static::TYPE_EXTENSION => ['required', 'helpers', 'languages', static::TYPE_EXTENSION],
                ][$this->type]);
            })
            ->values()
            ->flatten()
            ->each(function($item) {
                // Handle add-on prefixed files
                $target = in_array($item, ['mod', 'ext', 'pi', 'upd', 'mcp'])
                    ? $item . '.' . $this->name
                    : $item;
                // Handle language files
                if (Str::contains($item, ['language', 'lang'])) {
                    $target = Str::of($item)->replace('/lang', "/{$this->name}_lang");
                }
                // Generate
                $this->storage->addon()->write(
                    $target . '.php',
                    $this->readAndReplace(class_basename($item) . '.stub')
                );
            });

        return $this;
    }

    private function readAndReplace($location)
    {
        $contents = $this->storage->stubs()->read($location);

        foreach ($this->values as $key => $value) {
            $contents = Str::replace($key, $value, $contents);
        }

        return $contents;
    }
}
