<?php

namespace App\Http\Controllers\LinkShortening;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Clients;
use App\Models\Urls;

class ShortUrlCreateController extends Controller
{
    public function __construct()
    {

    }

    public function index(Request $request)
    {
        if ($request->isMethod('POST')) {
            $headerOrgId = $request->header('Orgid');
            $headerAccessToken = $request->header('Access-Token');

        if(!empty($headerOrgId) && !empty($headerAccessToken)){
            $clientDeatils = Clients::select('is_allow_shortURL','org_id','shortURL_access_token')->where('org_id',$headerOrgId)->first();
            if(!empty($clientDeatils))
            {
                if($clientDeatils['is_allow_shortURL'] == 1 || $clientDeatils['is_allow_shortURL'] == "1"){
                    $apiAccessToken = $headerAccessToken;
                    $dbAccessToken = "Bearer ".$clientDeatils['shortURL_access_token'];
                    if($dbAccessToken == $apiAccessToken)
                    {
                        $httpHost =$request->server('HTTP_HOST'); 
                        $data = json_decode($request->getContent());
                        $url= $data->url;
                        if(!empty($data->url)){
                            $domain = !empty($data->domain) ? $data->domain : ($request->isSecure() == true ? "https" : "http") . "://$httpHost"."/";
                            $orgId = (isset($data->OrgId)) ? $data->OrgId : "";
                            $shortUrl = $this->createShortUrl($url, $domain, $orgId,$request);

                        }else{
                            return response()->json(["status" => "false", "message" => "Incomplete request body", "details" => "Required request body content is missing"], 200);
                        }
                    }else{
                        return response()->json(["status" => "false", "message" => "Invalid Access Token"], 200);
                    }
                }else{
                    return response()->json(["status" => "false", "message" => "Not Allow to create Short URL"], 200);
                }
            }else{
                return response()->json(["status" => "false", "message" => "Invalid Org Id", "details" => "Please Provide a valid Org Id"], 200);
            }
        }else{
            return response()->json(["status" => "false", "message" => "Incomplete request header", "details" => "Required request header content is missing"], 200);
        }
        } else {
            return response()->json(["status" => "false", "message" => "Request should be POST type"], 200);
        }


    }


    public function createShortUrl($urlget, $domain, $orgId,$request)
    {

        if(!empty($urlget)){
            if(is_array($urlget)){
                $url = (object)[];
                $redirectUrl = array();
                foreach($urlget[0] as $key => $values){
                    $httpHost =$request->server('HTTP_HOST');
                    $domain = !empty($data->domain) ? $data->domain : ($request->isSecure() == true ? "https" : "http") . "://$httpHost"."/";
                    if (preg_match('/^http[s]?\:\/\/[\w]+/', urldecode($values->url))) {
                        $result = $this->find($values->url, $orgId, $values->trackingId);
                        if(empty($result)){
                            $id = $this->saveUrl($values->url, $values->trackingId, $orgId);
                            $url_f = $domain.$id;
                        } else {
                            $url_f = $domain.$result['rand_id'];
                        }
                        $url->$key =$url_f;
                        $redirect_url[] =$values;
                    } else {
                        echo json_encode(["status" => "false", "message" => "Incomplete Input URL", "details" => "Required Proper Input URL"], 200);
                        exit();

                    }
                }
                echo json_encode(["status" => "true", "short_url" => array($url),"message"=> "success"], 200);
                exit();
            
            }else{

                if (preg_match('/^http[s]?\:\/\/[\w]+/', urldecode($urlget))) {

                    $trackingId = "";
                    $result = $this->find($urlget, $orgId, $trackingId);

                    if(empty($result)){
                        $orgid ="";
                        $id = $this->saveUrl($urlget, $trackingId, $orgid);
                        $url = $domain.$id;
                       
                    } else {
                        $url = $domain.$result['rand_id'];

                    } 
                    echo json_encode(["status" => "true", "short_url" => $url,"redirect_url"=> $urlget,"message"=> "success"], 200);
                    exit();
                    return response()->json(["status" => "true", "short_url" => $url,"redirect_url"=> $urlget,"message"=> "success"], 200);
                } else {
                    return response()->json(["status" => "false", "message" => "Incomplete Input URL", "details" => "Required Proper Input URL"], 200);  
                }

            }

        }else{
            return response()->json(["status" => "false", "message" => "Required Input URL", "details" => "Required Input URL"], 200);
        }

    }

    public function find($url, $orgId, $trackingId) {
        $urlDetails = Urls::select('rand_id')->where('url',$url)->where('OrgId',$orgId)->where('trackingId',$trackingId)->first();
        return $urlDetails;
    }

    public function saveUrl($url, $trackingId, $orgId){
        $length = 6;
        $str = '1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZabcefghijklmnopqrstuvwxyz';
        $rand_char = substr(str_shuffle($str), 0, $length);
        $v_check = $this->idCheck($rand_char);
        if(empty($v_check)){
            $datetime = date('Y-m-d H:i:s');
            $statement =  Urls::create([
                'url' => $url,
                'created' => $datetime,
                'rand_id' => $rand_char, 
                'trackingId' => $trackingId,
                'OrgId' => $orgId,
            ]);
            // $statement = $this->connection->prepare('INSERT INTO urls (url, created, rand_id, trackingId, OrgId) VALUES (?,?,?,?,?)');
            // $statement->execute(array($url, $datetime, $rand_char, $trackingId, $orgId));
            return $rand_char;
        }else{
            $rand_char = substr(str_shuffle(date('d-M-yh_i_s')), 0, 6);
            $v_check_second = $this->idCheck($rand_char);
            if(empty($v_check_second)){
                $datetime = date('Y-m-d H:i:s');
                $statement =  Urls::create([
                    'url' => $url,
                    'created' => $datetime,
                    'rand_id' => $rand_char, 
                    'trackingId' => $trackingId,
                    'OrgId' => $orgId,
                ]);
                // $statement = $this->connection->prepare('INSERT INTO urls (url, created, rand_id, trackingId, OrgId) VALUES (?,?,?,?,?)');
                // $statement->execute(array($url, $datetime, $rand_char, $trackingId, $orgId));
                return $rand_char;  
            }else{
                $this->saveUrl($url);
            }
        }
    }

    public function idCheck($id){

        $idCheckDetails= Urls::select('rand_id')->where('rand_id',$id)->first();
        return $idCheckDetails;
    }

    //
}
