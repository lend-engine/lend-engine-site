<?php

namespace AppBundle\Controller\Billing;

use AppBundle\Entity\Tenant;
use AppBundle\Form\Type\BillingType;
use Postmark\Models\PostmarkException;
use Postmark\PostmarkClient;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class BillingPaymentController extends Controller
{
    /**
     * @Route("subscribe", name="subscribe")
     */
    public function billingPaymentForm(Request $request)
    {
        /** @var \AppBundle\Services\TenantService $tenantService */
        $tenantService = $this->get('service.tenant');

        /** @var \AppBundle\Services\BillingService $billingService */
        $billingService = $this->get('billing');
        $plans = $billingService->getPlans();

        $selectedPlan = null;
        $amount = 0;

        $planCode = $request->get('planCode');

        foreach ($plans AS $plan) {
            if ($planCode == $plan['stripeCode']) {
                $selectedPlan = $plan;
                $amount = $plan['amount'];
            }
        }

        if (!$selectedPlan) {
            $this->addFlash("error", "Invalid plan code: ".$planCode);
            return $this->redirectToRoute('page_pricing');
        }

        $tenantCode = $request->get('account');
        /** @var \AppBundle\Entity\Tenant $tenant */
        if (!$tenant = $tenantService->getTenantByAccountCode($tenantCode)) {
            $this->addFlash("error", "Account not found.");
            return $this->redirectToRoute('page_pricing');
        }

        if ($tenant->getDomain()) {
            $returnUri = 'https://'.$tenant->getDomain().'/admin/billing';
        } else {
            $returnUri = 'http://'.$tenantCode.'.lend-engine-app.com/admin/billing';
        }

        // Create the form
        $form = $this->createForm(BillingType::class, null, [
            'action' => $this->generateUrl('billing_payment_success', ['account' => $tenantCode])
        ]);

        $form->get('paymentAmount')->setData($amount);
        $form->get('planCode')->setData($planCode);
        $form->get('account')->setData($tenantCode);

        return $this->render('billing/billing_payment.html.twig', array(
            'tenant' => $tenant,
            'form' => $form->createView(),
            'plan' => $selectedPlan,
            'return_uri' => $returnUri,
            'billing_public_key' => getenv('STRIPE_SUBS_KEY_PUBLIC')
        ));
    }

    /**
     * @Route("subscribe-handler", name="billing_payment_handler")
     */
    public function billingPaymentHandler(Request $request)
    {

        /** @var \AppBundle\Services\StripeService $stripeService */
        $stripeService = $this->get('service.stripe');

        /** @var \AppBundle\Services\TenantService $tenantService */
        $tenantService = $this->get('service.tenant');

        $em = $this->getDoctrine()->getManager();

        $message = '';

        $data = json_decode($request->getContent(), true);

        if (isset($data['account'])) {
            $tenantCode = $data['account'];
        } else {
            $this->addFlash("error", "Account code not found.");
            return $this->redirectToRoute('page_pricing');
        }

        /** @var \AppBundle\Entity\Tenant $tenant */
        if (!$tenant = $tenantService->getTenantByAccountCode($tenantCode)) {
            $this->addFlash("error", "Account not found.");
            return $this->redirectToRoute('page_pricing');
        }

        if (isset($data['stripeTokenId']) && isset($data['planCode'])) {

            $tokenId  = $data['stripeTokenId'];
            $planCode = $data['planCode'];

            if ($planCode == 'free') {

                // Cancel any existing plans if they are downgrading
                if ($subscriptionId = $tenant->getSubscriptionId()) {
                    $stripeService->cancelSubscription($tenant, $subscriptionId);
                }
                $tenant->setPlan($planCode);
                $tenant->setStatus(Tenant::STATUS_LIVE);
                $em->persist($tenant);
                $em->flush();

                return new JsonResponse([
                    'success' => true,
                    'subscription_id' => $subscriptionId,
                    'message' => $message,
                ]);

            } else if ($tokenId) {

                // We're creating a new subscription
                $subscriptionResponse = $stripeService->createSubscription($tokenId, $tenant, $planCode);

                if (!$subscriptionResponse) {
                    $extraErrors = 'Failed to process card';
                    return new JsonResponse([
                        'error' => $extraErrors,
                        'message' => $message,
                        'errors' => $stripeService->errors
                    ]);
                } else if ($subscriptionResponse->status == 'active') {
                    return new JsonResponse([
                        'success' => true,
                        'subscription_id' => $subscriptionResponse->id,
                        'message' => $message,
                    ]);
                } else if ($subscriptionResponse->status == 'incomplete') {
                    return new JsonResponse([
                        'requires_action' => true,
                        'subscription_id' => $subscriptionResponse->id,
                        'payment_intent_client_secret' => $subscriptionResponse->latest_invoice->payment_intent->client_secret,
                        'message' => $message,
                    ]);
                } else {
                    return new JsonResponse([
                        'error' => "Unknown subscription status : ".$subscriptionResponse->status,
                        'message' => $message,
                        'errors' => $stripeService->errors
                    ]);
                }

            } else {
                return new JsonResponse([
                    'error' => "Invalid plan or token",
                    'message' => $message,
                    'errors' => $stripeService->errors
                ]);
            }

        } else {

            return new JsonResponse([
                'error' => "No planCode or Token",
                'message' => $message,
                'errors' => $stripeService->errors
            ]);

        }

    }

}