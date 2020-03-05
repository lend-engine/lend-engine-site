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

class BillingSuccessController extends Controller
{

    /**
     * @Route("subscribe-success", name="billing_payment_success")
     */
    public function billingPaymentSuccess(Request $request)
    {
        /** @var \AppBundle\Services\StripeService $stripeService */
        $stripeService = $this->get('service.stripe');

        /** @var \AppBundle\Services\BillingService $billingService */
        $billingService = $this->get('billing');

        /** @var \AppBundle\Services\TenantService $tenantService */
        $tenantService = $this->get('service.tenant');

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

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $planCode       = $form->get('planCode')->getData();
            $subscriptionId = $form->get('subscriptionId')->getData();

            $stripeService->activateSubscription($tenant, $planCode, $subscriptionId);

            $this->addFlash("success", "You're subscribed!");

            $this->sendBillingConfirmationEmail($tenant, $billingService->getPlanCode($planCode));
        }

        // redirect back to the app here
        return $this->redirect($returnUri);
    }

    /**
     * @param Tenant $tenant
     * @param $planCode
     */
    private function sendBillingConfirmationEmail(Tenant $tenant, $planCode)
    {
        try {
            $client = new PostmarkClient($this->getParameter('postmark_api_key'));
            $message = $this->renderView('emails/billing_welcome.html.twig',
                [
                    'plan' => $planCode,
                    'tenant' => $tenant
                ]
            );
            $client->sendEmail(
                "Lend Engine <hello@lend-engine.com>",
                $tenant->getOwnerEmail(),
                "Thanks for signing up",
                $message,
                null,
                null,
                true,
                'hello@lend-engine.com'
            );

            // And one to admin
            $client->sendEmail(
                "Lend Engine billing <hello@lend-engine.com>",
                "hello@lend-engine.com",
                "Thanks for signing up",
                $message
            );
        } catch (PostmarkException $ex) {
            $this->addFlash('error', 'Failed to send email:' . $ex->message . ' : ' . $ex->postmarkApiErrorCode);
        } catch (\Exception $generalException) {
            $this->addFlash('error', 'Failed to send email:' . $generalException->getMessage());
        }
    }
}