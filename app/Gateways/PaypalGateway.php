<?
namespace App\Gateways;

use App\Contracts\PaymentGatewayInterface;

class PaypalGateway implements PaymentGatewayInterface
{
    public function pay(array $payload): array
    {
        // Simulate using $payload['amount'] and return similar shape
        return [
        'success' => true,
        'payment_id' => 'PAYPAL-'.now()->timestamp,
        'response' => ['simulated'=>'paypal','payload'=>$payload],
        ];
    }
}