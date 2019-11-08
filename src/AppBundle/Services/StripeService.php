<?php

namespace AppBundle\Services;

use AppBundle\Entity\Tenant;
use Doctrine\ORM\EntityManager;
use Stripe\Stripe;

/**
 * Class StripeHandler
 * @package AppBundle\Services
 */

class StripeService
{

    /** @var EntityManager */
    private $em;

    public  $currency;
    private $apiKey;
    public  $errors = [];

    public function __construct(EntityManager $em, $billingApiKey)
    {
        $this->em        = $em;

        $this->currency = 'gbp';
        $this->apiKey   = $billingApiKey; // secret API key
        $this->setApiKey($this->apiKey);
    }

    /**
     * Can be given in the controller each time this handler is called
     * Allows the controller to decide which Stripe account is charged
     * @param $apiKey
     */
    public function setApiKey($apiKey)
    {
        Stripe::setApiKey($apiKey);
    }

    /**
     * @param $subscriptionId
     * @return bool|\Stripe\StripeObject
     */
    public function getSubscription($subscriptionId)
    {
        try {
            $sub = \Stripe\Subscription::retrieve($subscriptionId);
            return $sub;
        } catch (\Exception $e) {
            $this->errors[] = $e->getMessage();
            return false;
        }
    }

    /**
     * @param $customerStripeId
     * @return \Stripe\Customer
     */
    public function getCustomerById($customerStripeId)
    {
        try {
            $stripeCustomer = \Stripe\Customer::retrieve($customerStripeId);
            return $stripeCustomer;
        } catch (\Exception $e) {
            $this->errors[] = $e->getMessage();
            return false;
        }
    }

    /**
     * @param $stripeCustomerId
     * @param $paymentMethodId
     * @return bool
     */
    public function attachPaymentMethod($stripeCustomerId, $paymentMethodId)
    {
        try {
            $paymentMethod = \Stripe\PaymentMethod::retrieve($paymentMethodId);
            $paymentMethod->attach(['customer' => $stripeCustomerId]);
            return true;
        } catch (\Exception $e) {
            $this->errors[] = $e->getMessage();
            return false;
        }
    }

    /**
     * @param array $customer
     * @return \Stripe\Customer
     */
    public function createCustomer($customer = array())
    {
        try {
            $stripeCustomer = \Stripe\Customer::create($customer);
            return $stripeCustomer;
        } catch (\Exception $e) {
            $this->errors[] = $e->getMessage();
            return false;
        }
    }

    /**
     * @param $paymentMethodId
     * @param $amount
     * @param $customer array
     * @return bool|static
     */
    public function createPaymentIntent($paymentMethodId, $amount, $customer)
    {
        // Always have a customer for payments
        if (!$customer['id']) {
            $customer = $this->createCustomer($customer);
        }

        try {
            $intent = \Stripe\PaymentIntent::create([
                'payment_method' => $paymentMethodId,
                'amount' => $amount,
                'currency' => $this->currency,
                'confirmation_method' => 'manual',
                'confirm' => true,
                'customer' => $customer['id'],
                'setup_future_usage' => 'off_session'
            ]);
            return $intent;
        } catch (\Exception $e) {
            $this->errors[] = $e->getMessage();
            return false;
        }
    }

    /**
     * @param $paymentIntentId
     * @return bool|static
     */
    public function retrievePaymentIntent($paymentIntentId)
    {
        try {
            $intent = \Stripe\PaymentIntent::retrieve(
                $paymentIntentId
            );
            $intent->confirm();
            return $intent;
        } catch (\Exception $e) {
            $this->errors[] = $e->getMessage();
            return false;
        }
    }

