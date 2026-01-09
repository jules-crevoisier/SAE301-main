<?php

namespace App\Controller\Admin;

use App\Entity\Article;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use Doctrine\ORM\EntityManagerInterface;

class ArticleCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Article::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Article')
            ->setEntityLabelInPlural('Articles')
            ->setPageTitle('index', 'Gestion des articles')
            ->setPageTitle('new', 'Créer un article')
            ->setPageTitle('edit', 'Modifier un article');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('title', 'Titre')
                ->setRequired(true)
                ->setHelp('Le titre de l\'article'),
            SlugField::new('slug', 'Slug')
                ->setTargetFieldName('title')
                ->setHelp('URL-friendly version du titre'),
            ChoiceField::new('category', 'Catégorie')
                ->setChoices([
                    'Nouvelles Capsules' => 'capsules',
                    'Pose Américaine' => 'pose',
                    'Soins & Beauté' => 'soin',
                ])
                ->setRequired(true),
            TextareaField::new('excerpt', 'Extrait')
                ->setHelp('Court résumé de l\'article (optionnel)')
                ->setNumOfRows(3)
                ->hideOnIndex(),
            TextareaField::new('content', 'Contenu')
                ->setRequired(true)
                ->setNumOfRows(10)
                ->hideOnIndex(),
            ImageField::new('image', 'Image')
                ->setBasePath('/uploads/articles/')
                ->setUploadDir('public/uploads/articles/')
                ->setUploadedFileNamePattern('[randomhash].[extension]')
                ->setHelp('URL de l\'image ou upload'),
            BooleanField::new('published', 'Publié')
                ->setHelp('Cocher pour publier l\'article'),
            DateTimeField::new('createdAt', 'Date de création')
                ->hideOnForm(),
            DateTimeField::new('updatedAt', 'Date de modification')
                ->hideOnForm()
                ->hideOnIndex(),
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof Article) {
            $entityInstance->setUpdatedAt(new \DateTimeImmutable());
        }
        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof Article) {
            $entityInstance->setUpdatedAt(new \DateTimeImmutable());
        }
        parent::updateEntity($entityManager, $entityInstance);
    }
}
