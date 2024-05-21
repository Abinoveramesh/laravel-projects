<?php

use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
		$data = [
			['key_word' => 'user_toll_number' ,'value' => 186, 'status' => 1,'type' => 1],
			['key_word' => 'rider_toll_number','value' => 187,'status' => 1,'type' => 1],
			['key_word' => 'ccavenue_payment','value' => 1,'status' => 1,'type' => 1],
			['key_word' => 'ccavenue_refund','value' => 1,'status' => 1,'type' => 1],
			['key_word' => 'b2biz_payment','value' => 1,'status' => 1,'type' => 1],

		];
		foreach($data as $d) {
			$get = DB::table('settings')->where('key_word',$d['key_word'])->first();
			if($get == null || $get == '') {
				DB::table('settings')->insert($d);
			}
		}
    }
}