    /**
     * Subscribe the tenant to a Lend Engine plan
     * @param $tokenId
     * @param Tenant $tenant
     * @param $planCode
     * @return bool|\Stripe\Charge
     */
    public function createSubscription($tokenId, Tenant $tenant, $planCode)
    {

        if ($stripeCustomerId = $tenant->getStripeCustomerId()) {
            // Update the customer to use the new card details
            try {
                \Stripe\Customer::update(
                    $stripeCustomerId,
                    ['source' => $tokenId,]
                );
            } catch (\Exception $generalException) {
                $this->errors[] = $generalException->getMessage();
                return false;
            }
        } else {
            // new customer
            $customerDetails = [
                'name' => $tenant->getName(),
                'description' => $tenant->getOrgEmail(),
                'email' => $tenant->getOwnerEmail(),
                'source' => $tokenId
            ];

            if (!$customer = $this->createCustomer($customerDetails)) {
                $this->errors[] = "Could not create a customer in Stripe";
                return false;
            }

            $stripeCustomerId = $customer['id'];
            $tenant->setStripeCustomerId($stripeCustomerId);
        }

        try {

            $response = \Stripe\Subscription::create([
                'customer' => $stripeCustomerId,
                'plan'     => $planCode,
                'expand' => ['latest_invoice.payment_intent']
            ]);

            if (isset($response->error)) {
                $this->errors[] = $response->error->type.' : '.$response->error->message;
                return false;
            }

            if ($response->status == 'active') {
                return $response;
            } else if ($response->status == 'incomplete') {
                // Save the Stripe customer ID, but not the subscription
                $this->errors[] = 'Your card failed. Please try again.';
                try {
                    $this->em->persist($tenant);
                    $this->em->flush($tenant);
                } catch (\Exception $generalException) {
                    $this->errors[] = 'Failed to update tenant with new Stripe ID: '.$generalException->getMessage();
                }
                return $response;
            } else {
                $this->errors[] = 'Unhandled response status: '.$response->status;
                return false;
            }
        } catch (\Exception $generalException) {
            $this->errors[] = $generalException->getMessage();
        }

        return false;

    }

    /**
     * @param Tenant $tenant
     * @param $planCode
     * @param $subscriptionId
     * @return bool
     */
    public function activateSubscription(Tenant $tenant, $planCode, $subscriptionId)
    {
        $oldSubscriptionId = $tenant->getSubscriptionId();

        // ACTIVATE SUBSCRIPTION
        $tenant->setPlan($planCode);
        $tenant->setStatus(Tenant::STATUS_LIVE);
        $tenant->setSubscriptionId($subscriptionId);

        try {
            $this->em->persist($tenant);
            $this->em->flush($tenant);

            // Cancel any existing plans
            if ($oldSubscriptionId) {
                try {
                    $sub = \Stripe\Subscription::retrieve($oldSubscriptionId);
                    $sub->cancel();
                } catch (\Exception $e) {
                    $this->errors[] = 'Failed to cancel previous subscription: '.$e->getMessage();
                    $this->errors[] = $e->getMessage();
                }
            }
            return true;
        } catch (\Exception $generalException) {
            $this->errors[] = 'Failed to update account with new plan: '.$generalException->getMessage();
        }

        return false;
    }

    /**
     * @param Tenant $tenant
     * @param null $subscriptionId
     * @return bool
     */
    public function cancelSubscription(Tenant $tenant, $subscriptionId = null)
    {
        if (!$stripeCustomerId = $tenant->getStripeCustomerId()) {
            // Cancel and return
            $this->cancelAccount($tenant);
            return true;
        }

        if (!$customer = $this->getCustomerById($stripeCustomerId)) {
            // Does not exist in Stripe
            $this->cancelAccount($tenant);
            return true;
        }

        if (!$tenant->getSubscriptionId()) {
            // May have a customer ID, but no active subscription on Stripe
            $this->cancelAccount($tenant);
            return true;
        }

        try {
            $sub = \Stripe\Subscription::retrieve($subscriptionId);
            $sub->cancel();
            $this->cancelAccount($tenant);
            return true;
        } catch (\Exception $e) {
            $this->errors[] = $e->getMessage();
            return false;
        }
    }

    /**
     * @param Tenant $tenant
     * @return bool
     */
    private function cancelAccount(Tenant $tenant)
    {
        $tenant->setPlan(null);
        $tenant->setStatus(Tenant::STATUS_CANCEL);
        $tenant->setSubscriptionId(null);
        $this->em->persist($tenant);
        try {
            $this->em->flush($tenant);
            return true;
        } catch (\Exception $e) {
            $this->errors[] = $e->getMessage();
            return false;
        }
    }

}