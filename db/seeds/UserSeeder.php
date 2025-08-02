<?php
use Phinx\Seed\AbstractSeed;

class UserSeeder extends AbstractSeed
{
    public function run(): void
    {
        $data = [
            [
                'username' => 'admin',
                'email' => 'admin@example.com',
                'password' => password_hash('qwerasdf', PASSWORD_DEFAULT),
                'role' => 'admin',
                #'created_at' => date('Y-m-d H:i:s'),
                #'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->table('users')->insert($data)->save();
    }
}