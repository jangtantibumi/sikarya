<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Task;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class NotificationEngine extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sikarya:notify-engine';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan low stock (<10%) and approaching task deadlines (<7 days) and send notifications';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Running Notification Engine...');

        // 1. Check Task Deadlines (< 7 days)
        $approachingTasks = Task::where('status', '!=', 'done')
                                ->whereNotNull('deadline')
                                ->where('deadline', '<=', now()->addDays(7))
                                ->where('deadline', '>', now())
                                ->get();
                                
        foreach ($approachingTasks as $task) {
            $msg = "Task '{$task->title}' mendekati deadline ({$task->deadline->format('d M Y')}). Mohon segera diselesaikan.";
            Log::info("Notification Engine (Task): {$msg} - Sent to User #{$task->user_id}");
            // In a real app, we would insert into a `notifications` table or send an email/push
        }

        // 2. Check Low Stock (< 10% of reorder level or some threshold)
        // For simplicity, we just simulate by finding products where standard_cost > 0 and stock is low.
        // Actually, we need to sum stock movements per product.
        $products = Product::all();
        foreach ($products as $product) {
            $currentStock = StockMovement::where('product_id', $product->id)->sum('quantity');
            $threshold = $product->reorder_level > 0 ? $product->reorder_level : 1000; // default 1000 threshold
            
            if ($currentStock <= ($threshold * 1.10)) { // 10% above threshold or below
                $msg = "Stok Kritis: {$product->name} tersisa " . number_format($currentStock) . " (Batas aman: " . number_format($threshold) . "). Segera lakukan pengadaan (Purchasing).";
                Log::warning("Notification Engine (Stock): {$msg}");
                // In a real app, send this to CEO & Purchasing Manager
            }
        }

        $this->info('Notification Engine completed.');
    }
}
