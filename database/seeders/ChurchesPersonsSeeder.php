<?php

namespace Database\Seeders;

use App\Models\Church;
use App\Models\Person;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ChurchesPersonsSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $csvFile = database_path('seeders/churches-persons.csv');

        if (!file_exists($csvFile)) {
            $this->command->error('CSV file not found: ' . $csvFile);
            return;
        }

        $handle = fopen($csvFile, 'r');

        // Skip header row
        fgetcsv($handle, 1000, ',');

        $churchesCreated = 0;
        $personsCreated = 0;

        while (($data = fgetcsv($handle, 1000, ',')) !== false) {
            // Extract church data
            $churchName = trim($data[0]);
            $stateId = (int) trim($data[1]);
            $cityId = (int) trim($data[2]);

            // Extract person data
            $function = trim($data[3]);
            $personName = trim($data[4]);
            $phone = trim($data[5]);
            $birthDate = trim($data[6]);

            // Find or create church
            $church = Church::where('name', $churchName)->first();

            if (!$church) {
                $church = Church::create([
                    'name' => $churchName,
                    'state_id' => $stateId,
                    'city_id' => $cityId,
                ]);
                $churchesCreated++;
            }

            // Create person
            Person::create([
                'church_id' => $church->id,
                'name' => $personName,
                'birth_date' => $birthDate ?: null,
                'phone' => $phone ?: null,
                'function' => $function,
            ]);

            $personsCreated++;
        }

        fclose($handle);

        $this->command->info("Seeded {$churchesCreated} churches and {$personsCreated} persons successfully.");
    }
}
