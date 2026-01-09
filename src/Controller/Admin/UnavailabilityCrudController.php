<?php

namespace App\Controller\Admin;

use App\Entity\Unavailability;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class UnavailabilityCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Unavailability::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            DateTimeField::new('startDate', 'Date de début'),
            DateTimeField::new('endDate', 'Date de fin'),
            TextField::new('reason', 'Raison'),
        ];
    }
}
