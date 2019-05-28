<?php
namespace AppBundle\Form\Type;

use AppBundle\Entity\Org;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrgType extends AbstractType
{

    private $owners;
    private $tags;

    public function __construct()
    {

    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->owners = $options['owners'];
        $this->tags   = $options['tags'];

        $data = $builder->getData();

        $builder->add('name', TextType::class, array(
            'label' => 'Name',
            'required' => true,
        ));

        $builder->add('email', TextType::class, array(
            'label' => 'Contact email address',
            'required' => false,
        ));

        $builder->add('owner', EntityType::class, [
            'label' => 'Owner',
            'choices' => $this->owners,
            'class' => 'AppBundle:Contact',
            'choice_label' => 'email',
            'placeholder' => '- select owner -',
            'required' => false,
        ]);

        $choices = [
            "Yes" => Org::STATUS_ACTIVE,
            "No" => Org::STATUS_INACTIVE
        ];
        $builder->add('status', ChoiceType::class, array(
            'label' => 'Show in the directory',
            'choices' => $choices,
            'required' => true,
        ));

        $builder->add('lends', ChoiceType::class, array(
            'label' => 'What do you lend?',
            'choices' => $this->tags,
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
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'owners' => null,
            'tags' => null,
        ));
    }

}