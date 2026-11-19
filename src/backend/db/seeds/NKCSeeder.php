<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class NKCSeeder extends AbstractSeed
{
    private function generatePassword()
    {
        $characters =
            'abcdefghijklmnopqrstuvwxyz' .
            'ABCDEFGHIJKLMNOPQRSTUVWXYZ' .
            '0123456789';
        $passcode = '';

        for ($i = 0; $i < 10; $i++) {
            $passcode .= $characters[random_int(
                0,
                strlen($characters) - 1
            )];
        }

        return $passcode;
    }
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
        // 店舗とスタッフをすべて初期化
        $this->execute('DELETE FROM scores');
        $this->execute('DELETE FROM staff_stores');
        $this->execute('DELETE FROM staff WHERE id >= 7');
        $this->execute('DELETE FROM stores');

        // 店を追加
        $stores = [['id' => 1, 'name' => 'UNO'], ['id' => 2, 'name' => 'ダーツ'], ['id' => 3, 'name' => 'ブラックジャック'], ['id' => 4, 'name' => 'ポーカー'], ['id' => 5, 'name' => 'ルーレット']];
        $stores_table = $this->table('stores');
        foreach ($stores as $store) {
            $stores_table->insert(['id' => $store['id'], 'name' => $store['name']])->saveData();
        }

        // スタッフを追加
        $idx = 7;
        $staff = [
            [
                'id' => $idx++,
                'name' => '永島愛貴',
                'role' => 'admin'
            ],
            [
                'id' => $idx++,
                'name' => '塩川海響',
                'role' => 'store'
            ],
            [
                'id' => $idx++,
                'name' => '乙成隼士',
                'role' => 'admin'
            ],
            [
                'id' => $idx++,
                'name' => '加藤滉彗',
                'role' => 'store'
            ],
            [
                'id' => $idx++,
                'name' => '夏目暁土',
                'role' => 'store'
            ],
            [
                'id' => $idx++,
                'name' => '亀本健慎',
                'role' => 'store'
            ],
            [
                'id' => $idx++,
                'name' => '吉水優斗',
                'role' => 'store'
            ],
            [
                'id' => $idx++,
                'name' => '戸塚菜々学',
                'role' => 'store'
            ],
            [
                'id' => $idx++,
                'name' => '今井友陽',
                'role' => 'store'
            ],
            [
                'id' => $idx++,
                'name' => '佐藤輝三',
                'role' => 'store'
            ],
            [
                'id' => $idx++,
                'name' => '松本浩成',
                'role' => 'store'
            ],
            [
                'id' => $idx++,
                'name' => '清水孝也',
                'role' => 'store'
            ],
            [
                'id' => $idx++,
                'name' => '竹市七星',
                'role' => 'store'
            ],
            [
                'id' => $idx++,
                'name' => '竹谷弘杜',
                'role' => 'admin'
            ],
            [
                'id' => $idx++,
                'name' => '中島悠翔',
                'role' => 'store'
            ],
            [
                'id' => $idx++,
                'name' => '田中龍之介',
                'role' => 'store'
            ],
            [
                'id' => $idx++,
                'name' => '島田知明',
                'role' => 'admin'
            ],
            [
                'id' => $idx++,
                'name' => '布目純大',
                'role' => 'admin'
            ],
            [
                'id' => $idx++,
                'name' => '服部優生',
                'role' => 'store'
            ],
            [
                'id' => $idx++,
                'name' => '福岡蒼空',
                'role' => 'store'
            ],
            [
                'id' => $idx++,
                'name' => '福田優真',
                'role' => 'store'
            ],
            [
                'id' => $idx++,
                'name' => '木村光治',
                'role' => 'store'
            ],
            [
                'id' => $idx++,
                'name' => '鷲尾学途',
                'role' => 'admin'
            ],
            [
                'id' => $idx++,
                'name' => '清水銀太',
                'role' => 'admin'
            ],
            [
                'id' => $idx++,
                'name' => '千村祥平',
                'role' => 'store'
            ],
            [
                'id' => $idx++,
                'name' => '大嶋星璃花',
                'role' => 'admin'
            ],
            [
                'id' => $idx++,
                'name' => '牧戸泰臥',
                'role' => 'admin'
            ],
            [
                'id' => $idx++,
                'name' => '酒井隆行',
                'role' => 'admin'
            ],
            [
                'id' => $idx++,
                'name' => '星川誠弥',
                'role' => 'admin'
            ],
            [
                'id' => $idx++,
                'name' => '鈴木僚',
                'role' => 'admin'
            ],
            [
                'id' => $idx++,
                'name' => '勝原裕樹',
                'role' => 'admin'
            ],
            [
                'id' => $idx++,
                'name' => '小島尚起',
                'role' => 'admin'
            ],
            [
                'id' => $idx++,
                'name' => '宮本晃征',
                'role' => 'admin'
            ],
            [
                'id' => $idx++,
                'name' => '間瀬恵介',
                'role' => 'admin'
            ],
            [
                'id' => $idx++,
                'name' => '本間勇希哉',
                'role' => 'admin'
            ],
            [
                'id' => $idx++,
                'name' => '廣瀬遼輝',
                'role' => 'admin'
            ],
            [
                'id' => $idx++,
                'name' => '近藤荘太',
                'role' => 'admin'
            ],
            [
                'id' => $idx++,
                'name' => '藤田晃生',
                'role' => 'admin'
            ],
            [
                'id' => $idx++,
                'name' => '吉岡優太',
                'role' => 'admin'
            ]
        ];

        // スタッフを追加
        $staff_table = $this->table('staff');
        foreach ($staff as $s) {
            $password = $this->generatePassword();
            $s['password'] = password_hash($password, PASSWORD_DEFAULT);
            printf("Staff ID: %d, Name: %s, Password: %s\n", $s['id'], $s['name'], $password);
            $staff_table->insert($s)->saveData();

            // 全てのスタッフに全ての店舗を割り当てる
            $staff_store_table = $this->table('staff_stores');
            foreach ($stores as $store) {
                $staff_store_table->insert([
                    'staff_id' => $s['id'],
                    'store_id' => $store['id']
                ])->saveData();
            }
        }
    }
}
