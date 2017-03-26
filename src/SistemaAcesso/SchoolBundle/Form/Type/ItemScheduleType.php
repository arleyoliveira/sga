<?php
/**
 * Created by PhpStorm.
 * User: arley
 * Date: 25/03/17
 * Time: 12:28
 */

namespace SistemaAcesso\SchoolBundle\Form\Type;


use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Doctrine\ORM\EntityRepository;
use SistemaAcesso\BaseBundle\Utils\WeekDay;
use SistemaAcesso\SchoolBundle\Entity\Environment;
use SistemaAcesso\SchoolBundle\Entity\ItemSchedule;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ItemScheduleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('environment', EntityType::class, [
                'class' => Environment::class,
                'label' => 'Ambiente',
                'choice_label' => 'name',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('e')
                        ->where('e.active = :active')
                        ->setParameter("active", 1)
                        ->orderBy('e.name', 'ASC');
                },
                'placeholder' => "Selecione um ambiente",
                'widget_form_group_attr' => [
                    'class' => 'col-md-3'
                ],
            ])

            ->add('weekDay', ChoiceType::class, [
                'label' => 'Dia da Semana',
                'choices' => WeekDay::DAYS,
                'required' => true,
                'placeholder' => "Selecione um dia",
                'widget_form_group_attr' => [
                    'class' => 'row col-md-3'
                ],
            ])

            ->add('startTime', TimeType::class, array(
                    'label' => 'Início',
                    'attr' => array('class' => 'time'),
                    'choice_translation_domain' => null,
                    'required' => true,
                    'widget' => 'single_text',
                    'widget_form_group_attr' => [
                        'class' => 'col-md-3'
                    ],
                )
            )
            ->add('endTime', TimeType::class, array(
                    'label' => 'Fim',
                    'attr' => array('class' => 'time'),
                    'required' => true,
                    'widget' => 'single_text',
                    'widget_form_group_attr' => [
                        'class' => 'col-md-3'
                    ],
                )
            )


        ;

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => ItemSchedule::class,
            'cascade_validation' => true
        ));
    }

    public function getName()
    {
        return parent::getName(); // TODO: Change the autogenerated stub
    }
}