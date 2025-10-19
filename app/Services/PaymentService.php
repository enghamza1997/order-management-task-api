<?
namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use App\Models\CombinedOrder;
use App\Models\Payment;

class PaymentService
{
    protected $gateways;


    public function __construct()
    {
        $this->gateways = config('payment_gateways.drivers');
    }


    protected function resolveGateway(string $method): PaymentGatewayInterface
    {
        if(!isset($this->gateways[$method])){
            throw new \Exception("Payment gateway [$method] not supported");
        }
        $class = $this->gateways[$method];
        return app($class);
    }


    public function process(CombinedOrder $combinedOrder, string $method, float $amount = null): Payment
    {
        if($combinedOrder->status !== 'confirmed'){
            throw new \Exception('Payments can only be processed for confirmed orders');
        }
        $amount = $amount ?? $combinedOrder->total_amount;


        $gateway = $this->resolveGateway($method);
        $payload = ['combined_order_id'=>$combinedOrder->id, 'amount'=>$amount, 'method'=>$method];
        $result = $gateway->pay($payload);


        $payment = Payment::create([
        'payment_id'=>$result['payment_id'] ?? 'GEN-'.str()->upper(str()->random(8)),
        'combined_order_id'=>$combinedOrder->id,
        'status'=>$result['success'] ? 'successful' : 'failed',
        'method'=>$method,
        'amount'=>$amount,
        'response'=>$result['response'] ?? null,
        ]);


        return $payment;
    }
}