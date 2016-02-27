<?php

namespace SourceBans\CoreBundle\Exception;

use Symfony\Component\Form\FormInterface;

/**
 * InvalidFormException
 */
class InvalidFormException extends \RuntimeException
{
    /**
     * @var FormInterface
     */
    protected $form;

    /**
     * @param string $message
     * @param FormInterface $form
     */
    public function __construct($message, FormInterface $form = null)
    {
        $this->form = $form;
        parent::__construct($message);
    }

    /**
     * @return FormInterface
     */
    public function getForm()
    {
        return $this->form;
    }
}
