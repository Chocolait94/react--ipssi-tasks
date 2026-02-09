<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Fixtures pour creer des utilisateurs de test
 *
 * Cree deux utilisateurs :
 * 1. Un administrateur (ROLE_ADMIN) - peut tout faire, y compris supprimer
 * 2. Un utilisateur standard (ROLE_USER) - ne peut pas supprimer
 *
 * Commande : php bin/console doctrine:fixtures:load
 */
class UserFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        // =====================================================
        // UTILISATEUR ADMIN
        // =====================================================
        $admin = new User();
        $admin->setEmail('admin@example.com');
        $admin->setFirstname('Admin');
        $admin->setLastname('Systeme');
        $admin->setRoles(['ROLE_ADMIN']); // Role administrateur
        $admin->setCreatedAt(new \DateTime('-30 days'));

        // Hashage du mot de passe
        // IMPORTANT : Ne jamais stocker un mot de passe en clair !
        $hashedPassword = $this->passwordHasher->hashPassword($admin, 'admin123');
        $admin->setPassword($hashedPassword);

        $manager->persist($admin);

        // Reference pour d'autres fixtures si necessaire
        $this->addReference('user_admin', $admin);

        // =====================================================
        // UTILISATEUR STANDARD
        // =====================================================
        $user = new User();
        $user->setEmail('user@example.com');
        $user->setFirstname('Jean');
        $user->setLastname('Dupont');
        // Pas besoin de setRoles() car ROLE_USER est ajoute automatiquement
        $user->setCreatedAt(new \DateTime('-7 days'));

        $hashedPassword = $this->passwordHasher->hashPassword($user, 'user123');
        $user->setPassword($hashedPassword);

        $manager->persist($user);

        $this->addReference('user_standard', $user);

        // =====================================================
        // DEUXIEME UTILISATEUR STANDARD (pour les tests)
        // =====================================================
        $user2 = new User();
        $user2->setEmail('marie@example.com');
        $user2->setFirstname('Marie');
        $user2->setLastname('Martin');
        $user2->setCreatedAt(new \DateTime('-3 days'));

        $hashedPassword = $this->passwordHasher->hashPassword($user2, 'marie123');
        $user2->setPassword($hashedPassword);

        $manager->persist($user2);

        // Execution des INSERT en base de donnees
        $manager->flush();
    }
}
