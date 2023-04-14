<?php

namespace Webteractive\EE;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;

#[AsCommand(name: 'make:service', description: 'Add new service file to an add-on')]
class MakeServiceCommand extends Command
{
    public function inputs()
    {
        return [
            new InputArgument('addon', InputArgument::REQUIRED, 'The name of the add-on where the service file will be added.'),
            new InputArgument('name', InputArgument::REQUIRED, 'The name of the service.'),
        ];
    }

    public function handle()
    {
        return Command::SUCCESS;
    }
}