<?php

namespace App\Http\Controllers\LinkShortening;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Clients;
use App\Models\Urls;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Oauth\Oauth1 as GuzzleOauth;
use GuzzleHttp\Psr7\Message;
use Mail;
use App\Mail\LinkShorteningEmail;

class ShortUrlConvertController extends Controller
{
    public function __construct()
    {

    }

    public function index(Request $request)
    {

        $requestUri = $request->fullUrl();
        $query_parameters = explode("?", $requestUri);
        $param = !empty($query_parameters[1]) ? $query_parameters[1] : "";  
        $rand = rand(10,100);
        $q = '';
        $uri_path = parse_url($requestUri, PHP_URL_PATH);
        $uri_segments = explode('/', $uri_path);
        $length = strlen($uri_segments[1]);
        $q = ($length < 6 ) ? substr($uri_segments[1],0,6) : $uri_segments[1];
        
        if (empty($q)) {
          $this->not_found();
          return;
          } else {
          $q = urldecode($q);
          $data = $this->geturlid($q);
         

          if (!empty($data)) {
              $result = $this->fetch($data['id']);
              if (!empty($result)) {
                  $url = !empty($param) ? $result['url'].'?'.$param : $result['url'];
                  if(!preg_match('/bot|crawl|slurp|curl|wget|preview|Preview|WhatsApp|skype-url-preview|snippet|spider|mediapartners/i', $_SERVER['HTTP_USER_AGENT'])){
                      $this->update($data['id']);
                      if(!empty($data['trackingId'])){
                          $this->sfauthentication($result['OrgId'],$data['trackingId']);
                      }
                  }
                  return Redirect::away($url);
                } else {
                  $this->not_found();
              }
          } else {
              $this->not_found();
          }
      }
    }

    public function sfauthentication($orgId,$trackingid)
    {
        $orgdetails = $this->getorgdetails($orgId);
        if(!empty($orgdetails)) {
            $oauthtoken = (isset($orgdetails['oauthrefreshtoken'])) ? $orgdetails['oauthrefreshtoken'] : '';
            $org_type = (isset($orgdetails['org_type'])) ? $orgdetails['org_type'] : '';
            $client_id = (isset($orgdetails['client_id'])) ? $orgdetails['client_id'] : '';
            $client_secret = (isset($orgdetails['client_secret'])) ? $orgdetails['client_secret'] : '';
            $second_append_url  = (!empty($orgdetails['name_space_sf'])) ? $orgdetails['name_space_sf'] : 'tdc_tsw';
            $secondAppendUrl = str_replace("/","",$second_append_url);
            
            if($org_type =="Sandbox") {
               $service_url = 'https://test.salesforce.com/services/oauth2/token?grant_type=refresh_token&client_id='.$client_id.'&client_secret='.$client_secret.'&refresh_token='.$oauthtoken;
            } else {
                $service_url = 'https://login.salesforce.com/services/oauth2/token?grant_type=refresh_token&client_id='.$client_id.'&client_secret='.$client_secret.'&refresh_token='.$oauthtoken;
            }
            $curl_post_data = '';
            $rest_client = new Client([
                'timeout' => 30,
            ]);
            $guzzle_options['headers']['Content-Type'] = 'application/json';
            $guzzle_options['body'] = $curl_post_data;
            $response = $rest_client->request('POST', $service_url, $guzzle_options);
            $status_code = $response->getStatusCode();


            if($status_code === False) {
                $data =array('subject'=>"Link Tracking Error1: " . $orgId,
                'reason'=>"Link tracking failed  due to the below error:",
                'reasonstmt'=>"Link Tracking Error: Authentication Error url ".$service_url.""
            );
            $this->send_email($data);
                
            } else {
                $response = json_decode($response->getBody()->getContents());
                $instance_url = (isset($response->instance_url)) ? $response->instance_url : '';
                $access_token = (isset($response->access_token)) ? $response->access_token : '';
                $error = (isset($response->error)) ? $response->error : '';
                $error_description = (isset($response->error_description)) ? $response->error_description : '';
                if ($instance_url != ''){
                    $secondurl= $instance_url ."/services/apexrest/".$secondAppendUrl."/LinkTracking";
                    $post_data =array("timeStampId"=>$trackingid);

                    $rest_client = new Client([
                        'timeout' => 30,
                    ]);

            $guzzle_options['headers']['Content-Type'] = 'application/json';
            $guzzle_options['headers']['Authorization'] = 'Bearer '.$access_token;
            $guzzle_options['body'] = json_encode($post_data);
            $response = $rest_client->request('PATCH', $secondurl, $guzzle_options);

         
                    if($response===False) {
                        $data =array('subject'=>"Link Tracking Error2: " . $orgId,
                        'reason'=>"Link tracking failed  due to the below error:",
                        'reasonstmt'=>"Link Tracking Error: Tracking API Error"
                    );
                    $this->send_email($data);

                  
                    }
                } else{
                    $data =array('subject'=>"Link Tracking Error3: " . $orgId,
                    'reason'=>"Link tracking failed  due to the below error:",
                    'reasonstmt'=>"Link Tracking Error: Authentication Error ".$service_url.""
                );
                $this->send_email($data);

                }
            }
        } else {
                $data =array('subject'=>"Link Tracking Error4: This OrgId " . $orgId. " Not exist",
                'reason'=>"Link Tracking Error: This OrgId  " . $orgId . " Not exist",
                'reasonstmt'=>" "
                    );
                $this->send_email($data);

        }
    }

    public function not_found() {
        abort(404, '404 Not Found.');
    }

    public function geturlid($rand_id){
        $statement = Urls::select('*')->where('rand_id',$rand_id)->first(); 
        return $statement ;
    }

    public function fetch($id) {
        $statement = Urls::select('*')->where('id',$id)->first(); 
        return $statement ;
    }

    public function update($id) {
        $datetime = date('Y-m-d H:i:s');
        Urls::where('id', $id)
        ->update([
            'hits' => Urls::raw('hits + 1'),
            'accessed' => $datetime,
        ]);
    }

    public function getorgdetails($orgId) {
        $statement = Clients::select('*')->where('org_id',$orgId)->first();
        return $statement;
    }

    public function send_email($data) {

        Mail::to('kanchan.aggarwal@360degreecloud.in')
        ->send(new LinkShorteningEmail($data));
       
    }



    //
}
