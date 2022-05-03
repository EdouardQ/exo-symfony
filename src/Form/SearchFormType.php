<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class SearchFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('keyword', SearchType::class, [
                'required' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => "Veuillez saisir un seul mot clef."
                    ]),
                    new Regex([
                        'pattern' => '/^[a-zA-Z]+$/',
                        'match' => true,
                        'message' => "Veuillez saisir un seul mot clef."
                    ])
                ]
            ])
        ;
    }
}
