<?php


set_time_limit(0);   // unlimited for CLI
ini_set('max_execution_time', 0);

function senddatato($loc, $loc_prev)
{

	$plate_aray = [
			'038'	=> '06AACCJ0529L1ZM',
			'086'	=> '06AABAT8159P1ZR',
			'002'	=> '06BDQPS9170K2ZN',
			'037'	=> '06AFRPH4350Q1ZV',
            '039'	=> '06RTKM04066G1D4',
            
            '061'	=> '06ABKCS2915E1ZG',
            '075'	=> '06AAALM1526D1DT',
            '034'	=> '06RTKM04298A1D6',
            '044'	=> '06AANCP6248F1Z6',
            '045'	=> '06AANCP6248F1Z6',
            '085'	=> '06AAAGM0113K1D0',
			
			'068'	=> '06ATTPR9745D2ZZ',
			'053'	=> '06ATTPR9745D2ZZ',
			'092'	=> '06AAPAT2688H1ZW',
			'093'	=> '06ATTPR9745D2ZZ',
			'060'	=> '06ATTPR9745D2ZZ',
			'060'	=> '06AAOAT2574M1ZT',
			'079'	=> '06ATTPR9745D2ZZ',
			'067'	=> '06ATTPR9745D2ZZ',
			'048'	=> '06AACCJ0529L1ZM',
			'010'	=> '06AAEAT2331A1Z5',
			'011'	=> '06ATTPR9745D2ZZ',
			'013'	=> '06AAYFN2503R1ZK',
			'014'	=> '06ATTPR9745D2ZZ',
			'014_0'	=> '06AAALM0321N1DH',
			'016'	=> '06RTKM03723G1DA',
			'016_1'	=> '06BDQPS9170K2ZN',
			'018'	=> '06AAXFG0994Q1ZB',
			'050'	=> '06AACCJ0529L1ZM',
			'042'	=> '06ATTPR9745D2ZZ',
			'042_0'	=> '06AAECI2759K1ZB',
			'046'	=> '06AANCP6248F1Z6',
			'069'	=> '06ABKCS2915E1ZG',
			'043'	=> '06AANCP6248F1Z6',
			'084'	=> '06AAOAT9639H1ZS',
			'061'	=> '06ABKCS2915E1ZG',
			'047'	=> '06AANCP6248F1Z6',
			
			'008'	=> '03AABAT7501K1ZN',
			'005'	=> '06ABKCS2915E1ZG',
			'052'	=> '06ATTPR9745D2ZZ',
			'004'	=> '06ABKCS2915E1ZG',
			'035'	=> '06ABSFS6303D2Z5',
			'020'	=> '06BDQPS9170K2ZN',
			'095'	=> '07AAPFA8506N1ZX',
			'095_0'	=> '06AABAT4462B2ZT',
			'095_1'	=> '06AABAT7996E1Z3',
			'003'	=> '06AACCW5750A2ZM',
	];

	//$loc_prev['name'],$loc['imei'],$loc['lat'],$loc['lng'],$loc_prev['odometer'],$loc['dt_tracker'],$loc['speed'],$loc['angle']

	$mc_code = "";
	$gstin = "";
	if ($loc_prev['plate_number'] && isset($plate_aray[$loc_prev['plate_number']])) {
		$mc_code = $loc_prev['plate_number'];
		$mc_code = explode('_', $mc_code)[0];
		$gstin = $plate_aray[$loc_prev['plate_number']];
	} else {
		return;
	}

	$dt_tracker = '';
	$dt_tracker = DateTime::createFromFormat('Y-m-d H:i:s', $loc['dt_tracker'],  new DateTimeZone('UTC'));
	$dt_tracker = clone $dt_tracker;
	$dt_tracker->setTimeZone(new DateTimeZone('Asia/Kolkata'));
	$dt_tracker = $dt_tracker->format('Y-m-d H:i:s');

	if($loc['speed'] > 0) {
		$status = 'Moving';
	} else if($loc['speed'] == 0 && $loc['params']['acc'] == '0') 
	{
		$status = 'Halt';
	} else if($loc['speed'] == 0 && $loc['params']['acc'] == '1') 
	{
		$status = 'Halt';
	}

	$ignition = 'off';
	if($loc['params']['acc'] == '1'){
		$ignition = 'on';
	}

	$data = array(
      "MC_Code" => $mc_code,
      "AgencyGSTIN" => $gstin,
      "VehicleRegistrationNo" =>  $loc_prev['name'],
      "GPSDeviceID" =>$loc['imei'],
      "IMEI_No" => $loc['imei'],
      "DateTimeOfPosition" =>  $dt_tracker,
      "Lat" => (float) $loc['lat'],
      "Long" => (float) $loc['lng'],
      "Status" => $status,
      "IgnitionStatus" =>  $ignition,
      "SpeedKmPerHour" => $loc['speed'],
      "DirectionDegree" =>  $loc['angle'],
      "WorkType" =>$loc_prev['model'],
  );



// data start 
  $response = gpsTrackPost(json_encode($data));
  $data['response'] = json_decode($response);
  
//data stop


  
  //logTheData($loc);	// to stop log data
  logTheData($data);

}

