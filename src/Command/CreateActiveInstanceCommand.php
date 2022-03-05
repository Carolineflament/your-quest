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

        
        // One new organisator
        $organisator = new User();
        $organisator->setEmail('organisateur1@organisateur.com');
        $organisator->setRole($organisatorRole);
        $organisator->setPassword($this->passwordHasher->hashPassword($organisator, 'organisateur1'));
        $organisator->setUsername('organisateur1');
        $organisator->setLastname('Numéro1');
        $organisator->setFirstname('Orga');
        $organisator->setStatus(true);

        $userObjects[] = $organisator;

        $this->entityManager->persist($organisator);

        // Ten new players
        for ($i = 1; $i<= 10; $i++) {
            $player = new User();
            $player->setEmail("joueur$i@joueur.com");
            $player->setRole($playerRole);
            $player->setPassword($this->passwordHasher->hashPassword($player, "joueur$i"));
            $player->setUsername("joueur$i");
            $player->setLastname("Numéro$i");
            $player->setFirstname('Joueur');
            $player->setStatus(true);

            $userObjects[] = $player;

            $this->entityManager->persist($player);
        }
        
        

        $this->entityManager->flush();
        
        return Command::SUCCESS;
    }
    
}