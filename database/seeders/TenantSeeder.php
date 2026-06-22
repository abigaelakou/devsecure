<?php

// database/seeders/TenantSeeder.php
public function run(): void
{
    $tenants = [
        ['id' => 'lycee-a',    'name' => 'Lycée Moderne A',    'domain' => 'lycee-a.localhost'],
        ['id' => 'college-b',  'name' => 'Collège Saint-Paul', 'domain' => 'college-b.localhost'],
    ];

    foreach ($tenants as $data) {
        $tenant = \App\Models\Landlord\Tenant::firstOrCreate(
            ['id' => $data['id']],
            ['name' => $data['name'], 'email_contact' => 'admin@'.$data['id'].'.ci']
        );
        $tenant->domains()->firstOrCreate(['domain' => $data['domain']]);
    }
}