<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MakeModule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:module {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create full module (Model, Migration, Policy, Observer)';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $name = Str::studly($this->argument('name'));
        $this->info('Creating module "'.$name.'"');

        // Model + Migration + Factory + Seeder + Policy
        $this->call('make:model', [
            'name' => $name,
            '--migration' => true,
            '--factory' => true,
            '--seed' => true,
            '--policy' => true,
        ]);

        // Observer
        $this->call('make:observer', [
            'name' => "{$name}Observer",
            '--model' => $name,
        ]);

        $this->info("Module {$name} created successfully.");
    }
}
