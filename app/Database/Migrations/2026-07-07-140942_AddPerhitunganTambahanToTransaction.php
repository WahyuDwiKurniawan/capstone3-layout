<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPerhitunganTambahanToTransaction extends Migration
{
    public function up()
    {
        $fields = [
            'ppn' => [
                'type'    => 'DOUBLE',
                'null'    => true,
                'after'   => 'ongkir',
            ],
            'biaya_admin' => [
                'type'    => 'DOUBLE',
                'null'    => true,
                'after'   => 'ppn',
            ],
            'kupon_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'after'      => 'biaya_admin',
            ],
            'diskon_kupon' => [
                'type'    => 'DOUBLE',
                'null'    => true,
                'after'   => 'kupon_code',
            ],
        ];

        $this->forge->addColumn('transaction', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('transaction', 'ppn');
        $this->forge->dropColumn('transaction', 'biaya_admin');
        $this->forge->dropColumn('transaction', 'kupon_code');
        $this->forge->dropColumn('transaction', 'diskon_kupon');
    }
}
