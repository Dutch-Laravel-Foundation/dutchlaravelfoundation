<?php

declare(strict_types=1);

namespace App\Health\Checks;

use Illuminate\Support\Facades\Mail;
use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;
use Spatie\Health\Enums\Status;
use Symfony\Component\Mailer\Transport\Smtp\SmtpTransport;
use Symfony\Component\Mailer\Transport\Smtp\Stream\SocketStream;
use Throwable;

final class MailTransportCheck extends Check
{
    public function run(): Result
    {
        $mailer = (string) config('mail.default');
        $transportName = (string) config("mail.mailers.{$mailer}.transport");

        $meta = [
            'mailer' => $mailer,
            'transport' => $transportName,
        ];

        if ($transportName !== 'smtp') {
            return (new Result(Status::skipped()))
                ->shortSummary($transportName)
                ->meta($meta);
        }

        $transport = Mail::mailer($mailer)->getSymfonyTransport();

        if (! $transport instanceof SmtpTransport) {
            return Result::make()
                ->failed('The configured mail transport is not an SMTP transport.')
                ->meta($meta);
        }

        $stream = $transport->getStream();

        if ($stream instanceof SocketStream) {
            $stream->setTimeout(2.0);
        }

        $startedAt = hrtime(true);

        try {
            $transport->start();
        } catch (Throwable $exception) {
            return Result::make()
                ->failed('Could not connect and authenticate to the outbound SMTP server.')
                ->shortSummary('unreachable')
                ->meta([
                    ...$meta,
                    'exception' => $exception::class,
                ]);
        } finally {
            try {
                $transport->stop();
            } catch (Throwable) {
                // The connection result above is authoritative; cleanup must not mask it.
            }
        }

        $connectionTimeMs = (int) round((hrtime(true) - $startedAt) / 1_000_000);

        return Result::make()
            ->ok()
            ->shortSummary("{$connectionTimeMs}ms")
            ->meta([
                ...$meta,
                'connectionTimeMs' => $connectionTimeMs,
            ]);
    }
}
