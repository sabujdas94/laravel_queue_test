<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GenerateApiKey extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'apikey:generate {name : The name for the API key}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a new API key';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');
        
        // Generate a secure random API key
        $apiKey = 'sk_' . Str::random(48);
        
        // Insert into database
        DB::table('api_keys')->insert([
            'name' => $name,
            'key' => $apiKey,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $this->info('API Key generated successfully!');
        $this->line('');
        $this->line('Name: ' . $name);
        $this->line('API Key: ' . $apiKey);
        $this->line('');
        $this->warn('Please save this API key securely. You will not be able to see it again.');
        
        return Command::SUCCESS;
    }
}
