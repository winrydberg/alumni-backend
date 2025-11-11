<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Payment;
use App\Models\Donation;
use App\Models\User;
use Illuminate\Support\Str;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all donations
        $donations = Donation::all();

        // Get all users
        $users = User::where('is_verified', true)->get();

        if ($donations->isEmpty()) {
            $this->command->info('No donations found. Please create donations first.');
            return;
        }

        if ($users->isEmpty()) {
            $this->command->info('No verified users found. Creating payments without user association.');
        }

        $paymentMethods = ['mobile_money', 'card', 'bank_transfer', 'cash'];
        $paymentStatuses = ['completed', 'pending', 'failed'];

        // Create payments for each donation
        foreach ($donations as $donation) {
            $numberOfPayments = rand(5, 15); // Random number of payments per donation

            for ($i = 0; $i < $numberOfPayments; $i++) {
                $user = $users->isEmpty() ? null : $users->random();
                $status = $paymentStatuses[array_rand($paymentStatuses)];
                $amount = rand($donation->minimum_amount ?? 50, 5000);

                $paymentData = [
                    'payment_reference' => 'PAY-' . strtoupper(Str::random(12)),
                    'donation_id' => $donation->id,
                    'user_id' => $user?->id,
                    'donor_name' => $user ? $user->first_name . ' ' . $user->last_name : 'Anonymous Donor ' . rand(1, 100),
                    'donor_email' => $user?->email ?? 'donor' . rand(1000, 9999) . '@example.com',
                    'donor_phone' => $user?->phone_number ?? '+233' . rand(200000000, 599999999),
                    'amount' => $amount,
                    'payment_method' => $paymentMethods[array_rand($paymentMethods)],
                    'payment_status' => $status,
                    'transaction_id' => 'TXN-' . strtoupper(Str::random(16)),
                    'payment_notes' => $this->getRandomPaymentNote(),
                    'metadata' => [
                        'ip_address' => rand(1, 255) . '.' . rand(1, 255) . '.' . rand(1, 255) . '.' . rand(1, 255),
                        'user_agent' => 'Mozilla/5.0',
                        'payment_gateway' => $this->getPaymentGateway($paymentMethods[array_rand($paymentMethods)]),
                    ],
                    'paid_at' => $status === 'completed' ? now()->subDays(rand(1, 60)) : null,
                    'created_at' => now()->subDays(rand(1, 60)),
                    'updated_at' => now()->subDays(rand(0, 30)),
                ];

                Payment::create($paymentData);
            }

            $this->command->info("Created {$numberOfPayments} payments for donation: {$donation->title}");
        }

        $totalPayments = Payment::count();
        $this->command->info("Successfully created {$totalPayments} total payments!");
    }

    /**
     * Get random payment note
     */
    private function getRandomPaymentNote(): ?string
    {
        $notes = [
            'Payment received successfully',
            'Donation for scholarship fund',
            'Contributing to the cause',
            'In memory of a loved one',
            'Annual contribution',
            'Thank you for the opportunity to give',
            null, // Some payments have no notes
        ];

        return $notes[array_rand($notes)];
    }

    /**
     * Get payment gateway based on method
     */
    private function getPaymentGateway(string $method): string
    {
        $gateways = [
            'mobile_money' => ['MTN Mobile Money', 'Vodafone Cash', 'AirtelTigo Money'],
            'card' => ['Stripe', 'Paystack', 'Flutterwave'],
            'bank_transfer' => ['Bank Transfer', 'Direct Deposit'],
            'cash' => ['Cash Payment'],
        ];

        $options = $gateways[$method] ?? ['Generic Gateway'];
        return $options[array_rand($options)];
    }
}
