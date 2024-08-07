<?php

declare (strict_types=1);
/*
 * This file is part of the Monolog package.
 *
 * (c) Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace WDFQVendorFree\Monolog\Handler;

use WDFQVendorFree\Monolog\Logger;
use WDFQVendorFree\Monolog\Formatter\NormalizerFormatter;
use WDFQVendorFree\Monolog\Formatter\FormatterInterface;
use WDFQVendorFree\Doctrine\CouchDB\CouchDBClient;
/**
 * CouchDB handler for Doctrine CouchDB ODM
 *
 * @author Markus Bachmann <markus.bachmann@bachi.biz>
 */
class DoctrineCouchDBHandler extends \WDFQVendorFree\Monolog\Handler\AbstractProcessingHandler
{
    /** @var CouchDBClient */
    private $client;
    public function __construct(\WDFQVendorFree\Doctrine\CouchDB\CouchDBClient $client, $level = \WDFQVendorFree\Monolog\Logger::DEBUG, bool $bubble = \true)
    {
        $this->client = $client;
        parent::__construct($level, $bubble);
    }
    /**
     * {@inheritDoc}
     */
    protected function write(array $record) : void
    {
        $this->client->postDocument($record['formatted']);
    }
    protected function getDefaultFormatter() : \WDFQVendorFree\Monolog\Formatter\FormatterInterface
    {
        return new \WDFQVendorFree\Monolog\Formatter\NormalizerFormatter();
    }
}
