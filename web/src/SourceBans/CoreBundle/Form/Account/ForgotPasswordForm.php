<?php

namespace SourceBans\CoreBundle\Form\Account;

use SourceBans\CoreBundle\Adapter\AdapterInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * ForgotPasswordForm
 */
class ForgotPasswordForm extends AbstractType
{
    /**
     * @var AdapterInterface
     */
    private $adapter;

    /**
     * @param AdapterInterface $adapter
     */
    public function __construct($adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'models.LostPasswordForm.email',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Submit',
            ]);
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'constraints' => [
                new Assert\Callback([$this, 'validateEmail']),
            ],
        ]);
    }

    /**
     * @param array $data
     * @param ExecutionContextInterface $context
     */
    public function validateEmail(array $data, ExecutionContextInterface $context)
    {
        $admin = $this->adapter->getBy(['email' => $data['email']]);

        if ($admin === null) {
            $context->buildViolation('Invalid email address')
                ->atPath('email')
                ->addViolation();
        }
    }
}
