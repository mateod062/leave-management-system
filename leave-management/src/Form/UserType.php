<?php

namespace App\Form;

use App\DTO\UserCreationDTO;
use App\Entity\Team;
use App\Entity\UserRole;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Unique;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 2, 'max' => 255]),
                    new Unique(['entityClass' => 'App\Entity\User', 'field' => 'username'])
                ]
            ])
            ->add('email', EmailType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Email(),
                    new Unique(['entityClass' => 'App\Entity\User', 'field' => 'email'])
                ]
            ])
            ->add('password', PasswordType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 8, 'max' => 255])
                ]
            ])
            ->add('role', ChoiceType::class, [
                'choices' => [
                    'Admin' => UserRole::ROLE_ADMIN,
                    'Project Manager' => UserRole::ROLE_PROJECT_MANAGER,
                    'Team Lead' => UserRole::ROLE_TEAM_LEAD,
                    'Employee' => UserRole::ROLE_EMPLOYEE
                ],
                'constraints' => [
                    new NotBlank(),
                    new Choice([
                        'choices' => [
                            UserRole::ROLE_ADMIN,
                            UserRole::ROLE_PROJECT_MANAGER,
                            UserRole::ROLE_TEAM_LEAD,
                            UserRole::ROLE_EMPLOYEE
                        ]
                    ])
                ]
            ])
            ->add('team', EntityType::class, [
                'class' => Team::class,
                'choice_label' => 'name',
                'constraints' => [
                    new NotBlank()
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Submit',
                'attr' => ['class' => 'btn btn-primary']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UserCreationDTO::class,
        ]);
    }
}