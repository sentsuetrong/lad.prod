<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use CodeIgniter\Database\RawSql;

class CreateFilesTables extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'int',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true
            ],
            'name' => [
                'type' => 'text'
            ],
            'upload_identifier' => [
                'type' => 'varchar',
                'constraint' => 255
            ],
            'original_filename' => [
                'type' => 'varchar',
                'constraint' => 255
            ],
            'total_file_size' => [
                'type' => 'bigint',
                'constraint' => 20,
                'unsigned' => true
            ],
            'total_chunks' => [
                'type' => 'int',
                'constraint' => 11,
                'unsigned' => true
            ],
            'original_mime_type' => [
                'type' => 'varchar',
                'constraint' => 100
            ],
            'is_publish' => [
                'type' => 'tinyint',
                'constraint' => 1,
                'unsigned' => true,
                'default' => 1,
            ],
            'download_count' => [
                'type' => 'int',
                'constraint' => 11,
                'unsigned' => true,
                'default' => 0,
            ],
            'created_at' => [
                'type' => 'datetime',
                'default' => new RawSql('CURRENT_TIMESTAMP')
            ],
            'updated_at' => [
                'type' => 'datetime',
                'default' => new RawSql('CURRENT_TIMESTAMP')
            ],
            'deleted_at' => [
                'type' => 'datetime',
                'null' => true
            ],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('upload_identifier');
        $this->forge->createTable('files');

        $this->forge->addField([
            'file_id' => [
                'type' => 'int',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'chunk_number' => [
                'type' => 'int',
                'constraint' => 11,
                'unsigned' => true
            ],
            'chunk_data' => [
                'type' => 'longblob'
            ],
        ]);
        $this->forge->addForeignKey('file_id', 'files', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('files_chunks');

        $this->forge->addField([
            'file_id' => [
                'type' => 'int',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'user_id' => [
                'type' => 'int',
                'constraint' => 11,
                'unsigned' => true,
                'default' => null,
                'null' => true,
            ],
            'user_agent' => [
                'type' => 'text'
            ],
            'ip_address' => [
                'type' => 'varchar',
                'constraint' => 50
            ],
            'created_at' => [
                'type' => 'datetime',
                'default' => new RawSql('CURRENT_TIMESTAMP')
            ],
        ]);
        $this->forge->addForeignKey('file_id', 'files', 'id');
        $this->forge->addForeignKey('user_id', 'users', 'id');
        $this->forge->createTable('files_download_logs');
    }

    public function down()
    {
        $this->forge->dropTable('files', true);
        $this->forge->dropTable('files_chunks', true);
        $this->forge->dropTable('files_download_logs', true);
    }
}
