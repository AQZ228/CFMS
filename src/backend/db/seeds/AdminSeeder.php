<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use Phinx\Seed\AbstractSeed;

class AdminSeeder extends AbstractSeed
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
        $adminName = getenv('ADMIN_NAME');
        $adminPass = getenv('ADMIN_PASS');

        if (!$adminName || !$adminPass) {
            printf("Admin username or password is not set in environment variables.\nskipping...\n");
            die();
        }

        $this->execute("DELETE FROM staff WHERE id = 1");

        $password = password_hash($adminPass, PASSWORD_DEFAULT);
        $data = [
            [
                'id' => 1,
                'name' => $adminName,
                'role' => 'admin',
                'password' => $password
            ]
        ];

        $staff = $this->table('staff');
        $staff->insert($data)->saveData();
    }
}
