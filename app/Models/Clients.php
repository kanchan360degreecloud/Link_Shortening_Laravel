<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Clients extends Model
{
    use HasFactory;
    protected $table = 'clients';
    public $timestamps = false;

    protected $fillable = [
        'clientname',
        'org_id',
        'org_type',
        'sid',
        'token',
        'oauthrefreshtoken',
        'allow_security_flag',
        'allow_AI_flag',
        'client_id',
        'client_secret',
        'name_space_sf',
        'client_email',
        'is_allow_email',
        'is_email_503_allow',
        'is_allow_shortURL',
        'is_encrypt',
        'allow_new_sf_package',
        'shortURL_access_token',
        'shortURL_created_at',
        'shortURL_updated_at',
        'status',
        'reg_date',
        'clientId'
    ];
    
}
