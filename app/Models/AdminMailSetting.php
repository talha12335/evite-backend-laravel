<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminMailSetting extends Model
{
    use HasFactory;

    protected $table = 'admin_mail_settings';

    protected $fillable = [
        'provider',
        'sendgrid_api_key',
        'smtp_host',
        'smtp_port',
        'smtp_encryption',
        'smtp_username',
        'smtp_password',
        'from_email',
        'from_name',
    ];
}

