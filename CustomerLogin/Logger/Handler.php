<?php

namespace Codilar\CustomerLogin\Logger;

use Magento\Framework\Logger\Handler\Base;
use Monolog\Logger as MonologLogger;

class Handler extends Base
{
    protected $loggerType = MonologLogger::INFO;

    protected $fileName = '/var/log/customer_login.log';
}
