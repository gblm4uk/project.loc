<?php

namespace App\Form;

use Doctrine\DBAL\Types\DateType;
use Doctrine\DBAL\Types\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class ApiFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('task', HiddenType::class, [
                'data' => 'import-form',
            ])
            ->add('import', SubmitType::class)
            ->add('sort', ChoiceType::class, [
                'choices' => [
                    'default' => 'default',
                    'by name, ascending' => 'name_asc',
                    'by name, descending' => 'name_desc',
                    'by qty, ascending' => 'qty_asc',
                    'by qty, descending' => 'qty_desc',
                ],
            ]);
    }

}