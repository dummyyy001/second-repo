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
use WDFQVendorFree\Monolog\Utils;
use WDFQVendorFree\Monolog\Formatter\FormatterInterface;
use WDFQVendorFree\Monolog\Formatter\LineFormatter;
use WDFQVendorFree\Swift_Message;
use WDFQVendorFree\Swift;
/**
 * SwiftMailerHandler uses Swift_Mailer to send the emails
 *
 * @author Gyula Sallai
 *
 * @phpstan-import-type Record from \Monolog\Logger
 * @deprecated Since Monolog 2.6. Use SymfonyMailerHandler instead.
 */
class SwiftMailerHandler extends \WDFQVendorFree\Monolog\Handler\MailHandler
{
    /** @var \Swift_Mailer */
    protected $mailer;
    /** @var Swift_Message|callable(string, Record[]): Swift_Message */
    private $messageTemplate;
    /**
     * @psalm-param Swift_Message|callable(string, Record[]): Swift_Message $message
     *
     * @param \Swift_Mailer          $mailer  The mailer to use
     * @param callable|Swift_Message $message An example message for real messages, only the body will be replaced
     */
    public function __construct(\WDFQVendorFree\Swift_Mailer $mailer, $message, $level = \WDFQVendorFree\Monolog\Logger::ERROR, bool $bubble = \true)
    {
        parent::__construct($level, $bubble);
        @\trigger_error('The SwiftMailerHandler is deprecated since Monolog 2.6. Use SymfonyMailerHandler instead.', \E_USER_DEPRECATED);
        $this->mailer = $mailer;
        $this->messageTemplate = $message;
    }
    /**
     * {@inheritDoc}
     */
    protected function send(string $content, array $records) : void
    {
        $this->mailer->send($this->buildMessage($content, $records));
    }
    /**
     * Gets the formatter for the Swift_Message subject.
     *
     * @param string|null $format The format of the subject
     */
    protected function getSubjectFormatter(?string $format) : \WDFQVendorFree\Monolog\Formatter\FormatterInterface
    {
        return new \WDFQVendorFree\Monolog\Formatter\LineFormatter($format);
    }
    /**
     * Creates instance of Swift_Message to be sent
     *
     * @param  string        $content formatted email body to be sent
     * @param  array         $records Log records that formed the content
     * @return Swift_Message
     *
     * @phpstan-param Record[] $records
     */
    protected function buildMessage(string $content, array $records) : \WDFQVendorFree\Swift_Message
    {
        $message = null;
        if ($this->messageTemplate instanceof \WDFQVendorFree\Swift_Message) {
            $message = clone $this->messageTemplate;
            $message->generateId();
        } elseif (\is_callable($this->messageTemplate)) {
            $message = ($this->messageTemplate)($content, $records);
        }
        if (!$message instanceof \WDFQVendorFree\Swift_Message) {
            $record = \reset($records);
            throw new \InvalidArgumentException('Could not resolve message as instance of Swift_Message or a callable returning it' . ($record ? \WDFQVendorFree\Monolog\Utils::getRecordMessageForException($record) : ''));
        }
        if ($records) {
            $subjectFormatter = $this->getSubjectFormatter($message->getSubject());
            $message->setSubject($subjectFormatter->format($this->getHighestRecord($records)));
        }
        $mime = 'text/plain';
        if ($this->isHtmlBody($content)) {
            $mime = 'text/html';
        }
        $message->setBody($content, $mime);
        /** @phpstan-ignore-next-line */
        if (\version_compare(\WDFQVendorFree\Swift::VERSION, '6.0.0', '>=')) {
            $message->setDate(new \DateTimeImmutable());
        } else {
            /** @phpstan-ignore-next-line */
            $message->setDate(\time());
        }
        return $message;
    }
}