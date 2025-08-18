<?php

// src/Command/ManageUserCommand.php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:manage-user',
    description: 'Crée ou modifie un utilisateur avec login (email/téléphone) et statut actif/inactif',
)]
class ManageUserCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Gestion des utilisateurs');

        // Demande des informations
        $login = $io->ask('Login (email ou téléphone)', null, function ($login) {
            if (empty($login)) {
                throw new \RuntimeException('Le login ne peut pas être vide');
            }
            return $login;
        });

        $passwordQuestion = new Question('Mot de passe');
        $passwordQuestion->setHidden(true);
        $passwordQuestion->setValidator(function ($value) {
            if (empty($value)) {
                throw new \RuntimeException('Le mot de passe ne peut pas être vide');
            }
            return $value;
        });
        $password = $io->askQuestion($passwordQuestion);

        $isActive = $io->confirm('Activer le compte ?', true);

        // Vérification si l'utilisateur existe déjà
        $existingUser = $this->em->getRepository(User::class)->findOneBy(['login' => $login]);
        if ($existingUser) {
            $io->note('Un utilisateur avec ce login existe déjà. Mise à jour en cours...');
            $user = $existingUser;
        } else {
            $user = new User();
            $io->note('Création d\'un nouvel utilisateur...');
        }

        // Configuration de l'utilisateur
        $user->setLogin($login);
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));
        $user->setIsActive($isActive);
        $user->setRoles(['ROLE_USER']); // Role par défaut

        // Persistance
        $this->em->persist($user);
        $this->em->flush();

        $io->success(sprintf(
            'Utilisateur %s avec le login "%s" a été %s avec succès ! Statut: %s',
            $user->getId(),
            $user->getLogin(),
            $existingUser ? 'mis à jour' : 'créé',
            $user->isActive() ? 'ACTIF' : 'INACTIF'
        ));

        return Command::SUCCESS;
    }
}