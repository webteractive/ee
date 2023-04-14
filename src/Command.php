<?php

namespace Webteractive\EE;

use Illuminate\Support\Str;
use League\Flysystem\Filesystem;
use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use League\Flysystem\Local\LocalFilesystemAdapter;

abstract class Command extends BaseCommand
{
    protected InputInterface $input;
    protected OutputInterface $output;

    /**
     * @return array 
     */
    abstract protected function inputs();
    
    /**
     * @return int 
     */
    abstract protected function handle();

    protected function configure()
    {
        if (count($this->inputs()) > 0) {
            $this->setDefinition(
                new InputDefinition($this->inputs())
            );
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
        return $this->handle();
    }

    public function ask(string $question, $default = null)
    {
        $final = Str::of("<info>{$question}</info>")
            ->when($default, function($str, $default) {
                return $str->append(": [<comment>{$default}</comment>] ");
            }, fn ($str) => $str->append(': '));
        return optional($this->getHelper('question'))->ask(
            $this->input,
            $this->output,
            new Question($final, $default)
        );
    }

    public function line($string, $options = 0)
    {
        $this->output->writeln($string, $options);
        return $this;
    }

    public function info($string, $options = 0)
    {
        return $this->line("<info>{$string}</info>", $options);
    }

    public function cwd()
    {
        return new Filesystem(
            new LocalFilesystemAdapter(getcwd())
        );
    }

    public function addon($name)
    {
        return new Filesystem(
            new LocalFilesystemAdapter(getcwd() . '/' . $name)
        );
    }

    public function resources()
    {
        return new Filesystem(
            new LocalFilesystemAdapter(__DIR__ . '/../')
        );
    }
}
