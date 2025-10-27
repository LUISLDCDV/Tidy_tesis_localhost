<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class TestMailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:test {email : Email address to send test to}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test email to verify Maileroo SMTP configuration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');

        $this->info('Testing Maileroo SMTP configuration...');
        $this->info('Destination: ' . $email);

        // Display current mail configuration
        $this->table(['Configuration', 'Value'], [
            ['MAIL_MAILER', config('mail.default')],
            ['MAIL_HOST', config('mail.mailers.smtp.host')],
            ['MAIL_PORT', config('mail.mailers.smtp.port')],
            ['MAIL_ENCRYPTION', config('mail.mailers.smtp.encryption')],
            ['MAIL_USERNAME', config('mail.mailers.smtp.username') ? '***configured***' : 'NOT SET'],
            ['MAIL_FROM_ADDRESS', config('mail.from.address')],
            ['MAIL_FROM_NAME', config('mail.from.name')],
        ]);

        try {
            Mail::raw('Este es un email de prueba desde Tidy usando Maileroo SMTP.

Configuración:
- Host: ' . config('mail.mailers.smtp.host') . '
- Port: ' . config('mail.mailers.smtp.port') . '
- Encryption: ' . config('mail.mailers.smtp.encryption') . '
- Timestamp: ' . now() . '

Si recibes este email, la configuración de Maileroo está funcionando correctamente.

--
Tidy Admin System', function ($message) use ($email) {
                $message->to($email)
                        ->subject('Test Email - Maileroo SMTP Configuration')
                        ->from(config('mail.from.address'), config('mail.from.name'));
            });

            $this->info('✅ Email sent successfully!');
            $this->info('Check the inbox for: ' . $email);

            Log::info('Test email sent successfully', [
                'recipient' => $email,
                'mail_driver' => config('mail.default'),
                'mail_host' => config('mail.mailers.smtp.host')
            ]);

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Failed to send email: ' . $e->getMessage());

            $this->warn('Common issues:');
            $this->line('1. Check MAIL_USERNAME and MAIL_PASSWORD in .env');
            $this->line('2. Verify Maileroo credentials are correct');
            $this->line('3. Check if domain is verified in Maileroo');
            $this->line('4. Try different port (465, 587, 2525)');

            Log::error('Test email failed', [
                'recipient' => $email,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return 1;
        }
    }
}