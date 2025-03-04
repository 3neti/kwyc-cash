<?php

namespace App\Notifications;

use App\Models\Cash;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GeneratedCashVouchers extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Collection of generated voucher codes.
     */
    public function __construct(public Collection $collection) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        // Generate the CSV file with voucher codes
        $csvFilePath = $this->generateVoucherCodesCsv();

        // Create the mail message with the attachment
        $mailMessage = (new MailMessage)
            ->subject('Your Generated Cash Vouchers')
            ->greeting('Hello, ' . $notifiable->name . '!')
            ->line('You have successfully generated cash vouchers. Please find the attached CSV file containing the voucher codes.')
            ->line('Thank you for using our application!')
            ->attach($csvFilePath, [
                'as' => 'cash_vouchers.csv',
                'mime' => 'text/csv',
            ]);

        // Delete the CSV file after attaching it
        register_shutdown_function(function () use ($csvFilePath) {
            if (file_exists($csvFilePath)) {
                unlink($csvFilePath);
            }
        });

        return $mailMessage;
    }

    /**
     * Generate a CSV file containing the voucher codes.
     *
     * @return string The path to the generated CSV file.
     */
    protected function generateVoucherCodesCsv(): string
    {
        $filePath = 'cash_vouchers_' . now()->timestamp . '.csv';
        $fullPath = storage_path('app/' . $filePath);

        $handle = fopen($fullPath, 'w');

        // Add CSV header
        fputcsv($handle, ['Voucher Code', 'Value', 'Tag']);

        // Add voucher data to the CSV
        foreach ($this->collection as $voucher) {
            $cash = $voucher->getEntities(Cash::class)->first();
            fputcsv($handle, [
                $voucher->code,
                $cash->value,
                $cash->tag ?? 'N/A',
            ]);
        }

        fclose($handle);

        return $fullPath;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'voucher_count' => $this->collection->count(),
            'generated_at' => now()->toDateTimeString(),
        ];
    }
}
