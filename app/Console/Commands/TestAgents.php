<?php

namespace App\Console\Commands;

use App\Models\Agent;
use App\Models\AgentModel;
use App\Models\User;
use Illuminate\Console\Command;

class TestAgents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:agents';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the Agent and AgentModel functionality';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Testing Agent and AgentModel functionality...');
        
        // Test AgentModel
        $this->info('1. Testing AgentModel...');
        $agentModels = AgentModel::all();
        $this->info("   Found {$agentModels->count()} agent models:");
        foreach ($agentModels as $model) {
            $this->line("   - {$model->model_name} (Default: " . ($model->default ? 'Yes' : 'No') . ")");
        }
        
        // Test Agent
        $this->info('2. Testing Agent...');
        $agents = Agent::with(['user', 'agentModel'])->get();
        $this->info("   Found {$agents->count()} agents:");
        foreach ($agents as $agent) {
            $this->line("   - {$agent->role_name} ({$agent->sector}) - Status: {$agent->status}");
            $this->line("     User: {$agent->user->name}");
            $this->line("     Model: {$agent->agentModel->model_name}");
        }
        
        // Test User relationships
        $this->info('3. Testing User relationships...');
        $users = User::with('agents')->get();
        foreach ($users as $user) {
            $this->info("   User: {$user->name} ({$user->email})");
            $this->line("   Agents: {$user->agents->count()}");
        }
        
        // Test DashboardController
        $this->info('4. Testing DashboardController...');
        try {
            $controller = new \App\Http\Controllers\DashboardController();
            $user = User::first();
            \Illuminate\Support\Facades\Auth::login($user);
            $result = $controller->showdashboard();
            $this->info('   DashboardController executed successfully');
        } catch (\Exception $e) {
            $this->error("   DashboardController error: " . $e->getMessage());
            return 1;
        }
        
        $this->info('All tests passed! ✅');
        return 0;
    }
}