function logTheData($data){
	// Log file path in the current folder
//	$logFile = __DIR__ . '/log.txt';
	
	
	//chatgpt code	
    $logFile = __DIR__ . '/../mclogall/' . date('d-m-y') . '-log.txt';
    
    $logFile2 = __DIR__ . '/../mclog/';
	
	
	// Convert array to string
	$logEntry = print_r($data, true);
	// Prepare log message
	$logMessage = "[" . date('Y-m-d H:i:s') . "] " . $logEntry . PHP_EOL;
	// Write to log file
	file_put_contents($logFile, $logMessage, FILE_APPEND);
	
	if($data['MC_Code']){
	    file_put_contents($logFile2 . $data['MC_Code'] .'.'.date('d-m-y') .'.txt', $logMessage, FILE_APPEND);
	}
	
}



// sabuj
/*
function gpsTrackPost($data){
	$curl = curl_init();
	
    $username = 'SANSKAR_PKL';
    $password = 'SANS#PKL522GTPS';

    // Base64 encode the username and password for Authorization header
    $authorization = 'Basic ' . base64_encode($username . ':' . $password);

	curl_setopt_array($curl, array(

	CURLOPT_URL => 'https://swm.ulbharyana.gov.in/api/VendorApi/Add_VehicleLocation',
    	CURLOPT_RETURNTRANSFER => true,
    	//CURLOPT_RETURNTRANSFER => false,
    	CURLOPT_ENCODING => '',
    	CURLOPT_MAXREDIRS => 10,
    	CURLOPT_CONNECTTIMEOUT => 10,
    	CURLOPT_TIMEOUT => 10,
    	CURLOPT_FOLLOWLOCATION => true,
    	CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    	CURLOPT_CUSTOMREQUEST => 'POST',
    	CURLOPT_POSTFIELDS => $data,
    	CURLOPT_HTTPHEADER => array(
    		'Authorization: ' . $authorization,
    		'Content-Type: application/json'
    	),
    	CURLOPT_SSL_VERIFYHOST => 0,
    	CURLOPT_SSL_VERIFYPEER => false,
    ));

	$response = curl_exec($curl);

	
  curl_close($curl);

  return $response;
}

*/

//sabuj

// forward api start

function gpsTrackPost($data){
	$curl = curl_init();

    $forwardApiUrl = 'http://kuldip_queue.test/api/forward';
    $forwardApiKey = 'YOUR_API_KEY_HERE';

    $username = 'SANSKAR_PKL';
    $password = 'SANS#PKL522GTPS';

    $body = json_encode([
        'forward_url' => 'https://swm.ulbharyana.gov.in/api/VendorApi/Add_VehicleLocation',
        'header' => [
            'Authorization' => 'Basic ' . base64_encode($username . ':' . $password),
            'Content-Type'  => 'application/json',
        ],
        'payload' => json_decode($data, true),
    ]);

	curl_setopt_array($curl, array(
		CURLOPT_URL => $forwardApiUrl,
    	CURLOPT_RETURNTRANSFER => true,
    	CURLOPT_CONNECTTIMEOUT_MS => 2000,
    	CURLOPT_TIMEOUT_MS => 5000,
    	CURLOPT_NOSIGNAL => 1,
    	CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    	CURLOPT_POST => true,
    	CURLOPT_POSTFIELDS => $body,
    	CURLOPT_HTTPHEADER => array(
    		'X-API-KEY: ' . $forwardApiKey,
    		'Content-Type: application/json',
    	),
    	CURLOPT_SSL_VERIFYHOST => 0,
    	CURLOPT_SSL_VERIFYPEER => false,
    ));

	$response = curl_exec($curl);

	if($response === false){
		$response = 'CURL_ERROR: ' . curl_error($curl);
	}

	curl_close($curl);

	return $response;
}




// forward api ends 
