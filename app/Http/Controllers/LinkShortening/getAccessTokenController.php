<?php

namespace App\Http\Controllers\LinkShortening;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class getAccessTokenController extends Controller
{
    //
    public function index(Request $request)
    {
        if ($request->isMethod('POST')) {
            $post_data = json_decode($request->getContent());

           if(!empty($post_data->is_allow_shortURL) && !empty($post_data->orgId) ){
                    $is_allow_shortURL       = $post_data->is_allow_shortURL;
                    $orgId    = $post_data->orgId;
                    
                    if($is_allow_shortURL == "not allowed"){
                    $stmt = Clients::select('is_allow_shortURL')->where('org_id',$orgId)->first();
                    if(!empty($stmt)){
                              $shortURL_updated_at =date("Y-m-d h:i:s");
                              $update_allow = 0;
                              $updateData = array('is_allow_shortURL' => $update_allow,'shortURL_updated_at' => $shortURL_updated_at);
                              Clients::where('org_id', $orgId)->update($updateData);
                            //   $sql_up = "UPDATE clients SET is_allow_shortURL=?, shortURL_updated_at=? WHERE org_id=?";
                            //   $connection->prepare($sql_up)->execute([$update_allow, $shortURL_updated_at, $orgId]);
                              return response()->json(["status" => "true", "message" => "is_allow_shortURL updated Successfully"], 200);
                                 
                          }else{
                            return response()->json(["status" => "false", "message" => "Invalid Org Id", "details" => "Please Provide a valid Org Id"], 200);

                            //   http_response_code(200);
                            //   echo json_encode(["status" => "false", "message" => "Invalid Org Id", "details" => "Please Provide a valid Org Id"]); 
                          }
                        
                    }else if($is_allow_shortURL == "allowed"){
                        $row = Clients::select('is_allow_shortURL','shortURL_access_token')->where('org_id',$orgId)->first();

                //         $stmt = $connection->query("SELECT is_allow_shortURL,shortURL_access_token FROM clients WHERE `org_id` = '".$orgId."'");
                // //echo $stmt;die;
                // $row = $stmt->fetch();
                          if(!empty($row)){
                              $shortURL_updated_at =date("Y-m-d h:i:s");
                              $shortURL_created_at =date("Y-m-d h:i:s");
                              $update_allow = 1;
                              $access_token = md5($orgId).md5(time());
                              
                              if($row['shortURL_access_token'] ==""){
                                $updateData = array('is_allow_shortURL' => $update_allow,'shortURL_created_at' => $shortURL_created_at,'shortURL_access_token'=>$access_token,'shortURL_updated_at'=>$shortURL_updated_at);
                                Clients::where('org_id', $orgId)->update($updateData);

                                return response()->json(["status" => "true", "message" => "access token generated successfully","access_token" => $access_token], 200);

                                //    $sql_up = "UPDATE clients SET is_allow_shortURL=?, shortURL_created_at=?, shortURL_access_token=?, shortURL_updated_at=? WHERE org_id=?";
                                //    $connection->prepare($sql_up)->execute([$update_allow, $shortURL_created_at, $access_token, $shortURL_updated_at, $orgId]);
                                //    http_response_code(200);
                                //    echo json_encode(["status" => "true", "message" => "access token generated successfully","access_token" => $access_token]);
                              }else{

                                $updateData = array('is_allow_shortURL' => $update_allow,'shortURL_access_token' => $access_token,'shortURL_updated_at'=>$shortURL_updated_at);
                                Clients::where('org_id', $orgId)->update($updateData);
                                return response()->json(["status" => "true", "message" => "access token generated successfully","access_token" => $access_token], 200);

                                //    $sql_up = "UPDATE clients SET is_allow_shortURL=?, shortURL_access_token=?, shortURL_updated_at=? WHERE org_id=?";
                                //    $connection->prepare($sql_up)->execute([$update_allow, $access_token, $shortURL_updated_at, $orgId]);
                                //    http_response_code(200);
                                //    echo json_encode(["status" => "true", "message" => "access token generated successfully","access_token" => $access_token]);
                              }
                        }else{
                            return response()->json(["status" => "false", "message" => "Invalid Org Id", "details" => "Please Provide a valid Org Id"], 200);

                            //   http_response_code(200);
                            //   echo json_encode(["status" => "false", "message" => "Invalid Org Id", "details" => "Please Provide a valid Org Id"]); 
                          }
                        
                    }else{
                        return response()->json(["status" => "false", "message" => "Invalid is_allow_shortURL value", "details" => "Please Provide a valid value"], 200);

                        // http_response_code(200);
                        // echo json_encode(["status" => "false", "message" => "Invalid is_allow_shortURL value", "details" => "Please Provide a valid value"]);
                    }
                    
                    
        
            }else{
                return response()->json(["status" => "false", "message" => "Incomplete request body", "details" => "Required request body content is missing"], 200);

            //   http_response_code(200);
            //   echo json_encode(["status" => "false", "message" => "Incomplete request body", "details" => "Required request body content is missing"]);
            }
      }
      else{
        return response()->json(["status" => "false", "message" => "Invalid Request Type"], 200);
    }
    }
}
