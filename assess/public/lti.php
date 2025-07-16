<?php
require_once __DIR__ . '/../bootstrap.php';

$params = $_POST;
$launch_url = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

if (!isset($params['oauth_consumer_key']) || $params['oauth_consumer_key'] !== LTI_CONSUMER_KEY) {
    http_response_code(401);
    echo "Invalid consumer key";
    exit;
}

if (!validate_oauth($params, LTI_SHARED_SECRET, $launch_url)) {
    http_response_code(403);
    echo "Invalid OAuth signature";
    exit;
}

// Test sending grade back.
$consumer_key = LTI_CONSUMER_KEY;
$shared_secret = LTI_SHARED_SECRET;
$service_url = $params['lis_outcome_service_url'];
$sourcedid = $params['lis_result_sourcedid'];;

$service_url = str_replace('https://moodle.poc.localhost', 'https://poc-proxy', $service_url);

$grade = 0.55;

// Build the XML
$xml = '<?xml version = "1.0" encoding = "UTF-8"?>
<imsx_POXEnvelopeRequest xmlns = "http://www.imsglobal.org/services/ltiv1p1/xsd/imsoms_v1p0">
  <imsx_POXHeader>
    <imsx_POXRequestHeaderInfo>
      <imsx_version>V1.0</imsx_version>
      <imsx_messageIdentifier>' . uniqid() . '</imsx_messageIdentifier>
    </imsx_POXRequestHeaderInfo>
  </imsx_POXHeader>
  <imsx_POXBody>
    <replaceResultRequest>
      <resultRecord>
        <sourcedGUID>
          <sourcedId>' . htmlspecialchars($sourcedid) . '</sourcedId>
        </sourcedGUID>
        <result>
          <resultScore>
            <language>en</language>
            <textString>' . $grade . '</textString>
          </resultScore>
        </result>
      </resultRecord>
    </replaceResultRequest>
  </imsx_POXBody>
</imsx_POXEnvelopeRequest>';

// Prepare the request
$params = [
    'oauth_consumer_key' => $consumer_key,
    'oauth_nonce' => uniqid(),
    'oauth_signature_method' => 'HMAC-SHA1',
    'oauth_timestamp' => time(),
    'oauth_version' => '1.0',
    'oauth_body_hash' => base64_encode(sha1($xml, true)),
];

// Sign the request
$base_string = build_base_string($service_url, 'POST', $params);
$signature = base64_encode(hash_hmac('sha1', $base_string, rawurlencode($shared_secret) . '&', true));
$params['oauth_signature'] = $signature;

// Create Authorization header
$auth_header = 'OAuth ' . http_build_query($params, '', ', ');

// Send the request
$ch = curl_init($service_url);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: ' . $auth_header,
    'Content-Type: application/xml',
    'Content-Length: ' . strlen($xml),
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Debug response
echo "<pre>HTTP $http_code\n" . htmlspecialchars($response) . "</pre>";
