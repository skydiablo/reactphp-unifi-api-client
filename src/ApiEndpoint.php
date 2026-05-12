<?php
declare(strict_types=1);

namespace SkyDiablo\UnifiApiClient;

enum ApiEndpoint: string
{
    case LOGIN = '/api/login';
    case LOGOUT = '/api/logout';
    case INFO = '/v2/api/info';
    case SELF = '/api/self';
    case DEVICE_BASICS = '/api/s/{site}/stat/device-basic';
    case SITES = '/api/self/sites';
    case DEVICES = '/v2/api/site/{site}/device';
    case SITE_HEALTH = '/api/s/{site}/stat/health';
}
