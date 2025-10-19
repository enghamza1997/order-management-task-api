<?
namespace App\Gateways;

use App\Contracts\PaymentGatewayInterface;

class PaymobGateway implements PaymentGatewayInterface
{
    public function pay(array $payload): array
    {
        // simulate success/failure based on small random chance or payload
        $successful = true; // make deterministic in tests


        return [
            'success' => $successful,
            'payment_id' => 'PAYMOB-'.str()->upper(str()->random(8)),
            'response' => ['message'=>'simulated','payload'=>$payload],
        ];
    }
}