<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\AnswerRepository;
use App\Repository\CheckpointRepository;
use App\Repository\EnigmaRepository;
use App\Repository\GameRepository;
use App\Repository\InstanceRepository;
use App\Repository\RoleRepository;
use App\Repository\RoundRepository;
use App\Repository\ScanQRRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CreateActiveInstanceCommand extends Command
{
    protected static $defaultName = 'app:instance:now';
    protected static $defaultDescription = 'Create a test game and its one hour active instance now, with all their related entities datas';

    // Pour intéragir avec les entités
    private $roleRepository;
    private $userRepository;
    private $gameRepository;
    private $checkpointRepository;
    private $instanceRepository;
    private $roundRepository;
    private $scanQRRepository;
    private $enigmaRepository;
    private $answerRepository;

    private $entityManager;
    private $passwordHasher;

    

    public function __construct(RoleRepository $roleRepository, UserRepository $userRepository, GameRepository $gameRepository, CheckpointRepository $checkpointRepository, InstanceRepository $instanceRepository, RoundRepository $roundRepository, ScanQRRepository $scanQRRepository, EnigmaRepository $enigmaRepository, AnswerRepository $answerRepository, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher)
    {
        $this->roleRepository = $roleRepository;
        $this->userRepository = $userRepository;
        $this->gameRepository = $gameRepository;
        $this->checkpointRepository = $checkpointRepository;
        $this->instanceRepository = $instanceRepository;
        $this->roundRepository = $roundRepository;
        $this->scanQRRepository = $scanQRRepository;
        $this->enigmaRepository = $enigmaRepository;
        $this->answerRepository = $answerRepository;

        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
        

        parent::__construct();
    }

    protected function configure(): void
    {
        
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Class pour styliser les inputs/outputs
        $io = new SymfonyStyle($input, $output);

        $io->info("Création d'une instance de test, valide depuis 1h et pendant encore 1h.");

        /********** USERS **********/

        // Users array for future use
        $userObjects = [];

        // Get necessary Role objects 
        $organisatorRole = $this->roleRepository->findOneBy(['slug' => 'ROLE_ORGANISATEUR']);
        $playerRole = $this->roleRepository->findOneBy(['slug' => 'ROLE_JOUEUR']);

        // Set players datas array
        $players = [
            [
                'login' => 'joueur1@joueur.com',
                'password' => 'joueur1',
                'role' => '0'
            ],
            [
                'login' => 'joueur2@joueur.com',
                'password' => 'joueur2',
                'role' => '0'
            ],
            [
                'login' => 'joueur3@joueur.com',
                'password' => 'joueur3',
                'role' => '0'
            ],
            [
                'login' => 'joueur4@joueur.com',
                'password' => 'joueur4',
                'role' => '0'
            ],
            [
                'login' => 'joueur5@joueur.com',
                'password' => 'joueur5',
                'role' => '0'
            ],
            [
                'login' => 'joueur6@joueur.com',
                'password' => 'joueur6',
                'role' => '0'
            ],
            [
                'login' => 'joueur7@joueur.com',
                'password' => 'joueur7',
                'role' => '0'
            ],
            [
                'login' => 'joueur8@joueur.com',
                'password' => 'joueur8',
                'role' => '0'
            ],
            [
                'login' => 'joueur9@joueur.com',
                'password' => 'joueur9',
                'role' => '0'
            ],
            [
                'login' => 'joueur10@joueur.com',
                'password' => 'joueur10',
                'role' => '0'
            ]
            ];

        // New organisator
        $organisator = new User();
        $organisator->setEmail('organisateur1@organisateur.com');
        $organisator->setRole($organisatorRole);
        $organisator->setPassword($this->passwordHasher->hashPassword($organisator, 'organisateur1'));
        $organisator->setUsername('organisateur1');
        $organisator->setLastname('Organisateur');
        $organisator->setFirstname('Numéro-Un');
        $organisator->setStatus(true);

        $userObjects[] = $organisator;

        $this->entityManager->persist($organisator);

        // New players
        // $playersObjectsArray = [];
        // foreach($players as $currentUser)
        // {
        //     $user = new User();
        //     $user->setEmail($currentUser['login']);
        //     $user->setRole($roleEntity[$currentUser['role']]);
        //     $user->setPassword($this->passwordHasher->hashPassword($user, $currentUser['password']));
        //     $user->setUsername($faker->userName());
        //     $user->setLastname($faker->lastName());
        //     $user->setFirstname($faker->firstName());
        //     $user->setAddress($faker->secondaryAddress());
        //     $user->setPostalCode($faker->randomNumber(5, true));
        //     $user->setCity($faker->country());
        //     $user->setStatus(true);
        //     $userEntity[]= $user;
        //     $manager->persist($user);
        // }

        $this->entityManager->flush();
        
        return Command::SUCCESS;
    }
    
}