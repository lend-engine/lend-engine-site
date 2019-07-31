<?php
namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class BillingType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('paymentAmount', HiddenType::class, array(
            'required' => false,
            'mapped' => false,
            'attr' => [
                'class' => 'payment-amount'
            ]
        ));

        $builder->add('stripeTokenId', HiddenType::class, array(
            'required' => false,
            'mapped' => false
        ));

        $builder->add('planCode', HiddenType::class, array(
            'required' => false,
            'mapped' => false
        ));

        $builder->add('account', HiddenType::class, array(
            'required' => false,
            'mapped' => false
        ));

        $builder->add('subscriptionId', HiddenType::class, array(
            'required' => false,
            'mapped' => false
        ));
    }

}