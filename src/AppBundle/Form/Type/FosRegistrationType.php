<?php

// src/AppBundle/Form/RegistrationType.php

/**
 * Override the default FOSUserBundle Registration form
 *
 */
namespace AppBundle\Form\Type;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FosRegistrationType extends AbstractType
{

    public function __construct()
    {

    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('firstName', TextType::class, array(
            'label' => 'form.firstName',
            'required' => true,
        ));

        $builder->add('lastName', TextType::class, array(
            'label' => 'form.lastName',
            'required' => false,
        ));

        $builder->add('email', TextType::class, array(
            'label' => 'form.email',
            'required' => true,
            'attr' => array(
                'data-help' => ""
            )
        ));

        $builder->add('countryIsoCode', CountryType::class, array(
            'label' => 'form.country',
            'required' => true,
        ));

        // Hide the user name (entity class overrides this with email address)
        $builder->add('username', HiddenType::class, array(
            'required' => false,
            'attr' => array(
                'data-help' => ""
            )
        ));

    }

    public function getParent()
    {
        return 'FOS\UserBundle\Form\Type\RegistrationFormType';
    }

    public function getBlockPrefix()
    {
        return 'app_user_registration';
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'validation_groups' => ['AppBundleRegistration']
        ));
    }
}