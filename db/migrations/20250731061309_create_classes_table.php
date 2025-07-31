<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateClassesTable extends AbstractMigration
{

    public function up()
    {
        $table = $this->table('classes');
        $table->addColumn('class_id', 'integer')
            ->addColumn('subject_id', 'integer')
            ->addColumn('teacher_id', 'integer')
            ->addColumn('room_id', 'integer')
            ->addColumn('time_slot_id', 'integer')
            ->addColumn('day', 'string', ['limit' => 3])
            ->addColumn('term', 'string', ['limit' => 50])
            ->addColumn('is_override', 'boolean', ['default' => false])
            ->addIndex(['subject_id'])
            ->addIndex(['teacher_id'])
            ->addIndex(['room_id'])
            ->addIndex(['time_slot_id'])
            ->addIndex(['term'])
            # ->addForeignKey('time_slot_id', 'time_slots', 'id', ['delete'=> 'CASCADE'])
            ->create();
    }

    public function down()
    {
        $this->table('classes')->drop()->save();
    }
}
