<?php
declare(strict_types=1);

namespace SkyDiablo\UnifiApiClient;

enum ApiEndpoint: string
{
    case LOGIN = '/api/login';
    case LOGOUT = '/api/logout';
    case INFO = '/api/info';
    case DEVICE_BASICS = '/api/s/{site}/stat/device-basic';
    case SITES = '/api/self/sites';
    case DEVICES_V2 = '/v2/api/site/{site}/device';
}
