<?php

namespace Oro\Bundle\VisibilityBundle\Tests\Unit\Form\Type\Stub;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EntityVisibilityType extends TextType
{
    const NAME = 'oro_visibility_entity_visibility_type';

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'ownership_disabled' => 'true',
            'website' => null,
        ]);
    }
}
