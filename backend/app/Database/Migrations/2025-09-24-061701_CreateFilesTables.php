<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

class CreateFilesTables extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'int', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'name' => ['type' => 'text'],
            'upload_identifier' => ['type' => 'varchar', 'constraint' => 255],
            'original_filename' => ['type' => 'varchar', 'constraint' => 255],
            'total_file_size' => ['type' => 'bigint', 'constraint' => 20, 'unsigned' => true],
            'total_chunks' => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'original_mime_type' => ['type' => 'varchar', 'constraint' => 100],
            'created_at' => ['type' => 'datetime', 'default' => new RawSql('CURRENT_TIMESTAMP')],
            'updated_at' => ['type' => 'datetime', 'default' => new RawSql('CURRENT_TIMESTAMP')],
            'deleted_at' => ['type' => 'datetime', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('upload_identifier');
        $this->forge->createTable('files', false);

        $this->forge->addField([
            'upload_identifier' => ['type' => 'varchar', 'constraint' => 255],
            'chunk_number' => ['type' => 'int', 'constraint' => 11, 'unsigned' => true],
            'mime_type' => ['type' => 'varchar', 'constraint' => 100],
            'chunk_data' => ['type' => 'longblob'],
            'created_at' => ['type' => 'datetime', 'default' => new RawSql('CURRENT_TIMESTAMP')],
            'updated_at' => ['type' => 'datetime', 'default' => new RawSql('CURRENT_TIMESTAMP')]
        ]);
        $this->forge->addKey('upload_identifier');
        $this->forge->createTable('files_chunks', false);
    }

    public function down()
    {
        $this->forge->dropTable('files', true);
        $this->forge->dropTable('files_chunks', true);
    }
}
