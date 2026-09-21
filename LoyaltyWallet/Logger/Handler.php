<?php
declare(strict_types=1);

namespace Codilar\LoyaltyWallet\Logger;

use Magento\Framework\Logger\Handler\Base;
use Monolog\Logger;

class Handler extends Base
{
    /**
     * File name relative to var/log/
     * @var string
     */
    protected $fileName = '/var/log/codilar_wallet.log';

    /**
     * Logging level
     * @var int
     */
    protected $loggerType = Logger::INFO;
}
