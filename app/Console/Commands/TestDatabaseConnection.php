<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Exception;

class TestDatabaseConnection extends Command
{
    protected $signature = 'db:test';
    protected $description = 'Test database connection and show user data';

    public function handle()
    {
        try {
            // Test basic connection
            DB::connection()->getPdo();
            $this->info('✅ Database connection successful!');
            
            // Test users table
            $userCount = User::count();
            $this->info("📊 Total users: $userCount");
            
            // Show recent users
            $recentUsers = User::latest()->take(5)->get(['id', 'first_name', 'last_name', 'email', 'created_at']);
            
            if ($recentUsers->isNotEmpty()) {
                $this->info("\n📋 Recent users:");
                foreach ($recentUsers as $user) {
                    $this->line("- ID: {$user->id}, Name: {$user->first_name} {$user->last_name}, Email: {$user->email}");
                }
            } else {
                $this->warn("No users found in database");
            }
            
            // Test creating a sample user
            $this->info("\n🧪 Testing user creation...");
            $testUser = User::create([
                'first_name' => 'Test',
                'last_name' => 'User',
                'username' => 'testuser_' . time(),
                'email' => 'test_' . time() . '@example.com',
                'password' => bcrypt('password123'),
            ]);
            
            $this->info("✅ Test user created successfully! ID: {$testUser->id}");
            
            // Clean up test user
            $testUser->delete();
            $this->info("🧹 Test user cleaned up");
            
        } catch (Exception $e) {
            $this->error("❌ Database test failed: " . $e->getMessage());
            
            $this->warn("\n🔧 Troubleshooting steps:");
            $this->line("1. Check .env database configuration");
            $this->line("2. Ensure MySQL is running");
            $this->line("3. Run: php artisan config:clear");
            $this->line("4. Run: php artisan migrate");
            $this->line("5. Check database exists: mysql -u root -p -e 'SHOW DATABASES;'");
        }
    }
}