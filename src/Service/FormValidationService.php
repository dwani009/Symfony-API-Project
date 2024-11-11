<?php

namespace App\Service;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;

class FormValidationService
{
    /**
     * @param FormInterface $form
     * @return void
     */
    public function validateForm(FormInterface $form): void
    {
        if (!$form->isSubmitted() || !$form->isValid()) {
            $errors = $this->getFormErrors($form);
            throw new HttpException(400, json_encode(['errors' => $errors]));
        }
    }

    /**
     * Collect form validation errors in a structured array format.
     *
     * @param FormInterface $form
     * @return array
     */
    protected function getFormErrors(FormInterface $form): array
    {
        $errors = [];

        foreach ($form->getErrors(true) as $error) {
            $field = $error->getOrigin()->getName();
            $errors[$field][] = $error->getMessage();
        }

        return $errors;
    }
}