<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\RentBotPackage;

class PopulatePackageNamesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all packages ordered by amount (ascending) to assign PEX-1, PEX-2, etc.
        $packages = RentBotPackage::orderBy('amount', 'asc')->orderBy('id', 'asc')->get();
        
        $index = 1;
        foreach ($packages as $package) {
            // Only update if package_name is null or empty
            if (empty($package->package_name)) {
                $package->package_name = 'PEX-' . $index;
                $package->save();
            }
            $index++;
        }
        
        $this->command->info('Package names populated successfully!');
    }
}
