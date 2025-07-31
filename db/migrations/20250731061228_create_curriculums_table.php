<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateCurriculumsTable extends AbstractMigration
{

    public function up()
    {
        $table = $this->table('curriculums');
        $table->addColumn('code', 'string', ['limit' => 50])
            ->addColumn('name', 'string', ['limit' => 150])
            ->addColumn('term', 'string', ['limit' => 50])
            ->addColumn('subject_ids', 'text', ['null' => true]) // JSON
            ->addIndex(['code'], ['unique' => true])
            ->addIndex(['term'])
            ->create();
    }

    public function down()
    {
        $this->table('curriculums')->drop()->save();
    }
}
