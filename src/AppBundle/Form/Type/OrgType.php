<?php
namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class OrgType extends AbstractType
{

    public function __construct()
    {

    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $data = $builder->getData();

        $builder->add('name', TextType::class, array(
            'label' => 'Name',
            'required' => true,
        ));

        $builder->add('email', TextType::class, array(
            'label' => 'Contact email address',
            'required' => true,
        ));

        $choices = [
            'Assistive technology' => 'assistive',
            'Audio visual equipment' => 'audiovisual',
            'Electronic equipment' => 'electronics',
            'Re-usable nappies' => 'nappies',
            'Slings / baby carriers' => 'slings',
            'Sporting goods' => 'sports',
            'Toys' => 'toys',
            'Tools (commercial)' => 'tools',
            'Tools (personal/DIY)' => 'tools',
         ];
        $builder->add('lends', ChoiceType::class, array(
            'label' => 'What do you lend?',
            'choices' => $choices,
            'data' => explode(',',$data->getLends()),
            'expanded' => true,
            'multiple' => true,
            'required' => true,
            'attr' => [
                'data-help' => "Used in the directory search filters."
            ]
        ));

        $builder->add('website', TextType::class, array(
            'label' => 'Website',
            'required' => false,
        ));

//        $builder->add('countryIsoCode', CountryType::class, array(
//            'label' => 'Country',
//            'required' => true,
//            'placeholder' => '- choose country -',
//            'preferred_choices' => ['US', 'CA', 'AU', 'GB', 'NZ'],
//        ));

    }

}