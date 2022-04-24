<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Mail\WelcomeMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;


class WaitListController extends Controller
{
    //
    public function WaitList(Request $req)
    {
		//validate the email field
        $fields = $req->validate([
            'email' => 'required|string|unique:wait_lists,email'
            // 'university' => 'required|string'
        ]);
        $email = $fields['email'];

		//cURL email validator from rapidapi
		$curl = curl_init();
		curl_setopt_array($curl, [
			CURLOPT_URL => "https://email-validator8.p.rapidapi.com/api/v2.0/email",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS => "email=$email",
			CURLOPT_HTTPHEADER => [
				"X-RapidAPI-Host: email-validator8.p.rapidapi.com",
				"X-RapidAPI-Key: 5b3267133bmsh22dbc06669bede9p1005d2jsnccc3cbc5e302",
				"content-type: application/x-www-form-urlencoded"
			],
		]);

		$response = curl_exec($curl);
		$err = curl_error($curl);
		$js = json_decode($response);
		curl_close($curl);
		$res = $js->mx_records;
		if($res == 1){
			//create user in the database
			$user = User::create([
				'email' => $fields['email']
			]);
			//mail the email
			Mail::to($email)->send(new WelcomeMail());
			//redirect on mail success to the success page
			return view('/success');
		}else{
			//redirect on mail failure to the failure page
			return view('/failure');
		}
	// if ($err) {
	// 	echo "cURL Error #:" . $err;
	// } else {
	// 	return $js->mx_records;
	// }
    }
}
