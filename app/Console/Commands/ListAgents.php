<?php

namespace App\Console\Commands;

use App\Models\Agent;
use App\Models\AgentModel;
use App\Models\User;
use Illuminate\Console\Command;

class ListAgents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'agents:list {--user= : Filter by user ID} {--status= : Filter by status}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all agents with their relationships';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $query = Agent::with(['user', 'agentModel']);

        // Apply filters
        if ($userId = $this->option('user')) {
            $query->where('user_id', $userId);
        }

        if ($status = $this->option('status')) {
            $query->where('status', $status);
        }

        $agents = $query->get();

        if ($agents->isEmpty()) {
            $this->info('No agents found.');
            return 0;
        }

        $this->info("Found {$agents->count()} agent(s):\n");

        foreach ($agents as $agent) {
            $this->line("Agent ID: {$agent->id}");
            $this->line("Role: {$agent->role_name}");
            $this->line("Sector: {$agent->sector}");
            $this->line("Status: {$agent->status}");
            $this->line("Usage Limit: " . ($agent->usage_limit ?? 'Unlimited'));
            $this->line("User: {$agent->user->name} ({$agent->user->email})");
            $this->line("Model: {$agent->agentModel->model_name}");
            $this->line("Created: {$agent->created_at->format('Y-m-d H:i:s')}");
            $this->line("Training Data Keys: " . implode(', ', array_keys($agent->training_data ?? [])));
            $this->line('---');
        }

        // Show summary
        $this->newLine();
        $this->info('Summary:');
        $this->line("Total Agents: " . Agent::count());
        $this->line("Active Agents: " . Agent::where('status', 'active')->count());
        $this->line("Inactive Agents: " . Agent::where('status', 'inactive')->count());
        $this->line("Total Models: " . AgentModel::count());
        $this->line("Default Model: " . (AgentModel::where('default', true)->first()?->model_name ?? 'None'));

        return 0;
    }
}
