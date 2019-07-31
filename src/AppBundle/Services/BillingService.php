<?php

namespace AppBundle\Services;

/**
 * Class BillingService
 * @package AppBundle\Services
 *
 * Handles how features are mapped to billing plans
 *
 */
class BillingService
{
    /** @var string */
    private $env;

    /**
     * @param $symfonyEnv
     */
    public function __construct($symfonyEnv)
    {
        $this->env = $symfonyEnv;
    }

    /**
     * Returns the CURRENT billing plans for display in the UI
     * Customers on legacy plans are mapped to one of the current plans in CustomConnectionFactory
     * @return array
     */
    public function getPlans()
    {

        if ($this->env == 'prod') {

            // ALL PROD SERVERS

            $plans = [
                [
                    'code' => 'free',
                    'stripeCode' => 'free',
                    'name' => 'Free',
                    'amount' => 0
                ],
                [
                    'code' => 'starter',
                    'stripeCode' => 'plan_Cv8Lg7fyOJSB0z', // Standard monthly 5.00
                    'name' => 'Starter',
                    'amount' => 500
                ],
                [
                    'code' => 'plus',
                    'stripeCode' => 'plus',
                    'name' => 'Plus',
                    'amount' => 2000
                ],
                [
                    'code' => 'business',
                    'stripeCode' => 'plan_F4HgQehPQ2nOlN',
                    'name' => 'Business',
                    'amount' => 4000
                ]
            ];

        } else {

            // STAGING AND DEV SERVER

            $plans = [
                [
                    'code' => 'free',
                    'stripeCode' => 'free',
                    'name' => 'Free',
                    'amount' => 0
                ],
                [
                    'code' => 'starter',
                    'stripeCode' => 'plan_FX1HLedEtRzj4k',
                    'name' => 'Starter',
                    'amount' => 500
                ],
                [
                    'code' => 'plus',
                    'stripeCode' => 'plus',
                    'name' => 'Plus',
                    'amount' => 2000
                ],
                [
                    'code' => 'business',
                    'stripeCode' => 'plan_F4HR4VG76biNcB',
                    'name' => 'Business',
                    'amount' => 4000
                ]
            ];

        }

        return $plans;
    }

    /**
     * Transform plan_Cv6rBge0LPVNin to starter to allow dynamic plans on Stripe while keeping fixed codes in app
     * @param $planStripeCode
     * @return mixed
     */
    public function getPlanCode($planStripeCode)
    {
        $plan = 'NOTSET';
        switch ($planStripeCode) {

            case 'free':
                $plan = 'free';
                break;

            case 'standard':
            case 'starter':
            case 'plan_Cv8Lg7fyOJSB0z': // standard monthly 5.00
            case 'plan_Cv6TbQ0PPSnhyL': // test plan
            case 'plan_Cv6rBge0LPVNin': // test plan
            case 'plan_FX1HLedEtRzj4k': // starter on test env
            case 'single':
                $plan = 'starter';
                break;

            case 'premium':
            case 'plus':
            case 'multiple':
                $plan = 'plus';
                break;

            case 'business':
            case 'plan_F4HR4VG76biNcB': // test
            case 'plan_F4HgQehPQ2nOlN': // prod
                $plan = 'business';
                break;
        }

        return $plan;
    }
}