<?php

namespace GotChosen\SiteBundle\Form\Type;

use GotChosen\SiteBundle\Entity\ProfileProperty;
use GotChosen\SiteBundle\Validator\Constraints\Birthday;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    private $propertyRefs;

    public function __construct($propertyRefs)
    {
        $this->propertyRefs = $propertyRefs;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('username', 'text', [
                'label' => 'Username',
                'required' => true,
                'attr' => ['title' => 'Username must be at least 4 characters long and can only contain letters, numbers, and underscores.'],
                'constraints' => [new NotBlank()],
                'error_type' => 'inline',
            ])
            ->add('plainPassword', 'password', [
                'label' => 'Password',
                'required' => true,
                'attr' => ['title' => 'Password must be at least 8 characters long'],
                'error_type' => 'inline',
                'validation_groups' => ['Registration'],
            ])
            ->add('email', 'repeated', [
                'type' => 'email',
                'first_options' => ['label' => 'E-mail'],
                'second_options' => ['label' => 'Confirm E-mail'],
                'invalid_message' => 'E-mail and Confirm E-mail do not match',
                'required' => true,
                'constraints' => [new NotBlank(), new Email()],
                'error_type' => 'inline',
            ])
            /**
             * Replacing the old password/email fields with the ones above
            ->add('plainPassword', 'repeated', array(
                'type' => 'password',
                'first_options' => array('label' => 'Password', 'attr' => ['title' => 'Password must be at least 8 characters long']),
                'second_options' => array('label' => 'Confirm Password'),
                'invalid_message' => 'Password and Confirm Password do not match',
                'required' => true,
                'error_type' => 'inline',
                'validation_groups' => ['Registration'],
            ))
            ->add('email', 'email', [
                'label' => 'E-mail',
                'required' => true,
                'constraints' => [new NotBlank(), new Email()],
                'error_type' => 'inline',
            ])
             */
        ;

        /** @var $property ProfileProperty */
        foreach ( $this->propertyRefs as $property ) {
            $extra = [];
            if ( $property->getName() == 'BirthDay' ) {
                $extra = [
                    'title' => 'Must be 18 years old or older',
                    'constraints' => [new Birthday()]
                ];
            }
            $property->createFormElement($builder, false, $extra);
        }

        /*
            ->add('firstName', null, ['label' => 'First Name', 'required' => true, 'mapped' => false])
            ->add('lastName', null, ['label' => 'Last Name', 'required' => true, 'mapped' => false])
            ->add('birthday', 'date', [
                'label' => 'Birthday',
                'years' => range(date('Y') - 10, date('Y') - 100),
                'mapped' => false,
            ])
            ->add('country', 'country', ['preferred_choices' => ['US'], 'mapped' => false])
            ->add('preferredLanguage', 'choice', [
                'label' => 'Language',
                'choices' => ['en' => 'English', 'es' => 'Spanish', 'pt' => 'Portuguese'],
                'required' => true,
                'mapped' => false,
            ])
        ;
        */
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'GotChosen\SiteBundle\Entity\User',
            'intention'  => 'registration',
        ));
    }

    public function getName()
    {
        return 'gotchosen_site_registration';
    }
}