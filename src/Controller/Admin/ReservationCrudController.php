<?php

namespace App\Controller\Admin;

use App\Entity\Reservation;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ReservationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Reservation::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('reference', 'Référence'),
            TextField::new('guestFirstname', 'Prénom'),
            TextField::new('guestLastname', 'Nom'),
            EmailField::new('guestEmail', 'Email'),
            TelephoneField::new('guestPhone', 'Téléphone'),
            DateTimeField::new('dateRdv', 'Date et heure'),
            TextField::new('visitAddress', 'Adresse'),
            TextField::new('status', 'Statut'),
            MoneyField::new('totalPrice', 'Prix total')->setCurrency('EUR'),
            NumberField::new('totalDuration', 'Durée (min)'),
            TextareaField::new('comment', 'Commentaire')->hideOnIndex(),
            AssociationField::new('services', 'Services'),
        ];
    }
}
