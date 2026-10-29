<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use Phinx\Seed\AbstractSeed;

class TestStaffSeeder extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * https://book.cakephp.org/phinx/0/en/seeding.html
     */
    public function run(): void
    {
        if (!Lib\Develop::isDevelopment()) {
            printf("This seed is only for development purposes.\nskipping...\n");
            return;
        }

        $password = password_hash('abcd123', PASSWORD_DEFAULT);
        $data = [
            [
                'id' => 2,
                'name' => 'admin',
                'role' => 'admin',
                'password' => $password
            ],
            [
                'id' => 3,
                'name' => 'reception',
                'role' => 'reception',
                'password' => $password
            ],
            [
                'id' => 4,
                'name' => 'store',
                'role' => 'store',
                'password' => $password
            ],
            [
                'id' => 5,
                'name' => 'prize',
                'role' => 'prize',
                'password' => $password
            ]
        ];
        $this->execute("DELETE FROM staff WHERE id IN (2, 3, 4, 5)");

        $staff = $this->table('staff');
        $staff->insert($data)->saveData();

        printf("Test staff users have been seeded successfully.\n");
        foreach ($data as $staffUser) {
            printf("%d Username: %s, Password: abcd123\n", $staffUser['id'], $staffUser['name']);
        }
    }
}
