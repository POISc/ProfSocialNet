<?php

namespace App\Enum;

enum ConnectionType: string
{
    case SUBSCRIBER = 'subscriber';
    case FRIEND = 'friend';
    case OWNER = 'colleagues';
    case REQUEST_USER_TO_COMPANY = 'user_to_company';
    case REQUEST_COMPANY_TO_USER = 'company_to_user';
    case WORKER = 'worker';
}