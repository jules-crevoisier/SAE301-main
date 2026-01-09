<?php

namespace App\DataFixtures;

use App\Entity\Article;
use App\Entity\Category;
use App\Entity\Service;
use App\Entity\User;
use App\Entity\Reservation;
use App\Entity\Promotion;
use App\Entity\Unavailability;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Faker\Factory;

class AppFixtures extends Fixture
{
    private $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Initialisation de Faker (en français)
        $faker = Factory::create('fr_FR');

        // --- 1. LES CATÉGORIES ---
        $categoriesData = ['Soins des Mains', 'Beauté des Pieds', 'Dépose & Réparations', 'Nail Art'];
        $categoriesEntities = [];

        foreach ($categoriesData as $catName) {
            $category = new Category();
            $category->setName($catName);
            // Création d'un slug simple (ex: Soins des Mains -> soins-des-mains)
            $category->setSlug(strtolower(str_replace(' ', '-', $catName)));

            $manager->persist($category);
            $categoriesEntities[$catName] = $category; // On garde en mémoire pour lier les services
        }

        // --- 2. LES SERVICES ---
        $servicesData = [
            // [Nom, Prix, Durée (min), Catégorie]
            ['Pose Vernis Semi-permanent', 35, 45, 'Soins des Mains'],
            ['Pose Complète Gel (Chablons)', 65, 120, 'Soins des Mains'],
            ['Remplissage Gel', 45, 90, 'Soins des Mains'],
            ['Manucure Russe (Nettoyage)', 25, 30, 'Soins des Mains'],
            ['Pédicure Complète + Vernis', 55, 60, 'Beauté des Pieds'],
            ['Dépose Semi-permanent', 15, 20, 'Dépose & Réparations'],
            ['Réparation ongle cassé', 5, 10, 'Dépose & Réparations'],
            ['Nail Art simple (par doigt)', 2, 5, 'Nail Art'],
            ['Babyboomer', 10, 15, 'Nail Art'],
        ];

        $servicesEntities = [];

        foreach ($servicesData as $data) {
            $service = new Service();
            $service->setTitle($data[0]);
            $service->setPrice($data[1]);
            $service->setDuration($data[2]);
            $service->setDescription($faker->sentence(10));
            $service->setActive(true);
            $service->setCategory($categoriesEntities[$data[3]]);

            $manager->persist($service);
            $servicesEntities[] = $service;
        }

        // --- 3. LES UTILISATEURS (USER) ---

