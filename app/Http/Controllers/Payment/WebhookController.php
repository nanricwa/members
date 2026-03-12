<?php

namespace App\Http\Controllers\Payment;

use App\Enums\PaymentGateway;
use App\Http\Controllers\Controller;
use App\Services\Payment\PaymentGatewayFactory;
use App\Services\Payment\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function handleStripe(Request $request, PaymentService $paymentService): Response
    {
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature', '');

        try {
            $gateway = PaymentGatewayFactory::create(PaymentGateway::Stripe);

            if (! $gateway->verifyWebhookSignature($payload, $signature)) {
                Log::warning('Stripe Webhook: Invalid signature');

                return response('Invalid signature', 400);
            }

            $result = $gateway->handleWebhook($payload, $signature);
            $eventType = $result['event_type'];
            $data = $result['data'];

            Log::info("Stripe Webhook received: {$eventType}");

            match ($eventType) {
                'checkout.session.completed' => $paymentService->processCheckoutCompleted($data, PaymentGateway::Stripe),
                'invoice.paid' => $paymentService->processInvoicePaid($data, PaymentGateway::Stripe),
                'customer.subscription.deleted' => $paymentService->processSubscriptionDeleted($data, PaymentGateway::Stripe),
                'invoice.payment_failed' => $paymentService->processPaymentFailed($data, PaymentGateway::Stripe),
                default => Log::info("Stripe Webhook: Unhandled event type: {$eventType}"),
            };

            return response('OK', 200);
        } catch (\Exception $e) {
            Log::error('Stripe Webhook error: ' . $e->getMessage());

            return response('Webhook error', 500);
        }
    }
}
