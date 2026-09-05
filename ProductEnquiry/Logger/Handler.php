<?php
declare(strict_types=1);

namespace Codilar\ProductEnquiry\Logger;

use Magento\Framework\Logger\Handler\Base;
use Monolog\Level;

class Handler extends Base
{
    /**
     * @var int|Level
     */
    protected $loggerType = Level::Info;

    /**
     * File path relative to var/log/
     * @var string
     */
    protected $fileName = 'var/log/product_enquiry.log';
}
