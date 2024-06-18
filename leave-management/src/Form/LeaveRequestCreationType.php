<?php

namespace App\Form;

use App\Constraint\ValidLeaveRequestDates;
use App\DTO\LeaveRequestDTO;
use App\Repository\LeaveRequestRepository;
use App\Service\Auth\Interface\AuthenticationServiceInterface;
use App\Service\LeaveRequest\Interface\LeaveRequestQueryServiceInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

class LeaveRequestCreationType extends AbstractType
{
    public function __construct(
        private readonly AuthenticationServiceInterface $authenticationService,
        private readonly LeaveRequestQueryServiceInterface $leaveRequestQueryService
    )
    {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('startDate', DateType::class, [
                'widget' => 'single_text',
                'constraints' => [
                    new NotBlank(),
                    new Type(['type' => 'DateTime'])
                ]
            ])
            ->add('endDate', DateType::class, [
                'widget' => 'single_text',
                'constraints' => [
                    new NotBlank(),
                    new Type(['type' => 'DateTime'])
                ]
            ])
            ->add('reason', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 2, 'max' => 255])
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Send',
                'attr' => ['class' => 'btn btn-primary']
            ]);

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            $data = $form->getData();

            $user = $this->authenticationService->getAuthenticatedUser();

            if (!$data['startDate'] || !$data['endDate']) {
                return;
            }

            $overlappingRequests = $this->leaveRequestQueryService->getOverlappingLeaveRequests($user->getId(), $data['startDate'], $data['endDate']);
            if (count($overlappingRequests) > 0) {
                $form->addError(new FormError('The requested leave interval overlaps with another approved leave request.'));
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => LeaveRequestDTO::class,
            'constraints' => [new ValidLeaveRequestDates()]
        ]);
    }
}