        // A. Création d'un Admin (Vous !)
        $admin = new User();
        $admin->setEmail('admin@ongles.fr');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setFirstname('Sophie');
        $admin->setLastname('Lartiste');
        $admin->setPhono('0123456789');
        $admin->setAddress('10 rue de la Paix');
        $admin->setZipcode('10000');
        $admin->setCity('Troyes');
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'password'));
        $manager->persist($admin);

        // B. Création de clients fictifs
        $usersEntities = [];
        for ($i = 0; $i < 10; $i++) {
            $user = new User();
            $user->setEmail("client$i@mail.com");
            $user->setFirstname($faker->firstNameFemale());
            $user->setLastname($faker->lastName());
            $user->setPhono($faker->phoneNumber());
            $user->setAddress($faker->streetAddress());
            $user->setZipcode(str_replace(' ', '', $faker->postcode())); // Nettoyage espace
            $user->setCity($faker->city());
            $user->setPassword($this->passwordHasher->hashPassword($user, 'password'));

            $manager->persist($user);
            $usersEntities[] = $user;
        }

        // --- 4. LES RÉSERVATIONS ---
        for ($i = 0; $i < 20; $i++) {
            $reservation = new Reservation();

            // On prend un utilisateur au hasard
            $client = $faker->randomElement($usersEntities);
            $reservation->setClient($client);

            // Infos client (copie au moment de la commande)
            $reservation->setVisitAddress($client->getAddress() . ' ' . $client->getCity());

            // Date aléatoire (entre -1 mois et +1 mois)
            $date = $faker->dateTimeBetween('-1 month', '+1 month');
            // On force des horaires réalistes (9h - 18h)
            $date->setTime(rand(9, 17), [0, 15, 30, 45][rand(0, 3)]);
            $reservation->setDateRdv($date);

            $reservation->setReference('RDV-' . strtoupper(uniqid()));
            $reservation->setStatus($date < new \DateTime() ? 'COMPLETED' : 'CONFIRMED');

            // Ajout de 1 à 3 services au hasard
            $randomServices = $faker->randomElements($servicesEntities, rand(1, 3));
            $totalPrice = 0;
            $totalDuration = 0;

            foreach ($randomServices as $s) {
                $reservation->addService($s);
                $totalPrice += $s->getPrice();
                $totalDuration += $s->getDuration();
            }

            $reservation->setTotalPrice($totalPrice);
            $reservation->setTotalDuration($totalDuration);
            $reservation->setComment($faker->optional()->sentence());

            $manager->persist($reservation);
        }

        // --- 5. CODES PROMO ---
        $promo = new Promotion();
        $promo->setCode('BIENVENUE10');
        $promo->setPercentage(10);
        $promo->setAtive(true);
        $manager->persist($promo);

        $promoExpired = new Promotion();
        $promoExpired->setCode('NOEL2023');
        $promoExpired->setPercentage(20);
        $promoExpired->setAtive(false);
        $manager->persist($promoExpired);

        // --- 6. INDISPONIBILITÉS ---
        $unavailability = new Unavailability();
        $start = new \DateTime('+2 days 12:00:00');
        $end = new \DateTime('+2 days 14:00:00');
        $unavailability->setStartDate($start);
        $unavailability->setEndDate($end);
        $unavailability->setReason('Déjeuner pro');
        $manager->persist($unavailability);

        // --- 7. ARTICLES BLOG ---
        $articlesData = [
            [
                'title' => 'Nouvelles Capsules Tendances 2024',
                'category' => 'capsules',
                'excerpt' => 'Découvrez les dernières tendances en capsules pour sublimer vos ongles avec style et élégance.',
                'content' => 'Les capsules sont devenues incontournables dans le monde du nail art. Cette année, les tendances se tournent vers des designs plus audacieux et créatifs. Que vous préfériez des capsules classiques ou des modèles plus originaux, nous avons ce qu\'il vous faut pour exprimer votre personnalité.',
                'image' => 'https://images.unsplash.com/photo-1604654894610-df63bc536371?q=80&w=500',
            ],
            [
                'title' => 'Pose Américaine : L\'Art de la Perfection',
                'category' => 'pose',
                'excerpt' => 'La pose américaine est une technique qui demande précision et savoir-faire pour un rendu impeccable.',
                'content' => 'La pose américaine est une méthode de pose d\'ongles qui privilégie la qualité et la durabilité. Cette technique permet d\'obtenir des ongles naturels et résistants, parfaitement adaptés à votre morphologie. Nos professionnelles maîtrisent cette technique à la perfection pour vous offrir un résultat exceptionnel.',
                'image' => 'https://images.unsplash.com/photo-1632345031435-8727f6897d53?q=80&w=500',
            ],
            [
                'title' => 'Soins & Beauté : Prenez Soin de Vos Mains',
                'category' => 'soin',
                'excerpt' => 'Des soins adaptés pour des mains douces et des ongles en parfaite santé toute l\'année.',
                'content' => 'Vos mains méritent le meilleur ! Nos soins sont spécialement conçus pour hydrater, nourrir et protéger votre peau. Combinés à nos techniques de manucure professionnelle, vous obtiendrez des mains soignées et des ongles parfaitement entretenus. Un moment de détente et de bien-être vous attend.',
                'image' => 'https://images.unsplash.com/photo-1519014816548-bf5fe059798b?q=80&w=500',
            ],
            [
                'title' => 'Capsules XL : Le Grand Format',
                'category' => 'capsules',
                'excerpt' => 'Les capsules XL offrent plus d\'espace pour des designs encore plus créatifs et originaux.',
                'content' => 'Les capsules XL sont parfaites pour celles qui souhaitent des ongles plus longs et des designs plus imposants. Ces capsules offrent une surface plus grande pour laisser libre cours à votre créativité. Idéales pour des occasions spéciales ou simplement pour changer de style.',
                'image' => 'https://images.unsplash.com/photo-1604654894611-6973b376cbde?q=80&w=500',
            ],
            [
                'title' => 'French Manucure : L\'Intemporel',
                'category' => 'pose',
                'excerpt' => 'La French manucure reste un classique indémodable, élégant et raffiné pour toutes les occasions.',
                'content' => 'La French manucure est un must-have qui ne se démode jamais. Cette technique classique apporte une touche d\'élégance et de sophistication à vos ongles. Parfaite pour le quotidien comme pour les événements spéciaux, elle s\'adapte à tous les styles et toutes les personnalités.',
                'image' => 'https://images.unsplash.com/photo-1632345031435-8727f6897d53?q=80&w=500',
            ],
            [
                'title' => 'Routine Beauté : Conseils d\'Expert',
                'category' => 'soin',
                'excerpt' => 'Découvrez nos conseils d\'experts pour maintenir vos ongles en parfaite santé au quotidien.',
                'content' => 'Une bonne routine de soin est essentielle pour garder des ongles forts et en bonne santé. Nous vous partageons nos meilleurs conseils : hydratation régulière, protection contre les agressions extérieures, et entretien professionnel régulier. Suivez nos recommandations pour des ongles toujours parfaits.',
                'image' => 'https://images.unsplash.com/photo-1519014816548-bf5fe059798b?q=80&w=500',
            ],
        ];

        foreach ($articlesData as $articleData) {
            $article = new Article();
            $article->setTitle($articleData['title']);
            $article->setSlug(strtolower(str_replace([' ', 'é', 'è', 'ê', 'à', 'ç'], ['-', 'e', 'e', 'e', 'a', 'c'], $articleData['title'])));
            $article->setCategory($articleData['category']);
            $article->setExcerpt($articleData['excerpt']);
            $article->setContent($articleData['content']);
            $article->setImage($articleData['image']);
            $article->setPublished(true);
            $article->setCreatedAt($faker->dateTimeBetween('-2 months', 'now'));
            
            $manager->persist($article);
        }

        $manager->flush();
    }
}
