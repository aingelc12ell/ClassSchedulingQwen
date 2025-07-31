<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateStudentsTable extends AbstractMigration
{
    public function up()
    {
        $table = $this->table('students');
        $table->addColumn('cardid', 'string', ['limit' => 50])
            ->addColumn('name', 'string', ['limit' => 100])
            ->addColumn('curriculumId', 'string', ['limit' => 50])
            ->addColumn('enrollmentCount', 'integer', ['default' => 1])
            ->addIndex(['cardid'], ['unique' => true])
            ->create();
    }
    public function down()
    {
        $this->table('students')->drop()->save();
    }
}
